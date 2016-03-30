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
            console.info('OK', res);
            LI.alert('Print OK');
            logData.error = false;
            logData.status = res.statuses.join(' | ');
            logData.raw_status = res.raw_status;
          }).catch(function (err) {
            console.info('ERR', err);
            connector.log('error', 'Print error:', err);
            for ( var i in err.statuses ) LI.alert(err.statuses[i], 'error');
            logData.error = true;
            logData.status = err.statuses.join(' | ');
            logData.raw_status = err.raw_status;
          }).then(function(){
            console.info('then...', LI.directPrintLogUrl);
            // log direct print result in DB
            $.ajax({
              type: "GET",
              url: LI.directPrintLogUrl,
              data: {directPrint: logData},
              dataType: 'json',
              success: function () {
                console.info('directPrintLog success', LI.closePrintWindow);
                typeof LI.closePrintWindow === "function" && LI.closePrintWindow();
              },
              error: function (err) {
                console.error(err);
              },              
            });
          });
        }
      });
      return false;
    };

    connector.log('info', 'Scanning devices (direct call) ...');
    connector.log('info', LI.usb.printers);
    var devices = [];
    $.each(LI.usb.printers, function (type, devs) {
      $.each(devs, function (i, ids) {
        devices.push(ids);
      });
    });
    connector.areDevicesAvailable({type: 'usb', params: devices}).then(
      function (data) {
        // *T* here we are when the list of USB devices is received
        if (!(data.params && data.params.length > 0))
        {
          connector.log('info', 'No ' + data.type + ' device found within your search.');
          return;
        }
        var usbParams = data.params.shift();
        LI.activeDirectPrinter = {type:'usb', params: usbParams}; // global var

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
      },
      function (error) {
        //areDevicesAvailable returned an error
        connector.log('error', error);
      }
    );

    connector.onError = function () {
      $('#li_transaction_museum .print [name=direct], #li_transaction_manifestations .print [name=direct]')
        .remove();
      $('#li_transaction_museum .print').prop('title', null);
    };
  });

});

