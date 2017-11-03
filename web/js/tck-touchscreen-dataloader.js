LI.completeContentTriggers = [];
LI.completeContent = function(data, type, replaceAll = true)
{
  if ( typeof data != 'object' )
  {
    LI.alert('An error occured. Please try again.','error');
    return;
  }
  
  // PAYMENTS
  switch ( type ) {
  case 'payments':
    var content = $('#li_transaction_field_payments_list tbody');
    var template = content.find('tr.template');
    
    content.find('tr[data-payment-id]:not(.template)').remove();
    content.find('tr:not([data-payment-id])').show();
    
    if ( data.length == 0 )
    {
      LI.sumPayments();
      return false;
    }
    
    content.find('tr:not([data-payment-id])').hide();
    var total = 0;

    $.each(data, function(index, value){
      var tr = template.clone(true)
        .removeClass('template')
        .prop('title', value.detail ? value.detail : '');

      tr.find('[name="ids[]"]').val(value.id);
      tr.attr('data-payment-id', value.id);
      tr.find('.sf_admin_list_td_Method').html(value.method);
      tr.find('.sf_admin_list_td_list_value').html(LI.format_currency(parseFloat(value.value)));
      if ( value.delete_url )
      {
        tr.find('.sf_admin_td_actions .sf_admin_action_delete form').prop('action', value.delete_url);
        tr.find('.sf_admin_td_actions .sf_admin_action_delete a').prop('href', '#'+value.id);
      }
      else
        tr.find('.sf_admin_td_actions .sf_admin_action_delete').remove();
      tr.find('.sf_admin_td_actions .sf_admin_action_delete [name="transaction[payments_list][id]"]').val(value.id);
      
      var date = new Date(value.date.replace(' ','T'));
      tr.find('.sf_admin_list_td_list_created_at').html(date.toLocaleDateString());
      
      if ( value.translinked )
      {
        tr.addClass('cancellation');
        tr.prop('title', $('#li_transaction_field_close .payments .translinked').text().replace('%%id%%', value.translinked));
      }
      
      tr.appendTo(content);
      total += value.value;
    });
    
    LI.sumPayments();
    break;
  
  // MANIFESTATIONS & PRODUCTS & MUSEUM
  case 'store':
    var scan_product_code = $('#li_transaction_store').find('input[name="autocompleter"]').val();
  
  case 'manifestations':
  case 'museum':
    var wglobal = $('#li_transaction_'+type+' .families:not(.sample)'); // first element, parent of all
    
    if ( replaceAll )
    {
      wglobal = $('#li_transaction_'+type+' .families.sample').clone(true)
        .removeClass('sample');
      $('#li_transaction_'+type+' .families:not(.sample)').remove();
      wglobal.find('.family:not(.total)').remove();
      wglobal.insertBefore($('#li_transaction_'+type+' .footer'));
    }
    
    // manifestations / products
    $.each(data, function(id, pdt){
      if ( pdt.category === undefined )
        return;
      
      var wpdt = $('#li_transaction_'+type+' .families.sample .family:not(.total)').clone(true);
      var add = true;
      if ( $('#li_transaction_'+type+' #'+wpdt.prop('id')+pdt.id).length > 0 )
      {
        wpdt = $('#li_transaction_'+type+' #'+wpdt.prop('id')+pdt.id);
        add = false;
      }
      else
      {
        wpdt.prop('id', wpdt.prop('id')+pdt.id);
        wpdt.attr('data-family-id', pdt.id);
        wpdt.find('.item:not(.total)').remove();
        wpdt.find('h3 .fg-button.gauge').prop('href',pdt.gauge_url);
      }
      
      // keep the same manifestations for the next transaction
      var hashtag = '#'+type+'-'+pdt.id;
      if ( $('#li_transaction_field_new_transaction a.persistant').length > 0
        && $('#li_transaction_field_new_transaction a.persistant').prop('href').indexOf(hashtag) == -1 )
      $('#li_transaction_field_new_transaction a.persistant').prop('href',
        $('#li_transaction_field_new_transaction a.persistant').prop('href')+
        hashtag
      );
                                          
      // in progress: pdt
      wpdt.find('h3 .event').text(pdt.category).prop('href',pdt.category_url);
      wpdt.find('h3').css('background-color', pdt.color);
      // TODO (or not): declination_url
      
      // dates
      if ( pdt.happens_at )
      {
        var happens_at = new Date(pdt.happens_at.replace(' ','T'));
        var ends_at = pdt.ends_at ? new Date(pdt.ends_at.replace(' ','T')) : undefined;
        wpdt.find('h3 .happens_at').text(happens_at.toLocaleString().replace(/:\d\d( \w+){0,1}$/,'')).prop('href',pdt.product_url).prop('title', ends_at ? ends_at.toLocaleString().replace(/:\d\d \w+$/,'') : '');
      }
      else
        wpdt.find('h3 .happens_at').text(pdt.name).prop('href',pdt.product_url);
      
      // location
      if ( pdt.location )
        wpdt.find('h3 .location').text(pdt.location).prop('href',pdt.location_url);
      else
        wpdt.find('h3 .location').remove();
      
      if ( add )
        wpdt.insertBefore(wglobal.find('.family.total'));
      
      // gauges / declinations
      // sorting...
      var sort = {};
      $.each(pdt[pdt.declinations_name], function(index, declination){
        sort[declination.sort] = index;
      });
      // filling
      $.each(sort, function(sort, index){
        var declination = pdt[pdt.declinations_name][index];
        var wdeclination = $('#li_transaction_'+type+' .families.sample .item:not(.total)').clone(true);
        var add = true;
        if ( $('#li_transaction_'+type+' [data-'+declination.type+'-id="'+declination.id+'"]').length > 0 )
        {
          wdeclination = $('#li_transaction_'+type+' [data-'+declination.type+'-id="'+declination.id+'"]');
          add = false;
        }
        else
        {
          wdeclination.find('.declination').remove();
          wdeclination
            .attr('data-'+declination.type+'-id', declination.id)
            .attr('data-type', declination.type)
            .prop('id', wdeclination.prop('id')+declination.id)
          ;
        }
        
        wdeclination.find('h4').text(declination.name).prop('title', declination.name);
        
        // prices
        wdeclination.find('.data .available_prices').remove();
        $('<span></span>').addClass('available_prices').html(JSON.stringify(declination.available_prices))
          .appendTo(wdeclination.find('.data'));
        
        // graphical gauges
        switch ( type ) {
        case 'store':
        case 'museum':
        case 'manifestations':
          wdeclination.find('.data .gauge.raw').remove();
          $('<a></a>')
            .prop('href', declination.url)
            .addClass('gauge').addClass('raw')
            .appendTo(wdeclination.find('.data'));
          if ( declination.seated_plan_url && declination.seated_plan_seats_url )
          {
            var width = declination.seated_plan_width ? declination.seated_plan_width : '';
            wdeclination.find('.data .gauge.seated:not(.picture)').remove();
            $('<a></a>').addClass('gauge').addClass('seated')
              .prop('href', declination.seated_plan_seats_url)
              .append($('<img/>').prop('src', declination.seated_plan_url).prop('alt', 'seated-plan').attr('width', width))
              .appendTo(wdeclination.find('.data'));
          }
          break;
        }
        
        if ( add )
          wdeclination.insertBefore(wpdt.find('.item.total'));
        
        if ( declination.code == scan_product_code ) {
          $('.families .items div[data-declination-id="'+declination.id+'"]').click();
        }
        
        // in progress: prices
        if ( declination['prices'] != undefined )
        $.each(declination['prices'], function(index, price){
          // refresh related wips
          if ( price.qty == 0 )
          {
            if ( !price.id )
            {
              $('#li_transaction_'+type+' [data-'+declination.type+'-id="'+declination.id+'"] .declination.wip')
                .remove();
            }
            return;
          }
          var wprice = $('#li_transaction_'+type+' .families.sample .declination').clone(true);
          var add = true;
          if ( (tmp = wdeclination.find(str = '[data-price-id="'+price.id+'"].declination'+(price.state ? '.active.'+price.state : ':not(.active)'))).length > 0 )
          {
            wprice = tmp;
            add = false;
            wprice.find('.qty input').val(price.qty).select();
          }
          
          // check if price is available for this user
          var mod = false;
          $.each(declination.available_prices, function(k, p){
            if ( p.id === price.id )
              mod = true;
          });
          if ( !mod || price.state )
          {
            if ( parseInt(price.id)+'' === ''+price.id ) // everything but a Work In Progress price
              wprice.addClass('active');
            wprice.addClass(price.state ? price.state : 'readonly');
            if ( $.inArray(price.state, ['printed', 'cancelling']) > -1 || parseInt(price.id,10)+'' !== ''+price.id ) // every cancelling, printed or Work In progress price
              wprice.find('.qty input').prop('readonly', true).removeAttr('min');
          }
          wprice.find('.qty input').val(price.qty).select();
          wprice.find('.price_name').html(price.name).prop('title', price.description);
          wprice.find('.pit').html(LI.format_currency(price.pit));
          wprice.find('.vat').html(price.vat ? LI.format_currency(price.vat) : '-');
          wprice.find('.tep').html(LI.format_currency(price.tep));
          wprice.find('.extra-taxes').html(price['extra-taxes'] ? LI.format_currency(price['extra-taxes']) : '-');
          if ( price['item-details'] )
            wprice.find('.item-details a').prop('href', wprice.find('.item-details a').prop('href')+'?price_id='+price.id+'&'+declination.type+'_id='+declination.id);
          else
            wprice.find('.item-details a').remove();
          wprice.attr('data-price-id', price.id);
          if ( parseInt(price.id,10)+'' !== ''+price.id )
            wprice.addClass('wip');
          
          // ids & numerotation
          var ids = [];
          $.each(price.ids, function(index, value){
            var elt = price.ids_url && price.ids_url[index]
              ? $('<span></span>').text($.inArray(type, ['manifestations', 'museum']) != -1 && price.numerotation[index] ? ' '+price.numerotation[index] : '')
                .prepend($('<a></a>').prop('href', price.ids_url[index]).prop('target', '_blank').text(value))
              : $('<span></span>').text(value+( $.inArray(type, ['manifestations', 'museum']) && price.numerotation[index] ? ' '+price.numerotation[index] : '' ));
            ids.push($('<div></div>').append(elt.prepend('#').attr('data-id', value)).html());
          });
          wprice.find('.ids').html(ids.join(', '));
          
          if ( add )
            wprice.appendTo(wdeclination.find('.declinations tbody'));
        }); // each bunch of tickets
      }); // each declination
    }); // each pdt
    
    $('#li_transaction_'+type+' .item .total').select();
    
    if ( typeof LI.completeContentTriggers == 'object' )
    {
      $.each(LI.completeContentTriggers, function(id, fct){
        fct(type, data);
      });
    }
    
    break;
  
  default:
    console.log(type+' not implemented');
    break;
  }
  
  // hook for external plugins
  if ( LI.touchscreenContentLoad !== undefined )
  $.each(LI.touchscreenContentLoad, function(i, fct){
    fct(data, type, replaceAll);
  });
  
  return true;
}

