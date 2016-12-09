/**********************************************************************************
*
*	    This file is part of e-venement.
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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/

if ( LI == undefined )
  var LI = {};
if ( LI.touchscreenOnNewFamilyLoad == undefined )
  LI.touchscreenOnNewFamilyLoad = [];
LI.touchscreenSimplifiedCookie = {
  name: 'tck.touchscreen.simplified-gui',
  options: {
    maxAge: 30*24*60*60, // 30 days expiration
    path: '/'
  }
};
  
$(document).ready(function(){
  // SWITCH BACK FROM / TO SIMPLIFIED GUI
  $('#simplified-gui').click(function(){
    $('#li_fieldset_simplified').fadeToggle(function(){
      if ( !$(this).is(':visible') )
      {
        $('#sf_admin_container h1 #invoice').fadeOut(function(){ $(this).remove(); });
        Cookie.set(LI.touchscreenSimplifiedCookie.name, 'hide', LI.touchscreenSimplifiedCookie.options); // 30 days expiration
        return;
      }
      Cookie.set(LI.touchscreenSimplifiedCookie.name, 'show', LI.touchscreenSimplifiedCookie.options);    // 30 days expiration
      $('<a></a>').addClass('ui-widget-content').addClass('ui-state-default').addClass('ui-corner-all').addClass('ui-widget').addClass('fg-button')
        .prop('href', $('#li_transaction_field_payments_list .accounting.invoice').prop('action'))
        .prop('title', $('#li_transaction_field_payments_list .accounting.invoice input[type=submit]').val())
        .prop('id', 'invoice')
        .prop('target', '_blank')
        .append('<span class="ui-icon ui-icon-clipboard"></span>')
        .insertAfter($('#direct-surveys'))
      ;
      
      // click on the last (or the first) tab...
      if ( !Cookie.get(LI.touchscreenSimplifiedCookie.bunch) )
        Cookie.set(LI.touchscreenSimplifiedCookie.bunch, $('#li_fieldset_simplified .products-types [data-bunch-id]').first().attr('data-bunch-id'), LI.touchscreenSimplifiedCookie.options);
      $('#li_fieldset_simplified .products-types [data-bunch-id="'+Cookie.get(LI.touchscreenSimplifiedCookie.bunch)+'"]').click();
    });
    
    // THE CONTACT LINK...
    $('#li_transaction_field_contact_id').toggleClass('simplified');    
    $('#li_transaction_field_contact_id').toggleClass('loaded');    
    $('#li_transaction_field_postalcode').toggleClass('simplified');
    
    if ( $('#transaction_contact_id').val() != '' && $('#li_transaction_field_contact_id').hasClass('simplified'))
    {
        $('#li_transaction_field_contact_id').addClass('loaded');
    } else {
        $('#li_transaction_field_contact_id').removeClass('loaded');
    }
    
    if ($('#li_transaction_field_contact_id').hasClass('simplified'))
    {
        $('#autocomplete_transaction_contact_id').attr('placeholder', 'Contact');
        $('#transaction_postalcode').attr('placeholder', 'CP');
    } else {
        $('#autocomplete_transaction_contact_id').attr('placeholder', '');
        $('#transaction_postalcode').attr('placeholder', '');
    }
    
    // THE NEW TRANSACTION LINK
    $('#li_transaction_field_new_transaction').toggleClass('simplified');
    
    return false;
  });
  
  // loading data
  $('#li_fieldset_simplified .products-types [data-bunch-id]').unbind('click').click(function(){
    if ( $(this).is('.selected') )
      return false;
    
    // remember the last chosen tab
    Cookie.set(LI.touchscreenSimplifiedCookie.bunch, $(this).attr('data-bunch-id'), LI.touchscreenSimplifiedCookie.options);
    
    $('#li_fieldset_simplified .products-types .selected').removeClass('selected');
    $(this).addClass('selected');
    
    $('#li_fieldset_simplified .bunch')
      .attr('data-bunch-id', $(this).attr('data-bunch-id'));
    
    LI.touchscreenSimplifiedLoadData();
    return false;
  });
  
  if ( Cookie.get(LI.touchscreenSimplifiedCookie.name) == 'show' )
    $('#simplified-gui').click();
  
  // INIT PAYMENT METHODS FROM A COPY OF STANDARD GUI
  LI.touchscreenSimplifiedLoadPaymentMethods();
  
  // USING THE NORMAL "PRINT" BUTTON IF CLICKING ON THE SIMPLIFIED ONE
  $('#li_fieldset_simplified .cart .print').click(function(){
    $('#li_fieldset_content .bunch').find('.print, .store-print').submit();
    setTimeout(function(){ $('#transition .close').click(); },1000);
  });
  
  // SEARCHING PRODUCTS BY DECLINATION'S CODE
  $(window).keypress(function(e){
    if ( $('input:focus').length == 0 && $('#li_fieldset_simplified:visible').length > 0 )
      $('#li_fieldset_simplified .bunch .search input').focus().val(e.key);
  });
  $('#li_fieldset_simplified .bunch .search input').keypress(function(e){
    if ( e.which != 13 )
      return true;
    
    // init:
    $('#li_fieldset_simplified .bunch')
      .addClass('in-progress');
    
    var search = $(this).val();
    $.ajax({
      url: $(this).attr('data-url').replace('SEARCH_VAL', search),
      method: 'get',
      dataType: 'json',
      success: function(json){ LI.touchscreenSimplifiedProductsSearch(json, search) },
      error: LI.touchscreenSimplifiedErrorGUI
    });
    
    $(this).val('');
    return false;
  });
  
  // SEARCHING PRODUCTS BY CATEGORY
  $('#li_fieldset_simplified .bunch .categories').unbind('click').click(function(){
    // init:
    $('#li_fieldset_simplified .bunch > :not(.search):not(.categories)').remove();
    $('#li_fieldset_simplified .bunch').addClass('in-progress');
    
    var cats = this;
    
    if ( $(cats).find('span').text() == $(cats).attr('data-text-best-sales') ) // back to the best sales
    $.ajax({
      url: $(cats).attr('data-products-url').replace('&category_id=CATID', ''),
      method: 'get',
      dataType: 'json',
      success: function(json){
        $(cats).find('span').text($(cats).attr('data-text-categories'));
        LI.touchscreenSimplifiedProductsSearch(json);
      },
      error: LI.touchscreenSimplifiedErrorGUI
    });
    else
    $.ajax({
      url: $(cats).attr('data-categories-url'),
      method: 'get',
      dataType: 'json',
      success: function(json){
        $('#li_fieldset_simplified .bunch').removeClass('in-progress');
        $(cats).find('span').text($(cats).attr('data-text-best-sales'));
        
        $.each(json, function(i, cat){
          $('<li></li>')
            .appendTo($('#li_fieldset_simplified .bunch'))
            .attr('data-category-id', cat.id)
            .append('<span></span>')
            
            .find('span')
            .css('border-color', cat.color)
            .append($('<span></span>'))
            
            .find('span')
            .addClass('category')
            .text(cat.name)
          ;
        });
        
        $('#li_fieldset_simplified .bunch > :not(.search):not(.categories)').click(function(){
          $('#li_fieldset_simplified .bunch').addClass('in-progress');
          $('#li_fieldset_simplified .bunch > :not(.search):not(.categories)').remove();
          
          $.ajax({
            url: $(cats).attr('data-products-url').replace('CATID', $(this).attr('data-category-id')),
            dataType: 'json',
            success: function(json){
              $('#li_fieldset_simplified .bunch').removeClass('in-progress');
              $('#li_fieldset_simplified .bunch > :not(.search):not(.categories)').remove();
              $(cats).find('span').text($(cats).attr('data-text-categories'));
              LI.touchscreenSimplifiedProductsSearch(json);
            },
            error: LI.touchscreenSimplifiedErrorGUI
          });
        });
      }
    });
    
    return false;
  });
});

LI.touchscreenSimplifiedErrorGUI = function(){
  console.error('An error occurred when loading the simplified GUI...');
  $('#li_transaction_field_simplified').fadeOut();
  $('#li_transaction_field_contact_id').removeClass('simplified');
  $('#simplified-gui').remove();
}
      
LI.touchscreenSimplifiedProductsSearch = function(json, search){
  var form = $('#li_transaction_field_content [data-bunch-id="'+$('#li_fieldset_simplified .products-types .selected').attr('data-bunch-id')+'"] .new-family');
  
  LI.touchscreenSimplified_LoadData(json, form, true);
  
  // auto-select a declination if possible
  var type = $(form).closest('[data-bunch-id]').attr('data-bunch-id');
  if ( search )
  $.each(json.success.success_fields[type].data.content, function(i, pdt){
    $.each(pdt[pdt.declinations_name], function(i, decl){
      if ( decl.code && decl.code.toLowerCase() == search.toLowerCase() )
      {
        // click on the first price of the first declination availables
        $('#li_fieldset_simplified .bunch[data-bunch-id=store] [data-family-id="'+pdt.id+'"] > span')
          .click();
        
        // must have only one price to be added automatically
        if ( $(decl.available_prices).length != 1 )
          return;
        
        // continue...
        $(str = '#li_fieldset_simplified .bunch[data-bunch-id=store] [data-family-id="'+pdt.id+'"] [data-declination-id='+decl.id+']')
          .click();
        $('#li_fieldset_simplified .prices button:first').click();
        
        // clears the search string
        setTimeout(function(){ $('#li_fieldset_simplified .bunch .search input').focus() }, 200);
        
        return;
      }
    });
  });
}

LI.touchscreenSimplifiedLoadPaymentMethods = function(){
  $('#li_transaction_field_payment_new .field_payment_method_id li').each(function(){
    var label = $(this).find('label').text();
    var payment = $('<button></button>')
      .text(label.replace('_EPT_', ''))
      .prop('name', 'simplified[payment_method_id]')
      .val($(this).find('input').val())
      .attr('data-ept', label.indexOf('_EPT_') === 0 ? 1 : 0);
    $('<li></li>')
      .attr('data-payment-id', $(this).find('input').val())
      .append(payment)
      .insertBefore($('#li_fieldset_simplified .payments #ept-transaction-simplified'))
    ;
  });
  
  // click on a payment method
  $('#li_fieldset_simplified .payments button').click(function(){
    $('#li_transaction_field_payment_new [name="transaction[payment_new][value]"]').val($('#li_fieldset_simplified .payments [name="simplified[payment_value]"]').val());
    $('#li_fieldset_simplified .payments [name="simplified[payment_value]"]').val('')
    $('#li_transaction_field_payment_new [name="transaction[payment_new][detail]"]').val($('#li_fieldset_simplified .payments [name="simplified[payment_detail]"]').val());
    $('#li_fieldset_simplified .payments [name="simplified[payment_detail]"]').val('')
    $('#li_transaction_field_payment_new [name="transaction[payment_new][payment_method_id]"][value="'+$(this).val()+'"]')
      .prop('checked', true)
      .siblings('button').eq(0).trigger('click');
    return false;
  });
}

LI.touchscreenSimplifiedLoadData = function(){
  LI.touchscreenSimplifiedData = {};
  
  // init:
  $('#li_fieldset_simplified .bunch > :not(.search):not(.categories)').remove();
  $('#li_fieldset_simplified .bunch')
    .attr('data-bunch-id', $('#li_fieldset_simplified .products-types .selected').attr('data-bunch-id'))
    .addClass('in-progress');
  
  // get back distant initial data
  var form = $('#li_transaction_field_content [data-bunch-id="'+$('#li_fieldset_simplified .products-types .selected').attr('data-bunch-id')+'"] .new-family');
  $.ajax({
    url: $(form).prop('action'),
    type: $(form).prop('method'),
    data: { simplified: 1 /*, id: $('[name="transaction[close][id]"]').val() */ },
    dataType: 'json',
    success: function(json){
      LI.touchscreenSimplified_LoadData(json, form);
    },
    error: LI.touchscreenSimplifiedErrorGUI
  });
}

