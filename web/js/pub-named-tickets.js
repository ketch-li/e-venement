// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  // click on the labels
  $('form.named-tickets label').click(function(){
    $(this).closest('span').find('select, input').first().focus();
  });
  $('form.named-tickets input, form.named-tickets :not(.cherry-pick) > select')
    .change(function(){
      // do not submit the form if a complete contact is not given for $(this)
      if ( $(this).closest('.contact_title, .contact_name, .contact_firstname, .contact_email').length > 0 )
      {
        var go = true;
        var del = $(this).closest('.contact').find('.contact_name input, .contact_firstname input, .contact_email input').length;
        $(this).closest('.contact').find('.contact_name input, .contact_firstname input, .contact_email input').each(function(){
          if ( !$.trim($(this).val()) )
          {
            del--;
            go = false;
          }
        });
        if ( !go && (del > 0 || $(this).closest('.contact').find('.contact_title select').val()) ) // everything is not filled && everything is not empty
        {
          $(this).closest('.contact').find('.contact_title label, .contact_name label, .contact_firstname label, .contact_email label')
           .css('color', 'red');
          return;
        }
      }
      
      $(this).closest('form').submit();
    })
  ;
  
  // complete the named tickets using an other manifestation
  $('#tickets .complete').click(function(){
    if ( window.location.hash == '#debug' && confirm('Open a new tab?') )
    {
      window.open($(this).prop('href'));
      return false;
    }
    
    var busy = LI.busyIndicator();
    $.ajax({
      method: 'get',
      url: $(this).prop('href'),
      success: function(data){
        if ( !data )
        {
          busy.stop();
          return;
        }
        if ( window.location.hash == '#debug' )
          console.error('Autocomplete named tickets', data);
        LI.pubNamedTicketsData(data, function(){ 
          busy.stop();
        });
      }
    });
    return false;
  });
  
  $('form.named-tickets').submit(function(){
    if ( location.hash == '#debug' )
    {
      $(this).prop('target', '_blank');
      setTimeout(function(){ $(this).prop('target', null); }, 1000);
      return true;
    }
    
    var busy = LI.busyIndicator();
    $.ajax({
      url: $(this).prop('action'),
      type: $(this).prop('method'),
      data: $(this).serialize(),
      success: function(json){
        LI.pubNamedTicketsData(json, function(){
          busy.stop();
        });
      }
    });
    $(this).find('.contact').find('.contact_title label, .contact_name label, .contact_firstname label, .contact_email label')
      .css('color', null);
    return false;
  });
});


