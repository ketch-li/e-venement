// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};
LI.pub_month_classes = ['.sf_admin_list_td_dates', '.sf_admin_list_td_list_locations', '.sf_admin_list_td_list_depends_on', '.sf_admin_list_td_list_depends_on_dates'];

if ( LI.pubCartReady == undefined )
  LI.pubCartReady = [];

LI.safari = function(href){
  var w = window.open(window.location,'safari','top=100, left=100, width=1, height=1, menubar=no, scrollbars=no');
  w.blur();
  window.focus();
  w.onload = function(){
    w.close();
    window.location = href;
  }
}

$(document).ready(function(){
  // safari + iframe workaround
  if ( /^((?!chrome).)*safari/i.test(navigator.userAgent) && window.top != window && !Cookie.get('pub.safari.not_first_time') )
  {
    $('a').click(function(){
      LI.safari($(this).prop('href'));
      LI.safari = function(){};
      Cookie.set('pub.safari.not_first_time', true);
      return false;
    });
  }
  
  // the cart widget
  $.get($('#cart-widget-url').prop('href'),function(data){
    $('body').prepend($($.parseHTML(data)).find('#cart-widget'));
    
    for ( i = 0 ; LI.pubCartReady[i] != undefined ; i++ )
      LI.pubCartReady[i]();
  });
  
  // Improving the display of the list of Manifestations
  LI.pubPictureRowspan();
  
  // Making the entire line of a Manifestation clickable
  if ( $('.mod-manifestation.action-index .sf_admin_list .sf_admin_list_td_list_tickets').length == 0 ) // if not in 'display tickets in the list of manifestations
  $('.mod-manifestation.action-index .sf_admin_list .sf_admin_row > *:not(.sf_admin_list_td_list_picture)').click(function(){
    window.location = $(this).closest('tr').find('.sf_admin_list_td_formatted_date a').prop('href');
    return false;
  });
  $('.mod-manifestation.action-index .sf_admin_list .sf_admin_row .sf_admin_list_td_formatted_date a').click(function(event){
    event.stopPropagation();
  });
  
  // adding the store at the end of meta events when it is appropriate
  if ( $('.mod-meta_event.action-index').length == 1
    && $('.mod-meta_event.action-index .sf_admin_list .sf_admin_row').length > 0
    && $('#ariane .event.with-store a.store').length == 1 )
    $('.mod-meta_event.action-index .sf_admin_list .sf_admin_row:last').clone()
      .toggleClass('odd').toggleClass('even')
      .insertAfter($('.mod-meta_event.action-index .sf_admin_list .sf_admin_row:last'))
      .find('td').html('').first().append($('#ariane .event.with-store a.store').clone());
  
  // auto-redirects
  if ( location.hash != '#debug' )
  {
    // if no event is available but the store is present, go to the store
    if ( $('.mod-event.action-index').length == 1
      && $('.mod-event.action-index .sf_admin_list table').length == 0
      && $('.mod-event.action-index #ariane .event.with-store').length > 0
      || $('.mod-meta_event.action-index').length == 1
      && $('.mod-meta_event.action-index .sf_admin_list .sf_admin_row').length == 0
      && $('#ariane .event.with-store a.store').length == 1 )
      window.location = $('#ariane .event.with-store a.store').prop('href');
    
    // redirect into the only meta-event of the @homepage if no alternative
    else if ( $('.mod-meta_event.action-index .sf_admin_list .sf_admin_row').length == 1 && $('.mod-meta_event.action-index .sf_admin_list .sf_admin_row img').length == 0 )
      window.location = $('.mod-meta_event.action-index .sf_admin_list .sf_admin_row a').prop('href');
  }
  
  // removing empty gauges
  $('.mod-manifestation.action-show .adding-tickets .gauge').each(function(){
    if ( $('.mod-manifestation.action-show .adding-tickets').length > 0 && $(this).find('[data-price-id]').length == 0 && $(this).find('.manifestation_full').length == 0 )
      $(this).remove();
  });
  
  // removing the useless "my cart" buttons
  if ( $('.mod-manifestation.action-show .adding-tickets .gauge').length > 1 )
    $('.mod-manifestation.action-show .adding-tickets .gauge:not(:last) tfoot tr:last').hide();
  
  // temporary flashes
  var time = $.trim($('.sf_admin_flashes').text()).length/18*1000;
  setTimeout(function(){
    $('.sf_admin_flashes > *').fadeOut(function(){ $(this).remove(); });
  }, time < 5000 ? 5000 : time);
  
  // focus on registering forms
  $('.mod-cart.action-register #login, #contact-form').focusin(function(){
    $('.mod-cart.action-register #login, #contact-form').removeClass('active');
    $(this).addClass('active');
  });
  $('#contact-form input[type=text]:first').focus();
  
  // if treating month as a structural data
  if ( $('.sf_admin_list .sf_admin_list_th_month').length > 0
    && $('.sf_admin_list .sf_admin_list_th_month').css('display') != 'none' )
  {
    // removing the ordering feature from the table's header
    $('.sf_admin_list th a').each(function(){
      $(this).closest('th').html($(this).html());
    });
    
    // dividing events by their manifestations' month (so there is a duplication of events if 2 manifs happen in 2 different month)
    var arr = [];
    $('.sf_admin_list tbody .sf_admin_list_td_month').each(function(){
      var evt = $(this).closest('.sf_admin_row');
      
      $(this).find('.month:not(:first)').each(function(){
        var nevt = evt.clone().insertAfter(evt);
        var month = evt.find('.month:last').clone().removeClass('month').prop('class');
        
        evt.find('.month:last').remove();
        nevt.find('.month:not(:last)').remove();
        $.each(LI.pub_month_classes, function(i, classname){
          nevt.find(classname+' li:not(.'+month+')').remove();
        });
        
        if ( arr.indexOf(month) == -1 )
          arr.push(month);
      });
      
      var month = '.'+evt.find('.month:first').clone().removeClass('month').prop('class');
      $.each(LI.pub_month_classes, function(i, classname){
        evt.find(classname+' li:not('+month+')').remove();
      });
    });
    
    // adding a class depending on current month on every event
    $('.sf_admin_list tbody .sf_admin_row').each(function(){
      var month = $(this).find('.sf_admin_list_td_month .month').clone().removeClass('month').prop('class');
      $(this).addClass(month);
    });
    
    // reordering globally using the event's month (class added recently)
    $.each(arr, function(i, month){
      var first = $('.sf_admin_list tbody .sf_admin_row.'+month+':first');
      $('.sf_admin_list tbody .sf_admin_row.'+month+':not(:first)').each(function(){
        $(this).insertAfter(first);
      });
    });
    
    // reordering inside the month groups, by the date of the first manifestation
    $.each(LI.pub_month_classes, function(i, classname){
      $('.sf_admin_list tbody .sf_admin_row '+classname+' li:first-child').each(function(){
        var cur = parseInt($(this).attr('data-time'));
        var next = parseInt($(this).closest('.sf_admin_row').next().find(classname+' li:first').attr('data-time'));
        if ( cur > next )
          $(this).closest('.sf_admin_row').next().insertBefore($(this).closest('.sf_admin_row'));
      });
    });
    
    // grouping by month
    var month = '';
    var colspan = $('.sf_admin_list thead tr:first th').length;
    $('.sf_admin_list tbody .sf_admin_list_td_month').each(function(){
      if ( month != $(this).find('.month:first').html() )
      {
        month = $(this).find('.month:first').html();
        $('<tr></tr>').addClass('sf_admin_month').insertBefore($(this).closest('tr'))
          .append($('<td></td>').html(month).prop('colspan', colspan));
      }
      $(this).html('');
    });
  }
  
  // if treating day as a structural data (in the manifestations list)
  if ( $('.sf_admin_list .sf_admin_list_th_happens_at_time_h_r').length > 0
    && $('.sf_admin_list .sf_admin_list_th_happens_at_time_h_r').css('display') != 'none' )
  {
    // dividing manifestations by their day
    var arr = {};
    $('.sf_admin_list tbody .sf_admin_list_td_list_happens_at').each(function(){
      var evt = $(this).closest('.sf_admin_row');
      var d = /^(\d\d\d\d)-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d)$/.exec($.trim($(this).text()));
      var date = new Date(d[1], parseInt(d[2],10)-1, d[3], d[4], d[5], d[6]);
      var tmp = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+date.getDate();
      
      if ( arr[tmp] == undefined )
        arr[tmp] = [];
      arr[tmp].push(evt);
    });
    
    var colspan = $('.sf_admin_list tbody tr:first td').length;
    var mydates = Object.keys(arr);
    mydates.sort().reverse();
    $.each(mydates, function(i, key){
      var d = /^(\d\d\d\d)-(\d\d)-(\d\d)$/.exec(key);
      mydate = new Date(d[1], parseInt(d[2],10)-1, d[3]);
      var td = $('<td colspan="'+colspan+'"></td>').text(
        $.trim(arr[key][0].find('.sf_admin_list_td_list_day_name').text())
        +' '+
        mydate.getDate()+'/'+(mydate.getMonth()+1)
      );
      var tr = $('<tr></tr>')
        .addClass('sort-by-day')
        .append(td)
        .prependTo($('.sf_admin_list tbody'))
        .after(arr[key]);
    });
  }
  
  // underlining same lines in different <td>s in the same <tr>
  $('.sf_admin_list .no-bullet a').mouseenter(function(){
    $(this).closest('tr')
      .find('.no-bullet li:nth-child('+($(this).closest('li').index()+1)+')')
      .addClass('highlight');
  });
  $('.sf_admin_list .no-bullet a').mouseleave(function(){
    $(this).closest('tr').find('.no-bullet li')
      .removeClass('highlight');
  });
  
  // change quantities in manifestations list
  $('.sf_admin_list_td_list_tickets .qty input').focusout(function(){
    $(this).closest('form').submit();
    LI.manifCalculateTotal(this);
  });
  LI.manifCalculateTotal();
  $('.sf_admin_list_td_list_tickets form').submit(function(){
    if ( location.hash == '#debug' )
    {
      $(this).prop('target', '_blank');
      return true;
    }
    else
      $(this).prop('target', '');
    
    $.ajax({
      type: $(this).prop('method'),
      url: $(this).prop('action'),
      data: $(this).serialize(),
      success: function(json){
        $('.sf_admin_list_td_list_tickets form .qty input').val(0);
        if ( json.message )
          LI.alert(json.message, 'error');
        
        if ( !json.tickets || json.tickets.length == 0 )
          return;
        
        $.each(json.tickets, function(gauge_id, price){
          $.each(price, function(price_id, qty){
            $(str = '.sf_admin_list_td_list_tickets [data-gauge-id='+gauge_id+'] [data-price-id='+price_id+'] .qty input').val(qty);
          });
        });
      }
    });
    return false;
  });
  
  // terms & conditions
  $('#contact-form .terms_conditions input').change(function(){
    if ( $(this).is(':checked') )
      $(this).closest('p').removeClass('error');
    else
      $(this).closest('p').addClass('error');
  });
});

