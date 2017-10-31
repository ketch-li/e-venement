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
*    Copyright (c) 2006-2016 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
    $(document).ready(function(){
      // first table
      $.get($('#sf_fieldset_statistics .meta-data-url').prop('href'), function(json){
        $.each(json, function(bunch, data){
          $.each(data, function(id, value) {
            if ( typeof(value) == 'object' )
            {
              $.each(value, function(i, user){
                $('<td></td>')
                  .text(user.nb)
                  .prop('title', user.user)
                  .addClass('id-'+i)
                  .attr('data-user-num', i)
                  .appendTo($('#sf_fieldset_statistics .'+bunch+' .'+id));
                if ( $('#sf_fieldset_statistics .'+bunch+' thead .id-'+i).length == 0 )
                  $('<td></td>').addClass('id-'+i)
                    .attr('data-user-num', i)
                    .text(user.user).addClass('ui-state-default').addClass('ui-th-column')
                    .prop('title', user.user)
                    .appendTo($('#sf_fieldset_statistics .'+bunch+' thead tr'));
              });
            }
            else
              $('#sf_fieldset_statistics .'+bunch+' .'+id+' td').text(value);
          });
          
          $('#sf_fieldset_statistics .'+bunch+' thead [data-user-num]').each(function(){
            var nb = 0;
            $(this).closest('table').find('tbody [data-user-num="'+$(this).attr('data-user-num')+'"]').each(function(){
              nb += parseInt($(this).text(),10);
            });
            if ( nb == 0 )
              $('#sf_fieldset_statistics .'+bunch+' [data-user-num="'+$(this).attr('data-user-num')+'"]').hide();
          });
        });
      });
      
      // second table make'up
      $('#sf_fieldset_statistics .filling-complete .min, #sf_fieldset_statistics .filling-complete .max')
        .mouseenter(function(){
          if ( $(this).hasClass('max') )
            $(this).prev().addClass('ui-state-hover');
          if ( $(this).hasClass('min') )
            $(this).next().addClass('ui-state-hover');
        })
        .mouseleave(function(){
          if ( $(this).hasClass('max') )
            $(this).prev().removeClass('ui-state-hover');
          if ( $(this).hasClass('min') )
            $(this).next().removeClass('ui-state-hover');
        })
        .click(function(){
          var elt = this;
          setTimeout(function(){
            var sibling;
            if ( $(elt).hasClass('max') )
              sibling = $(elt).prev();
            if ( $(elt).hasClass('min') )
              sibling = $(elt).next();
            if ( sibling == undefined )
              return;
            
            if ( $(elt).hasClass('ui-state-highlight') )
              sibling.addClass('ui-state-highlight');
            else
              sibling.removeClass('ui-state-highlight');
          },100);
        })
      ;
      
      // second table engine
      $.ajax({
          // initial data
          url: $('#sf_fieldset_statistics .filling-data-url').prop('href'),
          success: LI.statsCompleteFillingData,
          //error: function(){ LI.alert('An error occurred', 'error'); $('#transition .close').click(); }
      });
      $('#sf_fieldset_statistics .tab-print a').click(function(){
        // force refresh
        LI.window_transition();
        $.ajax({
          url: $(this).prop('href'),
          success: LI.statsCompleteFillingData,
          error: function(){ LI.alert('An error occurred', 'error'); $('#transition .close').click(); }
        });
        return false;
      });
    });

if ( LI == undefined )
  var LI = {};
