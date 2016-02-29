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
$(document).ready(function(){
  // *T* here we are after the page is loaded

    var connector = new EveConnector('https://localhost:8164', function(){
        // *T* here we are after the websocket first connection is established

        connector.log('info', 'Scanning devices (direct call) ...');
        connector.log('info', LI.usb.printers);
        var devices = [];
        $.each(LI.usb.printers, function(type, devs){
            $.each(devs, function(i, ids) { devices.push(ids); });
        });
        connector.areDevicesAvailable({ type: 'usb', params: devices}).then(
            function(data){
                // *T* here we are when the list of USB devices is received
                if (!( data.params && data.params.length > 0 ))
                {
                   connector.log('info', 'No '+data.type+' device found within your search.');
                   return;
                }
                var myDevice = data.params.shift();

                $('#li_transaction_museum .print, #li_transaction_manifestations .print')
                .each(function(){
                    $(this)
                      .append($('<input type="hidden" />').prop('name', 'direct').val(JSON.stringify(myDevice)))
                      .prop('title', $('#li_transaction_field_close .print .direct-printing-info').text());
                })
                .attr('onsubmit', null)
                .submit(function(){
                    // *T* here we are when the print form is submitted
                    connector.log('info', 'Submitting the form...');
                    if ( !LI.printTickets(this,false) )
                        return false;

                    $.ajax({
                        method: $(this).prop('method'),
                        url: $(this).prop('action'),
                        data: $(this).serialize(),
                        success: function(data){
                            // *T* here we are when we have got the base64 data representing tickets ready to be printed
                            if ( !data )
                            {
                                connector.log('info', 'Empty data, nothing to send');
                                return;
                            }

                            // sends data to the printer through the connector
                            connector.log('info', 'Sending data...');
                            connector.log('info', data);
                            var device = { type: 'usb', params: myDevice };
                            connector.sendData(device, data)
                            .then(
                                function(res){
                                    connector.log('info', 'Data sent!');
                                    connector.readData(device).then(
                                      function(res){
                                        connector.log('info', res);
                                        if ( res !== undefined ) {
                                          console.log(BocaStatus.getStatuses(atob(res)));
                                        }
                                      },
                                      function(err){
                                        connector.log('error', 'connector.readData()', err);
                                      }
                                    )
                                },
                                function(err){
                                    connector.log('error', 'Data not sent!');
                                }
                            );
                        }
                    });

                    return false;
                });
            },
            function(error) {
                //areDevicesAvailable returned an error
                connector.log('error', error);
            }
        );

        connector.onError = function(){
          $('#li_transaction_museum .print [name=direct], #li_transaction_manifestations .print [name=direct]')
            .remove();
          $('#li_transaction_museum .print').prop('title', null);
        };
    });
});