LI.touchscreenSimplified_LoadData = function(data, form, append){
  $('#li_fieldset_simplified .bunch').removeClass('in-progress');
  
  var type = $(form).closest('[data-bunch-id]').attr('data-bunch-id');
  console.error('Simplified GUI: loading basic products ('+type+')');
  if (!( data.success != undefined && data.success.success_fields[type] != undefined ))
  {
    console.error('Simplified GUI: No data found for '+type, data);
    return;
  }
  if ( window.location.hash == '#debug' )
    console.error('Simplified GUI: Loading data for '+type);
  
  // storing data in the global var
  if ( append && LI.touchscreenSimplifiedData[type] !== undefined )
    $.each(data.success.success_fields[type].data.content, function(key, content){
      LI.touchscreenSimplifiedData[type][key] = content;
    });
  else
    LI.touchscreenSimplifiedData[type] = data.success.success_fields[type].data.content;
  
  var events = {};
  var manifsAfterLimit = 0;
  var timeLimit = new Date($('#li_transaction_field_simplified [name=manifestations-display-interval]').val().replace(' ', 'T')); // retrieves the time limit after which we want to hide the manifestations
  
  $.each(data.success.success_fields[type].data.content, function(id, manif){
    if ( window.location.hash == '#debug' )
      console.error('Simplified GUI: Loading an item (#'+id+') from the '+type);
    
    var pdt;
    var widget = $('<li></li>');    
    var gauges = $('<ul></ul>');
    
    switch ( type ) {
    case 'museum':
    case 'manifestations':
      var manifDate = new Date(manif.happens_at.replace(' ', 'T'));
      pdt = manifDate.toLocaleString().replace(/:\d\d( \w+){0,1}$/,'');

      if ( manifDate.getTime() > timeLimit.getTime() )
      {
        widget.addClass('after-limit');
        manifsAfterLimit++;
      }
      break;
    
    case 'store':
      pdt = manif.name;
      break;
    
    default:
      pdt = '';
      break;
    }
    
    widget
      .append(gauges)
      .attr('data-family-id', manif.id)
      .attr('data-category-id', manif.category_id)
      .attr('data-category', manif.category)
      .prepend('<span></span>')
      .find('> span')
      .css('border-color', manif.color)
      .append($('<span></span>').addClass('product').text(pdt))
      .append($('<span></span>').addClass('category').text(manif.category))
    ;
    if ( append )
    {
      $('#li_fieldset_simplified .bunch[data-bunch-id="'+type+'"] [data-family-id='+manif.id+']').remove();
      widget.insertAfter($('#li_fieldset_simplified .bunch[data-bunch-id="'+type+'"] .categories'));
    }
    else
      widget.appendTo($('#li_fieldset_simplified .bunch[data-bunch-id="'+type+'"]'));
    
    $.each(manif[manif.declinations_name], function(i, gauge){
      var li = $('<li></li>')
        .attr('data-'+gauge.type+'-id', gauge.id)
        .appendTo(gauges);
      $('<input />')
        .prop('type', 'radio')
        .val(gauge.id)
        .prop('name', 'simplified[declination_id]')
        .appendTo(li)
      ;
      li.append(' ');
      $('<span></span>').text(gauge.name).addClass('declination-name').appendTo(li);
    });
  });
  
  // relooking of the array, to avoid mistakes w/ the key of the JS associative array
  var obj = {};
  $.each(LI.touchscreenSimplifiedData[type], function(key, data){
    obj[data.id] = data;
  });
  LI.touchscreenSimplifiedData[type] = obj;
  
  LI.touchscreenSimplifiedBehavior(type);

  switch ( type ) {
  case 'museum':
  case 'manifestations':
    // adding show more button
    if ( manifsAfterLimit > 0 )
    {
      $('<li></li>')
        .prop('id', 'show-more')
        .appendTo($('#li_transaction_field_simplified .bunch[data-bunch-id="manifestations"]'))
        .click(function(){
          $('.after-limit').fadeIn();
          $(this).remove();
          return false;
        })
      ;
      
      var button = $('<button></button>')
        .attr('class', 'ui-widget-content ui-state-default ui-corner-all ui-widget fg-button')
        .appendTo('#li_transaction_field_simplified #show-more')
        .append($('<span></span>').attr('class', 'ui-icon ui-icon-circle-plus'))
      ;
    }
    break;
  
  /*
  default:
    $('#li_transaction_field_simplified [data-bunch-id="store"] [data-category-id]').each(function(){
      console.error('store:', $('#li_transaction_field_simplified [data-bunch-id="store"] [data-category-id="'+$(this).attr('data-category-id')+'"]').length);
    });
    break;
  */
  }
}