LI.manifCalculateTotal = function(elt){
  if ( elt == undefined )
    elt = $('.sf_admin_list_td_list_tickets .qty input');
  $(elt).each(function(){
    $(this).closest('form').find('.total').html(
      LI.format_currency(parseInt($(this).val(),10) * parseFloat($(this).closest('form').find('.value').text()))
    );
  });
}

LI.pubPictureRowspan = function()
{
  if ( $('.mod-manifestation.action-index .sf_admin_list tr.sf_admin_row').length > 0 )
  {
    var pic = $('.mod-manifestation.action-index .sf_admin_list tr.sf_admin_row .sf_admin_list_td_list_picture:not([rowspan]):not(.picture-done)')[0];
    $(pic).prop('rowspan',$(pic).closest('tbody').find('.sf_admin_list_td_list_picture [data-event-id="'+$(pic).find('[data-event-id]').attr('data-event-id')+'"]').length);
    $(pic).addClass('picture-done');
    $(pic).closest('tbody')
      .find('.sf_admin_list_td_list_picture:not(.picture-done) [data-event-id="'+$(pic).find('[data-event-id]').attr('data-event-id')+'"]')
      .closest('.sf_admin_list_td_list_picture')
      .remove();
  }
  else
  {
    $('#command tbody tr.tickets')
      .addClass('picture-to-merge');
    var trs;
    for ( i = 0 ; (trs = $('#command tbody tr.picture-to-merge')).length > 0 && i < 200 ; i++ ) // var i is a protection against infinite loops
    {
      var tr = trs.first();
      tr.find('td.picture').prop('rowspan', trs.parent().find('[data-manifestation-id='+tr.attr('data-manifestation-id')+']').length);
      tr.parent().find('[data-manifestation-id='+tr.attr('data-manifestation-id')+']').removeClass('picture-to-merge').not(tr).find('td:first').hide();
    }
  }
}

