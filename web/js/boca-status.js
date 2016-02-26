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


BocaStatus = function(){
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
    return ( this.statusCodes[code] != undefined ) ? this.statusCodes[code] : false;
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
  
};