LI.touchscreenSimplifiedStockCache = {};
LI.touchscreenSimplifiedBehavior = function(type){
  // opens gauges for manifestation or equivalent
  $('#li_fieldset_simplified .bunch[data-bunch-id="'+type+'"] > li:not(.search):not(.categories) > :not(ul)').unbind('click').click(function(){
    $('#li_fieldset_simplified .prices > *').remove();
    var ul = $(this).closest('li').find('ul').slideToggle('fast');
    ul
      .find('.selected').removeClass('selected')
      .find(':checked').prop('checked', false)
    ;
    if ( ul.find('input').length == 1 )
      ul.find('input').closest('li').click();
    $(this).closest('.content').find('.bunch > li > ul').not($(this).closest('li').find('ul')).slideUp('fast');
    
    // graphical gauge triggering...
    var success = function(json, declination){
      var stock = {
        total: 0,
        current: 0,
        state: 'warning' // can be perfect / warning / critical
      }
      var type;
      switch ( type = $(declination).closest('[data-bunch-id]').attr('data-bunch-id') ) {
      case 'store':
        var data = json.declinations[$(declination).attr('data-declination-id')];
        stock.total = data.perfect > data.current ? data.perfect : data.current;
        stock.current = data.current;
        stock.state = 'critical';
        stock.free = data.perfect - data.current;
        if ( data.current >= data.critical )
          stock.state = 'warning';
        if ( data.current >= data.perfect )
          stock.state = 'correct';
        break;
      
      case 'museum':
      case 'manifestations':
        //if ( json.total == 0 )
        //  return;
        stock.total = json.total;
        stock.free = json.free;
        
        $.each(json.booked, function(type, value){
          stock.current += value;
        });
        
        break;
      default:
        console.error('Type of gauge/stock not yet implemented...');
        break;
      }
      
      $('<span></span>').addClass('gauge-gfx').addClass(type)
        .append($('<span></span>')
          .addClass(stock.state)
          .css('width', stock.total == 0 ? '100%' : (stock.current/stock.total*100)+'%')
          .prop('title', stock.current+' / '+stock.total)
        )
        .prop('title', stock.free+' / '+stock.total)
        .appendTo($(declination).is('[data-family-id]') ? $(declination).find('.product') : declination)
      ;
    }
    
    var pdt = LI.touchscreenSimplifiedData[type][$(this).closest('[data-family-id]').attr('data-family-id')];
    if ( $(this).closest('[data-family-id]').find('.gauge-gfx').length == 0 )
    {
      // global gauge
      if ( pdt.gauge_url )
      {
        var family = $(this).closest('[data-family-id]');
        var gauge = pdt.gauge_url;
        if ( LI.touchscreenSimplifiedStockCache[gauge] !== undefined )
          success(LI.touchscreenSimplifiedStockCache[gauge], family);
        else
        $.ajax({
          url: gauge,
          method: 'get',
          success: function(json){
            LI.touchscreenSimplifiedStockCache[gauge] = json;
            success(json, family);
          }
        });
      }
      
      // specific gauge
      $(this).closest('[data-family-id]').find('li').each(function(){
        var gauge = pdt[pdt.declinations_name][$(this).attr('data-'+pdt.declinations_name.slice(0,-1)+'-id')];
        var declination = this;
        if ( LI.touchscreenSimplifiedStockCache[gauge.url] !== undefined )
          success(LI.touchscreenSimplifiedStockCache[gauge.url], declination);
        else
        $.ajax({
          url: gauge.url,
          method: 'get',
          success: function(json){
            LI.touchscreenSimplifiedStockCache[gauge.url] = json;
            success(json, declination);
          }
        });
      });
    }
  });
  
  // activating a particular gauge or equivalent
  $('#li_fieldset_simplified .bunch[data-bunch-id="'+type+'"] > li > ul > li').unbind('click').click(function(){
    var type = $(this).closest('.bunch').attr('data-bunch-id');
    
    // cleansing
    $(this).closest('.content').find('.selected').removeClass('selected');
    $(this).closest('.content').find(':checked').prop('checked', false);
    
    // select current gauge
    $(this).addClass('selected');
    $(this).find('input').prop('checked', true);
    
    // show related prices
    LI.touchscreenSimplifiedPrices(this, LI.touchscreenSimplifiedData[type]);
  });
}