LI.sumPayments = function()
{
  var val = 0;
  $('#li_transaction_field_payments_list tbody tr .sf_admin_list_td_list_value').each(function(){
    val += isNaN(parseFloat($(this).html().replace(',','.')))
      ? 0
      : LI.parseFloat($(this).html());
  });
  $('#li_transaction_field_payments_list tfoot .total .sf_admin_list_td_list_value')
    .html(LI.format_currency(val));
  
  var ratio = 1 - (val / LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.pit').html()));
  if ( isNaN(ratio) )
    ratio = 0;
  
  // difference
  var topay = LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.pit').html());
  var $elem = $('#li_transaction_field_payments_list tfoot .change .sf_admin_list_td_list_value.pit');
  var oldval = $elem.data('value');
  var rest = topay - val;
  $elem.html(LI.format_currency(rest)).data('value', rest);
  if ( oldval !== (rest))
    $elem.trigger('changeData');
  
    if (rest < 0) 
    {
        $('.payment_missing').hide();
        $('.payment_change').show();
        $('#li_transaction_field_payments_list .change').addClass('warning');
    } else {
        $('.payment_change').hide();
        $('.payment_missing').show();
        $('#li_transaction_field_payments_list .change').removeClass('warning');
    }  
  
  // VAT & co.
  var topay = LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.pit').html());
  $('#li_transaction_field_payments_list tfoot .change .sf_admin_list_td_list_value.vat').html(LI.format_currency(
    LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.vat').html())
    * ratio
  ));
  $('#li_transaction_field_payments_list tfoot .change .sf_admin_list_td_list_value.tep').html(LI.format_currency(
    LI.parseFloat($('#li_transaction_field_payments_list tfoot .topay .sf_admin_list_td_list_value.tep').html())
    * ratio
  ));
  
  // hidding content as it used to be
  if ( Cookie.has('tck.touchscreen.hidden-bunches') )
  {
    var settings = JSON.parse(Cookie.get('tck.touchscreen.hidden-bunches'));
    $.each(settings, function(id, hidden){
      if ( hidden && $('.bunch[data-bunch-id="'+id+'"] .families:not(.sample) .family:not(.total) .item').length == 0 )
        $('.bunch[data-bunch-id="'+id+'"]').addClass('small');
      else
        $('.bunch[data-bunch-id="'+id+'"]').removeClass('small');
    });
  }
}
