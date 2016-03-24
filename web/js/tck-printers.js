/**********************************************************************************
*
*           This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2016 Marcos Bezerra de Menzes <marcos.bezerra@libre-informatique.fr>
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/

var LIPrinter = function(device, connector) {

  var myType;
  $.each(LI.usb.printers, function (type, devs) {
    $.each(devs, function (i, ids) {
      if( ids.pid === device.params.pid && ids.vid === device.params.vid )
        myType = type;
    });
  });

  switch(myType) {
    case 'boca':
      return new BocaPrinter(device, connector);
    case 'star':
      return new StarPrinter(device, connector);
    default:
      return false;
  }
};



var StarPrinter = function(device, connector){
  this.device = device;
  this.connector = connector;
  this.vendor = 'Star';
  this.model = 'TSP700II';

  var validateStatus = function(status) {
    if ( status.length === 0 ) return true;
    if ( status.length < 2 ) return false;
    for (var i=0; i<status.length; i++) {
      var byte = status.charCodeAt(i);
      if ( byte & 128 || byte & 16 ) return false;
      if ( i === 0 && !(byte & 1) ) return false;
      if ( i > 0 && byte & 1 ) return false;
    }
    var header = status.charCodeAt(0);
    var len = (header & 2) / 2 + (header & 4) / 2 + (header & 8) / 2 + (header & 32) / 4;
    if ( status.length !== len ) return false;
    return true;
  };

  this.getStatuses = function(status) {
    if ( !validateStatus(status) )
      return ['Could not parse printer status'];
    var err = [];
    var byte;
    if ( status.length > 2 ) {
      byte = status.charCodeAt(2);
      if ( byte & 8 )
        err.push('OFFLINE');
      if ( byte & 32 )
        err.push('COVER OPEN');
    }
    if ( status.length > 3 ) {
      byte = status.charCodeAt(3);
      if ( this.model === 'TUP500' && byte & 4 )
        err.push('HEAD THERMISTOR ERROR');
      if ( byte & 8 )
        err.push('AUTO CUTTER ERROR');
      if ( byte & 32 )
        err.push('NON-RECOVERABLE ERROR');
      if ( byte & 64 )
        err.push('STOPPED BY HIGH HEAD TEMPERATURE');
    }
    if ( status.length > 4 ) {
      byte = status.charCodeAt(4);
      if ( byte & 8 )
        err.push('BM ERROR');
      if ( byte & 64 )
        err.push('RECEIVE BUFFER OVERFLOW');
    }
    if ( status.length > 5 ) {
      byte = status.charCodeAt(5);
      if ( byte & 8 )
        err.push('PAPER END / NO PAPER');
    }
    return err;
  };

  this.print = function(data) {
    var printer = this;
    var connector = this.connector;
    var device = this.device;
    return new Promise(function(resolve, reject){
      connector.sendData(device, data).then(function(res){
        connector.readData(device).then(function(res){
          if ( typeof res !== 'undefined' ) {
            var raw_status = atob(res);
            var statuses = printer.getStatuses(raw_status);
            if ( statuses.length > 0 )
              reject({statuses: statuses, raw_status: raw_status});
            else
              resolve({statuses: statuses, raw_status: raw_status});
          }
          else
            reject({statuses: ['Star direct print status is undefined'], raw_status: ''});
        });
        setTimeout(function(){
          reject({statuses: ['Direct print timeout on Star printer'], raw_status: ''});
        }, 2500);
      })
      .catch(function(err){
        reject(err);
      });
    });
  };

};  // END StarPrinter()



var BocaPrinter = function(device, connector) {
  this.device = device;
  this.connector = connector;
  this.vendor = 'Boca';

  this.statusCodes = {
    0x01: "REJECT BIN WARNING",
    0x02: "REJECT BIN ERROR",
    0x03: "PAPER JAM PATH 1 ",
    0x04: "PAPER JAM PATH 2",
    0x05: "TEST BUTTON TICKET ACK",
    0x06: "TICKET ACK",
    0x07: "WRONG FILE IDENTIFIER DURING UPDATE",
    0x08: "INVALID CHECKSUM",
    0x09: "VALID CHECKSUM",
    0x0A: "OUT OF PAPER PATH 1",
    0x0B: "OUT OF PAPER PATH 2",
    0x0C: "PAPER LOADED PATH 1",
    0x0D: "PAPER LOADED PATH 2",
    0x0E: "ESCROW JAM",
    0x0F: "LOW PAPER",
    0x10: "OUT OF PAPER",
    0x11: "X-ON",
    0x12: "POWER ON",
    0x13: "X-OFF",
    0x14: "BAD FLASH MEMORY",
    0x15: "NAK (illegal print command)",
    0x16: "RIBBON LOW",
    0x17: "RIBBON OUT",
    0x18: "PAPER JAM",
    0x19: "ILLEGAL DATA",
    0x1A: "POWERUP PROBLEM",
    0x1C: "DOWNLOADING ERROR",
    0x1D: "CUTTER JAM",
    0x1E: "STUCK TICKET or CUTJAM PATH1",
    0x1F: "CUTJAM PATH2"
  };

  this.getStatus = function(code) {
    return ( this.statusCodes[code] !== undefined ) ? this.statusCodes[code] : false;
  };

  this.getStatuses = function(codes) {
    var statuses = [];
    for (var i=0; i<codes.length; i++) {
      var code = codes.charCodeAt(i);
      var status = this.getStatus(code);
      if ( status )
        statuses.push(status);
    }
    return statuses;
  };

  this.print = function(data) {
    var printer = this;
    var connector = this.connector;
    var device = this.device;
    return new Promise(function(resolve, reject){
      connector.sendData(device, data).then(function(){
        connector.readData(device).then(function(res){
          if ( typeof res !== 'undefined' ) {
            var raw_status = atob(res);
            var statuses = printer.getStatuses(raw_status);
            if ( statuses.indexOf("TICKET ACK") != -1 )
              resolve({statuses: statuses, raw_status: raw_status});
            else
              reject({statuses: statuses, raw_status: raw_status});
          }
          else
            reject({statuses: ['Boca direct print status is undefined'], raw_status: ''});
        });
        setTimeout(function(){
          reject({statuses: ['Direct print timeout on Boca printer'], raw_status: ''});
        }, 2500);
      })
      .catch(function(err){
        reject({statuses: [err], raw_status: ''});
      });
    });
  };

}; // END BocaPrinter