LI.touchscreenSimplifiedPrices = function(gauge, data){
  var target = $('#li_fieldset_simplified .prices');
  target.find('> li').remove();
  var declinations_name = data[$(gauge).closest('[data-family-id]').attr('data-family-id')].declinations_name;
  var prices = data[$(gauge).closest('[data-family-id]').attr('data-family-id')][declinations_name][$(gauge).attr('data-'+declinations_name.slice(0,-1)+'-id')].available_prices;
  if ( prices == undefined )
  {
    console.error('Simplified GUI: no price found for manifestation #'+$(gauge).closest('[data-family-id]').attr('data-family-id')+' and gauge #'+$(gauge).attr('data-gauge-id'));
    return;
  }
  
  // add price widgets
  $.each(prices, function(i, price){
    var li = $('<li></li>')
      .attr('data-price-id', price.id)
      .appendTo(target);
    $('<button></button>')
      .text(price.name)
      .prop('name', 'simplified[price_id]')
      .prop('title', price.description+' → '+price.value)
      .val(price.id)
      .appendTo(li)
    ;
  });
  
  // click on a price button
  $(target).find('button').click(function(){
    var declname;
    var bunch = $('#li_fieldset_simplified .bunch :checked').closest('.bunch').attr('data-bunch-id');
    $.each(LI.touchscreenSimplifiedData[bunch], function(id, pdt){
      declname = pdt.declinations_name.slice(0,-1); // remove the last char "s"
    });
    
    var form = $('#li_transaction_field_price_new form.prices');
    $(form).find('[name="transaction[price_new][bunch]"]').val(bunch);
    $(form).find('[name="transaction[price_new][price_id]"]').val($(this).val());
    $(form).find('[name="transaction[price_new][declination_id]"]').val($('#li_fieldset_simplified .bunch :checked').val());
    $(form).find('[name="transaction[price_new][type]"]').val(declname);
    $(form).submit();
    return false;
  });
}