LI.pubNamedTicketsInitialization = function()
{
  $('form.named-tickets').each(function(){
    var busy = LI.busyIndicator();
    $.get($(this).prop('action'), function(json){
      LI.pubNamedTicketsData(json, function(){ 
        busy.stop();
      });
    });
  });
}
LI.pubNamedTicketsData = function(json, callback)
{
  if ( window.location.hash == '#debug' )
    console.error(json);
  
  if (!( json.success && json.success.tickets ))
  {
    LI.alert('An error occurred with named tickets');
    if ( typeof callback == 'function' )
      callback();
    return;
  }
  
  var form = $('form.named-tickets');
  if ( Object.keys(json.success.tickets).length == 1 )
  $.each(json.success.tickets, function(id, ticket){
    if ( $('form.named-tickets#ticket-'+ticket.id).length > 0 )
      form = $('form.named-tickets#ticket-'+ticket.id);
  });
  
  form.find('.ticket:not(.sample)').remove();
  if ( json.success.tickets.length == 0 )
    form.fadeOut();
  else
    form.fadeIn();
  
  // reinit the previously selected seats
  $('.picture.seated-plan .seat.ordered.in-progress').removeClass('ordered').removeClass('in-progress');
  $('.picture.seated-plan .seat[data-ticket-id]').removeAttr('data-ticket-id');
  
  if ( json.success.tickets.length == 0 )
    $('#tickets .submit').hide();
  else
    $('#tickets .submit').show();
  
  $.each(json.success.tickets, function(id, ticket){
    var elt = form.find('.ticket.sample').clone(true)
      .removeClass('sample')
      .appendTo(form)
    ;
    if ( ticket.contact_id )
      elt.find('.cherry-pick').remove();
    $.each(['gauge_id', 'seat_id', 'price_id', 'contact_id'], function(key, field){
      elt.attr('data-'+field.replace('_','-'), ticket[field]);
    });
    $.each(['id', 'gauge_name', 'seat_name', 'value', 'taxes', 'contact_id', 'contact_title', 'contact_name', 'contact_firstname', 'contact_email', 'comment'], function(key, field){
      if ( elt.find('.'+field+' input, .'+field+' select').length > 0 )
      {
        elt.find('.'+field+' input, .'+field+' select').val(ticket[field]);
        elt.find('.'+field+' label').hide();
      }
      else
        elt.find('.'+field).text(ticket[field]);
    });
    if ( !ticket.seat_name )
      elt.find('.seat_label').hide();
    elt.find('input, select, button').each(function(){
     if ($(this).attr('name')) {
        $(this).attr('name', $(this).attr('name').replace('%%ticket_id%%', ticket.id));
        }
     });
    elt.find('.force').val(ticket['force']);
    
    // synthetic view or not
    if ( ticket.prices_list.length == 0 )
      elt.find('.price_name').text(ticket.price_name);
    else
    {
      // display the currently selected seat
      $('.picture.seated-plan .seat[data-id='+ticket.seat_id+']')
        .attr('data-ticket-id', ticket.id)
        .addClass('ordered').addClass('in-progress');
      $('<option value=""></option>').text('--'+$('#plans .infos .no-price').text()+'--').appendTo(elt.find('.price_name select'));
      $.each(ticket.prices_list, function(id, price){
        $('<option></option>').val(price.id).text(price.name)
          .appendTo(elt.find('.price_name select'));
      });
      elt.find('.price_name select').val(ticket.price_id);
      elt.find('.price_name select, .delete, .me').each(function(){
        $(this).attr('name', $(this).attr('name').replace('%%ticket_id%%', ticket.id));
      });
      
      // delete a ticket
      elt.find('.delete').unbind('click').click(function(){
        // set ticket price to null (triggers form submission)
        $(this).closest('.ticket').find('.price_name select').val('');
       
        // update the totals
        var gauge_id = $(this).closest('.ticket').data('gauge-id');
        var price_id = $(this).closest('.ticket').data('price-id');
        var $select = $('.prices .quantity select#price_'+gauge_id + '_' + price_id + '_quantity');
        $select.val( parseInt($select.val()) > 0 ? $select.val() - 1 : 0);
        $select.focusout(); // triggers totals computation
      });
      
      // put %%ME%% on a ticket, and no one on previous tickets that belonged to me
      elt.find('.me').unbind('click').click(function(){
        // reset the other tickets set to %%ME%%
        $('form.named-tickets .contact .id[value='+$(this).closest('.contact').find('.me').val()+']').each(function(){
          $(this).closest('.contact').find('.contact_title select, .contact_name input, .contact_firstname input, .contact_email input').val('').first().change();
        });
        
        // reset the current ticket & give it to "me"
        $(this).closest('.contact').find('input:not(.force)').val($(this).prop('title')).prop('disabled',true);
        $(this).closest('.contact').find('.contact_id input.force').val('true');
      });
    
      // playing w/ labels printed over inputs/selects
      elt.find('input, select')
        .unbind('focusout').unbind('focus')
        .focusout(function(){
          if ( $.trim($(this).val()) == '' )
          {
            $(this).val('');
            $(this).closest('span').find('label').css('display', '');
          }
        })
        .focus(function(){
          $(this).closest('span').find('label').hide();
        }).focusout() // the delay is needed to let the asynchronous bind finish
      ;
      setTimeout(function(){
        elt.find('input, select').focusout();
      },500);
      //$('#actions .register a, #tickets .submit button').focus();
      $('.prices .submit input').focus();
    }
  });
  
  if ( typeof callback == 'function' )
    callback();
}

LI.pubNamedTicketsCherryPick = function(elt){
  // fill in the contact
  var ticket = $(elt).closest('.ticket');
  $.each(['id', 'name', 'firstname', 'title', 'email'], function(i, field){
    ticket.find('.contact_'+field).find('select, input').val(
      $(elt).find('option:selected').attr('data-'+field)
    );
  });
  ticket.find('.contact_email input').change();
  
  // remove it from other lists
  if ( $(elt).val() ) {
    $(elt).closest('form').find('.cherry-pick option[value="'+$(elt).val()+'"]').remove();
    $(elt).closest('form').find('.cherry-pick').each(function(){
      if ( $(this).find('option').length == 1 )
        $(elt).closest('form').find('.cherry-pick').remove();
    });
  }
  
  return false;
}