LI.statsCompleteFillingData = function(json)
{
  var currency = LI.get_currency($('#sf_fieldset_unbalanced tfoot .nb').html());
  var fr_style = LI.currency_style($('#sf_fieldset_unbalanced tfoot .nb').html()) == 'fr';

  // showing optional data by default
  $('#sf_fieldset_statistics .filling-complete .sf_admin_row.held').show();
  $('#sf_fieldset_statistics .filling-complete').find('td').show();
  
  $('#sf_fieldset_statistics .filling-complete .min + .max .nb').closest('td').hide();
  $('#sf_fieldset_statistics .filling-complete .min + .max .th').hide();
  $('#sf_fieldset_statistics .filling-complete .min .nb').closest('td').prop('rowspan', 2);
  
  // hidding cols / rows if useless
  if ( location.hash != '#debug' )
  {
    if ( json.seats.free.all.nb + json.seats.ordered.all.nb + json.seats.printed.all.nb + json.seats.held.all.nb == 0 )
      $('#sf_fieldset_statistics .filling-complete').find('.f-st-ag, .sos-st-ag, .f-st-sg, .sos-st-sg, .f-st-og, .sos-st-og').hide();
    if ( json.seats.free.all.nb  + json.seats.ordered.all.nb  + json.seats.printed.all.nb + json.seats.held.all.nb
      == json.gauges.free.all.nb + json.gauges.ordered.all.nb + json.gauges.printed.all.nb )
      $('#sf_fieldset_statistics .filling-complete').find('.f-at-ag, .sos-at-ag, .f-at-sg, .sos-at-sg, .f-at-og, .sos-at-og').hide();
    if ( json.seats.held.all.nb == 0 )
      $('#sf_fieldset_statistics .filling-complete .sf_admin_row.held').hide();
    if ( json.seats.closed.all.nb == 0 )
      $('#sf_fieldset_statistics .filling-complete .sf_admin_row.closed').hide();
  }
  
  // this is a super-powerful compression of the "data dispatcher", to avoid hidden bugs as much as we can
  $.each({ seats: 'st', gauges: 'at' }, function(type, tckprefix){
  $.each(['free', 'closed', 'ordered', 'printed', 'held', 'total', 'not-free'], function(i, data){
  $.each({ online: 'og', onsite: 'sg', all: 'ag' }, function(state, gaugeprefix){
    var nb;
    var calculated = {
      total: {
        nb: json[type].free[state].nb + json[type].ordered[state].nb + json[type].printed[state].nb + json[type].held[state].nb,
        min: {
          money: json[type].free[state].min.money + json[type].ordered[state].money + json[type].printed[state].money + json[type].held[state].money,
          money_txt: LI.format_currency(json[type].free[state].min.money + json[type].ordered[state].money + json[type].printed[state].money + json[type].held[state].money, false, fr_style, currency)
        },
        max: {
          money: json[type].free[state].max.money + json[type].ordered[state].money + json[type].printed[state].money + json[type].held[state].money,
          money_txt: LI.format_currency(json[type].free[state].max.money + json[type].ordered[state].money + json[type].printed[state].money + json[type].held[state].money, false, fr_style, currency)
        },
      },
      'not-free': {
        nb: json[type].ordered[state].nb + json[type].printed[state].nb + json[type].held[state].nb,
        money: json[type].ordered[state].money + json[type].printed[state].money + json[type].held[state].money,
        money_txt: LI.format_currency(json[type].ordered[state].money + json[type].printed[state].money + json[type].held[state].money, false, fr_style, currency)
      }
    }
    if ( data != 'total' && data != 'not-free' )
      nb = json[type][data][state].nb;
    else if ( data == 'total' )
      nb = calculated['total'].nb;
    else if ( data == 'not-free' )
      nb = calculated['not-free'].nb;
    
    // numbers
    $('#sf_fieldset_statistics .filling-complete .'+data+' .f-'+tckprefix+'-'+gaugeprefix+' .nb')
      .text(nb);
    $('#sf_fieldset_statistics .filling-complete .'+data+' .f-'+tckprefix+'-'+gaugeprefix+' .percent')
      .text(LI.format_currency(100 * nb / calculated['total'].nb, false, true, ''), fr_style, currency);
    
    // money
    $.each(['min', 'max'], function(id, key){
      if ( data != 'total' && data != 'not-free' )
      {
        var value = json[type][data][state][key];
        if ( value == undefined )
        {
          key = '';
          value = json[type][data][state];
        }
      }
      else if ( data == 'total' )
        value = calculated['total'][key];
      else if ( data == 'not-free' )
      {
        key = '';
        value = calculated['not-free'];
      }
      
      $('#sf_fieldset_statistics .filling-complete .'+data+(key ? '.'+key : '')+' .sos-'+tckprefix+'-'+gaugeprefix+' .money')
        .text(value.money_txt);
      $('#sf_fieldset_statistics .filling-complete .'+data+(key ? '.'+key : '')+' .sos-'+tckprefix+'-'+gaugeprefix+' .percent')
        .text(LI.format_currency(100 * value.money / ( key && data == 'free' ? calculated['total'][key].money : calculated['total'].max.money ), false, true, ''), fr_style, currency);
    });
  }); // type
  }); // data
  }); // state
  
  $('#transition .close').click();
}