if ( LI.touchscreenContentLoad == undefined )
  LI.touchscreenContentLoad = [];
LI.touchscreenContentLoad.push(function(data, type, reset){
  // every element on the .cart element is rendered here
  
  switch ( type ) {
  case 'payments':
    $('#li_fieldset_simplified .cart .paid .payment').remove();
    $('#li_fieldset_simplified .cart .paid .value').html(LI.format_currency(0)).attr('data-value', 0);
    $.each(data, function(id, payment){
      // do not display payments from other transcations
      if ( payment.translinked )
        return;
      
      $('<span></span>')
        .attr('data-id', payment.id)
        .addClass('payment')
        .dblclick(function(){
          var id = $(this).closest('[data-id]').attr('data-id');
          $('#li_transaction_field_payments_list [data-payment-id="'+id+'"] .sf_admin_action_delete form').submit();
          $(this).closest('[data-id]').remove();
          $('#li_fieldset_simplified .cart .paid .value').html(LI.format_currency(0)).attr('data-value', 0);
          LI.touchscreenSimplifiedTotal();
          return false;
        })
        .append('<span class="amount">'+LI.format_currency(payment.value)+'</span>')
        .append(' ')
        .append($('<span></span>').addClass('method').prop('title', payment.method).text(payment.method))
        .appendTo($('#li_fieldset_simplified .cart .paid .left'))
      ;
      $('#li_fieldset_simplified .cart .paid .right .value')
        .attr('data-value', parseFloat($('#li_fieldset_simplified .cart .paid .right .value').attr('data-value')) + parseFloat(payment.value))
        .html(LI.format_currency($('#li_fieldset_simplified .cart .paid .right .value').attr('data-value')))
      ;
    });
    
    LI.touchscreenSimplifiedTotal();
    
    break;
  
  case 'museum':
  case 'manifestations':
  case 'store':
    // resetting data...
    if ( reset )
      $('#li_fieldset_simplified .cart .item.'+type).remove();
    
    $.each(data, function(id, pdt){
      $.each(pdt[pdt.declinations_name], function(id, declination){
        // cancellations preprocessing
        var cancelling = [];
        $.each(declination.prices, function(id, price){
          if ( price.state != 'cancelling' )
            return;
          cancelling.push(price);
          delete declination.prices[id];
        });
        
        // normal tickets
        $.each(declination.prices, function(id, price){
          if ( window.location.hash == '#debug' )
            console.error('Simplified GUI: loading item #'+pdt.id+' sold/to sell of type '+type+'...');
          
          // clear data & recalculate totals
          $('#li_fieldset_simplified .cart .item.'+type+'[data-product-id="'+pdt.id+'"][data-declination-id="'+declination.id+'"][data-price-id="'+price.id+'"][data-state="'+(price.state?price.state:'')+'"]')
            .remove();
          LI.touchscreenSimplifiedTotal();
          
          // if nothing has to be displaid, return
          if ( price.qty == 0 )
            return;
          
          if ( window.location.hash == '#debug' )
            console.error('Simplified GUI: rendering item #'+pdt.id+' sold/to sell of type '+type+' with ids: '+price.ids.join()+'.');
          // if something needs to be displaid, display it one by one
          $.each(price.ids, function(i, pdtid){
            var name; switch ( type ) {
            case 'store':
              name = pdt.name;
              declname = 'declination';
              break;
            default:
              name = new Date(pdt.happens_at.replace(' ','T')).toLocaleString().replace(/:\d\d( \w+){0,1}$/,'');
              declname = 'gauge';
              break;
            }
            var left = $('<div></div>').addClass('left');
            var right = $('<div></div>').addClass('right');
            $('<li></li>')
              .addClass('item')
              .addClass(type)
              .addClass(price.state ? 'sold' : 'asked')
              .attr('data-product-id', pdt.id)
              .attr('data-declination-id', declination.id)
              .attr('data-price-id', price.id)
              .attr('data-state', price.state ? price.state : '')
              .attr('data-qty', price.qty)
              .attr('data-value', (price.pit + price['extra-taxes']) / price.qty)
              .prop('title', '#'+pdtid+(price.numerotation[i] ? ' → '+price.numerotation[i] : ''))
              .append(left)
              .append(right)
              .insertAfter($('#li_fieldset_simplified .cart .total'))
              .dblclick(function(){
                if ( $(this).is('.sold') )
                  return;
                $(str = '#li_transaction_field_content .bunch[data-bunch-id="'+type+'"] [data-family-id="'+pdt.id+'"] [data-'+declname+'-id="'+declination.id+'"] [data-price-id="'+price.id+'"] .qty.nb .ui-icon-minus')
                  .click();
                $(this).remove();
                LI.touchscreenSimplifiedTotal();
                return false;
              })
            ;
            left
              .append($('<span></span>').text(pdt.category).addClass('category').prop('title', pdt.category))
              .append(' ')
              .append($('<span></span>').text(name).addClass('product'))
              .append(' ')
              .append($('<span></span>').text(price.name).addClass('price'))
              .append(' ')
              .append($('<span></span>').text(declination.name).addClass('declination').prop('title', declination.name))
            ;
            right
              .append($('<span></span>').attr('data-value', price.pit/price.qty).html(LI.format_currency(price.pit/price.qty)).addClass('value'))
              .append(' ')
              .append($('<span></span>').attr('data-extra-taxes', price['extra-taxes']/price.qty).html(LI.format_currency(price['extra-taxes'])).addClass('extra-taxes'))
            ;
          });
        });
        
        // cancelling post-processing
        $.each(cancelling, function(i, price){
          $(str = '#li_fieldset_simplified .cart .item.'+type+'[data-price-id="'+price.id+'"][data-declination-id="'+declination.id+'"][data-product-id="'+pdt.id+'"][data-value="'+(price.pit+price['extra-taxes'])/price.qty+'"]:not([data-state=""]):not(.cancelled):first')
            .addClass('cancelled');
        });
      });
    });
    
    LI.touchscreenSimplifiedTotal();
    
    break;
    
    default:
      console.error('Simplified GUI: '+type+' is not yet implemented');
      break;
  }
});

