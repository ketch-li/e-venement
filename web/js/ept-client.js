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

var evelayer = new EveConnectorLayer(false, 'B8', 978, 1, 0, 0);
evelayer.application.logical.physical.clear(['stxProcessed']);
evelayer.createClient('wss://localhost:8164', { type: 'websocket', params: { ip: 'localhost', port: 8001 }});

function toggleEPTtransaction()
{
  $('#li_transaction_field_payment_new form').toggle();
  $('#ept-transaction').toggle();
}

var getCentsAmount = function (value) {
  var amount = value.replace(",", ".").trim();
    if( /^(\-|\+)?[0-9]+(\.[0-9]+)?$/.test(amount) )
      return Math.round(amount * 100);
  return NaN;
};

function startEPT(button) {
  var amount = getCentsAmount($('#transaction_payment_new_value').val());
  if ( isNaN(amount) ) {
    alert('Wrong amount');
    return false;
  }
  var transaction_id = $("#transaction_close_id").val().trim();
  if ( ! transaction_id ) {
    alert('Transaction ID not found');
    return false;
  }

  // replace new payment form by EPT transaction message
  toggleEPTtransaction();


  evelayer.prepareTransaction(amount, transaction_id);

  evelayer.application.logical.physical.on('applicationSuccess', function(result){
    console.log('applicationSuccess', result);
    if ( parseInt(result.private) != transaction_id ) {
      console.log('...for another transaction_id:', result.private);
      return;
    }
    evelayer.application.logical.physical.clear(['applicationSuccess', 'applicationFailure']);
    alert('applicationSuccess');
    toggleEPTtransaction();
  });
  evelayer.application.logical.physical.on('applicationFailure', function(result){
    console.log('applicationFailure', result);
    if ( parseInt(result.private) != transaction_id ) {
      console.log('...for another transaction_id:', result.private);
      return;
    }
    evelayer.application.logical.physical.clear(['applicationSuccess', 'applicationFailure']);
    alert('applicationFailure');
    toggleEPTtransaction();
  });

  // prevent new payment form submit
  return false;
}

function cancelEPT() {
  // replace EPT transaction message by new payment form
  toggleEPTtransaction();
}

$(document).ready(function(){
  // "Carte Bancaire" click handler
  $('#transaction_payment_new_payment_method_id_3').siblings('button').click(startEPT);

  $('#cancel-ept-transaction').click(cancelEPT);
});