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
 *    Copyright (c) 2006-2016 Baptiste SIMON <baptiste.simon AT e-glop.net>
 *    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
 *
 ***********************************************************************************/

if ( LI === undefined )
  var LI = {};

$(document).ready(function () {
  // *T* here we are after the page is loaded

  var connector = new EveConnector('https://localhost:8164', function () {
    // *T* here we are after the websocket first connection is established

    // form submit event handler for direct printing
    var directPrint = function (event) {
      var form = event.target;
      if (LI.activeDirectPrinter === undefined) {
        LI.alert('Direct printer is undefined', 'error');
        return false;
      }
      $.ajax({
        method: $(form).prop('method'),
        url: $(form).prop('action'),
        data: $(form).serialize(),
        error: function (error) {
          console.error('directPrint ajax error', error);
        },
        success: function (data) {
          // *T* here we are when we have got the base64 data representing tickets ready to be printed
          if (!data) {
            connector.log('info', 'Empty data, nothing to send');
            return;
          }

          // send data to the printer through the connector then reads the printer answer
          connector.log('info', 'Sending data...');
          connector.log('info', data);
          var Printer = LIPrinter(LI.activeDirectPrinter, connector);
          if (!Printer) {
            LI.alert('Direct printer not configured', 'error');
            return;
          }
          var logData = {
            transaction_id: LI.transactionId,
            duplicate: $(form).find('[name=duplicate]').prop('checked'),
            printer: JSON.stringify(LI.activeDirectPrinter)
          };
          Printer.print(data).then(function(res){
            connector.log('info', 'Print OK', res);
            LI.alert('Print OK');
            logData.error = false;
            logData.status = res.statuses.join(' | ');
            logData.raw_status = res.raw_status;
          }).catch(function (err) {
            connector.log('error', 'Print error:', err);
            for ( var i in err.statuses ) LI.alert(err.statuses[i], 'error');
            logData.error = true;
            logData.status = err.statuses.join(' | ');
            logData.raw_status = err.raw_status;
          }).then(function(){
            // log direct print result in DB
            $.ajax({
              type: "GET",
              url: LI.directPrintLogUrl,
              data: {directPrint: logData},
              dataType: 'json',
              success: function () {
                connector.log('info', 'directPrintLog success', LI.closePrintWindow);
                typeof LI.closePrintWindow === "function" && LI.closePrintWindow();
              },
              error: function (err) {
                console.error(err);
              }            
            });
          });
        }
      });
      return false;
    };

    var lastDisplay = {date: Date.now(), lines: []};

    // outputs totals on USB/serial display
    var displayTotals = function(event) {
      var Display = LIDisplay(LI.activeDisplay, connector);
      if (!Display) 
        return;      
      
      var total = $('#li_transaction_field_payments_list .topay .pit').data('value');
      total = LI.format_currency(total, false).replace('€', 'E');
      var total_label = $('.displays .display-total').text().trim();
      var total_spaces = ' '.repeat(Display.width - total.length - total_label.length);
      
      var left = $('#li_transaction_field_payments_list .change .pit').data('value');
      left = LI.format_currency(left, false).replace('€', 'E');
      var left_label = $('.displays .display-left').text().trim();
      var left_spaces = ' '.repeat(Display.width - left.length - left_label.length);      
      
      var lines = [
        total_label + total_spaces + total, 
        left_label + left_spaces + left
      ];
      
      if ( lines.join('||') === lastDisplay.lines.join('||') )
        return;
      
      var now = Date.now();
      var delay = (now - lastDisplay.date < 500) ? 500 : 0;
      setTimeout(function(){
        lastDisplay.date = now;
        lastDisplay.lines = lines;
        Display.write(lines);
      }, delay);

    };

    // outputs default message on USB display
    var displayDefaultMsg = function(event) {
      var Display = LIDisplay(LI.activeDisplay, connector);
      if (!Display) 
        return;      
      
      var msg = $('.displays .display-default').text();
      msg = msg || "...";
      Display.write([msg]);
    };

    // configure the form for direct printing (if there is an available direct printer)
    var configureDirectPrint = function()
    {
      if ( LI.activeDirectPrinter === undefined ) 
        return;

      var usbParams = LI.activeDirectPrinter.params;

      var dp_title = $('#li_transaction_field_close .print .direct-printing-info').length > 0 ?
        $('#li_transaction_field_close .print .direct-printing-info').text() :
        ( LI.directPrintTitle ? LI.directPrintTitle : "Direct Print" );

      $('#li_transaction_museum .print, #li_transaction_manifestations .print')
        .each(function () {
          $(this)
            .append($('<input type="hidden" />').prop('name', 'direct').val(JSON.stringify(usbParams)))
            .prop('title', dp_title);
        })
        .attr('onsubmit', null)
        .unbind('submit')
        .submit(function () {
          // *T* here we are when the print form is submitted
          connector.log('info', 'Submitting direct print form...');
          LI.printTickets(this, false, directPrint);
          return false;
        });

      // Partial print
      $('form.print.partial-print')
        .append($('<input type="hidden" />').prop('name', 'direct').val(JSON.stringify(usbParams)))
        .prop('title', dp_title)
        .unbind('submit')
        .submit(directPrint);      
    };
    
    // configure the form for handling USB/serial display (if there is an available USB display)
    var configureDisplay = function()
    {
      if ( LI.activeDisplay === undefined ) 
        return;
      
      // Refresh Display when totals change
      $('#li_transaction_field_payments_list .topay .pit').on("changeData", displayTotals);      
      $('#li_transaction_field_payments_list .change .pit').on("changeData", displayTotals);
      
      // Display totals when page (or tab) is selected
      document.addEventListener("visibilitychange", function(evt){
        var evtMap = {focus: true, focusin: true, pageshow: true, blur: false, focusout: false, pagehide: false};
        var visible = false;
        evt = evt || window.event;
        if (evt.type in evtMap)
          visible = evtMap[evt.type];
        else if ('hidden' in document)
          visible = !this.hidden;  
        if (visible) 
          displayTotals();
      });      
      
      // Display default message when leaving the page (or tab closed...)
      $(window).on("beforeunload", displayDefaultMsg);    
    };

    var getAvailableDevices = function(type)
    {
      if (['usb', 'serial'].indexOf(type) === -1)
        return Promise.reject('Wrong device type: ' + type);
        
      // Get all the configured devices ids
      connector.log('info', 'Configured ' + type + ' devices: ', LI[type]);
      if (LI[type] === undefined)
        return;
      var devices = [];
      ['printers', 'displays', 'epts'].forEach(function(family){
        if (LI[type][family] !== undefined)
          for (var name in LI[type][family])
            devices.push(LI[type][family][name][0]);
      });  

      return connector.areDevicesAvailable({type: type, params: devices}).then(
        function(data){
          // data contains the list of the available devices
          connector.log('info', 'Available USB devices:', data.params);

          // Check if we have an available USB printer device and store it in LI.activeDirectPrinter global variable
          var foundPrinter = false;
          if ( type === "usb" && LI.usb.printers !== undefined ) {
            foundPrinter = data.params.some(function(device){
              for ( var name in LI.usb.printers )
                if ( LI.usb.printers[name][0].vid === device.vid && LI.usb.printers[name][0].pid === device.pid ) {
                  LI.activeDirectPrinter = {type: type, params: device}; // global var
                  connector.log('info', 'LI.activeDirectPrinter:', LI.activeDirectPrinter);
                  return true;
                }
              return false;
            });
          }
          foundPrinter && configureDirectPrint();

          // Check if we have an available SERIAL display device and store it in LI.activeDisplay global variable
          var foundDisplay = false;
          if ( type === "serial" && LI.serial.displays !== undefined ) {
            foundDisplay = data.params.some(function(device){
              for ( var name in LI.serial.displays )
                if ( device.pnpId.includes(LI.serial.displays[name][0].pnpId) ) {
                  LI.activeDisplay = {type: type, params: device}; // global var
                  connector.log('info', 'LI.activeDisplay:', LI.activeDisplay);
                  return true;
                }
              return false;
            });
          }
          if (foundDisplay) {
            configureDisplay();
            displayTotals();
          }

          // Check if we have an available USB EPT device and store it in LI.activeEPT global variable
          if ( type === "usb" && LI.usb.epts !== undefined ) {
            data.params.some(function(device){
              for ( var name in LI.usb.epts )
                if ( LI.usb.epts[name][0].vid === device.vid && LI.usb.epts[name][0].pid === device.pid ) {
                  LI.activeEPT = {type: type, params: device}; // global var
                  connector.log('info', 'LI.activeEPT:', LI.activeEPT);
                  return true;
                }
              return false;
            });
          }          
        },
        function(error){
          //areDevicesAvailable returned an error
          connector.log('error', error);          
        }
      );

    }; // END getAvailableDevices()
    
    var getAvailableUsbDevices = function()
    { 
      return getAvailableDevices('usb'); 
    };
    
    var getAvailableSerialDevices = function()
    { 
      return getAvailableDevices('serial'); 
    };    
    
    getAvailableUsbDevices().then(getAvailableSerialDevices);
 
    connector.onError = function () {
      $('#li_transaction_museum .print [name=direct], #li_transaction_manifestations .print [name=direct]')
        .remove();
      $('#li_transaction_museum .print').prop('title', null);
    };
    
  }); // END new EveConnector() callback

});