// for having a good update after printing/integrating
if ( LI.touchscreenFormComplete == undefined )
  LI.touchscreenFormComplete = [];
LI.touchscreenFormComplete.push(function(data, index){
  if ( data.remote_content === undefined )
    return;
  if ( !data.remote_content.load.reset )
    return;
  if ( !data.remote_content.load.type )
    return;
  
  var type = data.remote_content.load.type.replace(/_price$/, '');
  $('#li_fieldset_simplified .cart .item.'+type).remove();
});

LI.touchscreenSimplifiedTotal = function()
{
  // qty
  $('#li_fieldset_simplified .cart .total .qty')
    .attr('data-qty', $('#li_fieldset_simplified .cart .item').length)
    .text($('#li_fieldset_simplified .cart .item').length)
  ;
  
  // value
  $('#li_fieldset_simplified .cart .total .value').each(function(){
    $(this)
      .attr('data-value', 0)
      .html(LI.format_currency($(this).attr('data-value')))
    ;
  });
  $('#li_fieldset_simplified .cart .item').each(function(){
    var item = this;
    $('#li_fieldset_simplified .cart .total .value').each(function(){
      $(this)
        .attr('data-value', parseFloat($(this).attr('data-value')) + parseFloat($(item).find('.value').attr('data-value')) + parseFloat($(item).find('.extra-taxes').attr('data-extra-taxes')))
        .html(LI.format_currency($(this).attr('data-value')))
      ;
    });
  });
  
  var topay = $('#li_fieldset_simplified .cart .topay');
  var paid  = $('#li_fieldset_simplified .cart .paid');
  var total = $('#li_fieldset_simplified .cart .total');
  var rest = parseFloat(total.find('.value').attr('data-value')) - parseFloat(paid.find('.value').attr('data-value'));
  
  topay.find('.value').html(LI.format_currency(rest));
  

    if (rest < 0) 
    {
        $('.payment_missing').hide();
        $('.payment_change').show();
    } else {
        $('.payment_change').hide();
        $('.payment_missing').show();
    }  
  
}
