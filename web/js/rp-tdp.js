// the global var that can be used everywhere as a "root"
if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  var object_elts = 'td:first-child:not([class]), .sf_admin_list_td_name, .sf_admin_list_td_firstname, .sf_admin_list_td_postalcode, .sf_admin_list_td_city, .sf_admin_list_td_list_emails, .sf_admin_list_td_list_phones, .sf_admin_list_td_organisms_list, .sf_admin_list_td_list_see_orgs, .sf_admin_list_td_list_contact, td:last-child';
  var subobjects_elts = '.sf_admin_list_td_list_professional_id, .sf_admin_list_td_list_organism, .sf_admin_list_td_list_professional, .sf_admin_list_td_list_organism_postalcode, .sf_admin_list_td_list_organism_city, .sf_admin_list_td_list_professional_emails, .sf_admin_list_td_list_organism_phones_list, .sf_admin_list_td_list_professional_description, .sf_admin_list_td_list_professional_groups_picto';
  
  // READ ONLY: deactivating every field if the user has no credential for modification
  if ( $('#tdp-top-bar .action.update[href=#]').length == 1 || $('body.action-archives').length == 1 )
  {
    $('#tdp-content input, #tdp-content select, #tdp-content textarea')
      .prop('disabled',true);
    $('#tdp-side-bar .tdp-object-groups .new').remove(); // this can be found a second time in LI.tdp_side_bar()
  }
  
  // LINKS TO TRANSACTIONS
  $('#tdp-side-ticketting').click(function(){ $(this).find('.transactions').fadeOut(100); });
  $('#tdp-side-ticketting .nb').click(function(){ var elt = this; setTimeout(function(){
    $(elt).closest('li').find('.transactions').fadeIn();
  },200); });
  
  // METAEVENTS
  $('#tdp-side-ticketting .metaevent:not(.hidden) .name').click(function(){ $(this).closest('.metaevent').find('.events, .seat-rank').slideToggle(); });
  $('#tdp-side-ticketting .metaevent.hidden .name').click(function(){
    var elt = $(this);
    if ( $(this).prop('href') == '#' )
    {
      LI.window_transition();
      return false;
    }
    
    $.get($(this).prop('href'), function(data){
      // stop transition
      $('#transition .close').click();
      
      // process data
      data = $.parseHTML(data);
      var melt;
      $(elt).closest('.metaevent').replaceWith(
        melt = $(data).find('.metaevent[data-meta-event-id="'+$(elt).closest('[data-meta-event-id]').attr('data-meta-event-id')+'"]'
      ))
      
      // display stuff
      $(melt).closest('.metaevent').find('.events, .seat-rank').slideDown();
    });
    
    $(elt).prop('href', '#');
    return false;
  });
  $('#tdp-side-ticketting .metaevent .seat-rank').click(function(){ $(this).fadeOut(); });
  
  // LINK TO RELATIONSHIPS
  $('.sf_admin_form_field_Relationships table table').each(function(){
    $(this).find('input[type=hidden]').each(function(){
      if ( /contact\[Relationships\]\[\d+\]\[url\]/.test($(this).attr('name')) )
      {
        relationship = $(this).closest('table');
        tfoot = $('<tfoot><tr><th></th><td><a><span class="ui-icon ui-icon-person"></span></a></td></tr></tfoot>');
        tfoot.prop('title', relationship.find('input[type=text]').val())
          .find('a').prop('href',$(this).val());
        tfoot.appendTo(relationship);
      }
   });
  });
  
  // BIRTHDAYS DEALING WITH INTEGERS AND TIPPING TIPS
  $('.sf_admin_form_field_YOBs table table tr').each(function(){
    // adding titles to YOBs' inputs
    $(this).prop('title', $(this).find('th label').html());
  });
  // navigation between birth date fields
  $('.sf_admin_form_field_YOBs table table tr input[type=text]').keyup(function(e){
    if ( e.which == 8 && $(this).val() == '' )
      $(this).closest('tr').prev().find('input[type=text]').focus();
  });  
  $('.sf_admin_form_field_YOBs table table tr:not(:last-child) input[type=text]').keyup(function(e){
    $(this).val(isNaN(parseInt($(this).val(),10)) ? '' : ($(this).val().substring(0,1) == '0' && $(this).val() !== '0' ? '0' : '')+parseInt($(this).val(),10));
    if ( $(this).val().length >= ($(this).css('width') === $(this).closest('table').find('tr:first input[type=text]').css('width') ? 2 : 4) )
      $(this).closest('tr').next().find('input[type=text]').focus();
  });
  // adding the captain's age to birth dates
  $('.sf_admin_form_field_YOBs table table').each(function(){
    inputs = $(this).find('input[type=text]');
    if ( inputs.eq(2).val() )
    {
      date   = new Date(inputs.eq(2).val(), inputs.eq(1).val()-1, inputs.eq(0).val());
      now    = new Date();
      nYears = now.getUTCFullYear() - date.getUTCFullYear();
      nMonth = now.getUTCMonth()    - date.getUTCMonth();

      plus = '';
      if ( nMonth < 0 )
      {
        nYears -= 1;
        nMonth  = 12+nMonth;
      }
      if ( nMonth >= 6 )
        plus = '½';

      $(this).append('<tfoot><tr><th></th><td>'+nYears+(nYears < 21 ? '<br/>'+plus : '')+'</td></tr></tfoot>');
    }
  });  
  
  // FORMS: submitting subobjects though AJAX
  $('.tdp-subobject form, .tdp-object #sf_admin_content > form').submit(function(){
    $("html, body").animate({ scrollTop: 0 }, "slow");
    $('.sf_admin_flashes *').fadeOut('fast',function(){ $(this).remove(); });
    LI.tdp_submit_forms();
    return false;
  });
  $('.tdp-subobject form').find('select, input, textarea').change(function(e){
    if ( e.originalEvent == undefined && !$(this).hasClass('open_list_selected') )
      return;
    $(this).attr('data-update', true);
  });
  
  // CONTENT: FOCUSING ON A FIELD
  highlight = $('#tdp-content #sf_admin_content input, #tdp-content #sf_admin_content select, #tdp-content #sf_admin_content textarea');
  highlight.focusin(function(){
    $(this).closest('span')
      .addClass('tdp-highlight');
  });
  highlight.focusout(function(){
    $(this).closest('span')
      .removeClass('tdp-highlight');
  });
  
  $('#tdp-content .tdp-object .tdp-professional_id').hide();
  $('#tdp-content .tdp-object .tdp-professional_id [name="organism[professional_id]"]').each(function(){
    var orig = $(this);
    var copy = $(this).clone(true)
      .prop('name', 'close-contact')
      .click(function(){
        if ( orig.prop('checked') )
          $(this).prop('checked', false);
        orig.prop('checked', $(this).prop('checked'));
        $('#tdp-content [name=close-contact]').prop('checked', false);
        $(this).prop('checked', orig.prop('checked'));
      })
      .appendTo(
        $('#tdp-content .tdp-subobject [data-id="'+$(this).val()+'"]')
          .closest('.tdp-subobject').find('h1')
      );
  });
  
  // CONTENT: MULTIPLE PROFESSIONALS
  $('#tdp-content .sf_admin_row').each(function(){
    $(this).prop('title', $.trim($(this).find('.sf_admin_list_td_description').html()));
    if ( (length = $(this).find('.sf_admin_list_td_list_organism .pro').length) > 1 )
    {
      // duplicating professional lines
      for ( i = 1 ; i < length ; i++ )
      {
        tr = $(this).clone(true);
        
        tr.find('> :not('+subobjects_elts+')')
          .remove();
        $(this).find('> :not('+subobjects_elts+')')
          .prop('rowspan',parseInt($(this).find('> :not('+subobjects_elts+')').prop('rowspan'),10)+1);
        
        $(this).after(tr);
      }
      
      // creating the different search cases for elements removal
      search = Array('.pro:first-child');
      for ( i = 0 ; i < length ; i++ )
        search.push(search[search.length-1]+' + .pro');
      
      tr = $(this);
      for ( i = 0 ; i < length ; i++ )
      {
        // the search path for elements to remove
        tmp = '';
        for ( j = 0 ; j < length ; j++ )
        if ( j != i )
        {
          if ( tmp != '' )
            tmp += ', ';
          tmp += search[j];
        }
        
        // removal
        tr.find(tmp)
          .remove();
        tr = tr.next();
      }
    }
  });
  
  // CONTENT: DELETING A SUBOBJECT
  $('.tdp-subobject .tdp-actions .tdp-delete').click(function(){
    elt = $(this).closest('.tdp-subobject');
    if ( elt.length != 1 )
      return false;
    
    if ( !confirm(elt.find('._delete_confirm').html()) )
    {
      $('#transition .close').click();
      return false;
    }
    
    elt.fadeOut('slow');
    $.ajax({
      url: $(this).prop('href'),
      type: 'POST',
      data: {
        sf_method: 'delete',
        _csrf_token: elt.find('._delete_csrf_token').html(),
      },
      complete: function(data) {
        elt.remove();
        $('#transition .close').click();
        $("html, body").animate({ scrollTop: 0 }, "slow");
        info = $('.tdp-object .sf_admin_flashes');
        info.replaceWith('<div class="sf_admin_flashes ui-widget"><div class="notice ui-state-highlight">Fonction supprimée.</div></div>');
        info.hide().fadeIn('slow');
        setTimeout(function(){ info.fadeOut('slow',function(){ info.remove(); }) },5000);
      },
      error: function(data,error) {
        elt.fadeIn('slow');
        $('#transition .close').click();
        info = elt.find('.sf_admin_flashes');
        info.replaceWith('<div class="sf_admin_flashes ui-widget"><div class="error ui-state-error">Impossible de supprimer la fonction... ('+error+')</div>');
        info.hide().fadeIn('slow');
        setTimeout(function(){ info.fadeOut('slow',function(){ info.remove(); }) },5000);
      },
    });
    
    return false;
  });
  
  // CONTENT: SEEING CONTACT'S ORGANISMS
  LI.tdp_show_orgs();
  if ( window.list_scroll_end == undefined )
    window.list_scroll_end = new Array();
  window.list_scroll_end[window.list_scroll_end.length] = LI.tdp_show_orgs;
  if ( window.integrated_search_end == undefined )
    window.integrated_search_end = new Array();
  window.integrated_search_end[window.integrated_search_end.length] = LI.tdp_show_orgs;
  
  // FILTERS
  $('#tdp-update-filters').get(0).blink = function(){
    $(this).addClass('blink');
    setTimeout(function(){ $('#tdp-update-filters').toggleClass('blink'); }, 330);
    setTimeout(function(){ $('#tdp-update-filters').toggleClass('blink'); }, 670);
    setTimeout(function(){ $('#tdp-update-filters').toggleClass('blink'); },1000);
    setTimeout(function(){ $('#tdp-update-filters').toggleClass('blink'); },1330);
    setTimeout(function(){ $('#tdp-update-filters').removeClass('blink'); },1670);
  };
  
  // SIDEBAR
  LI.tdp_side_bar();
  
  // Highlightening email addresses & addresses if they are "ticked" as invalid
  $('.tdp-line.internet .tdp-contact_email_npai input[type=checkbox], .tdp-line.complements .tdp-email_npai input[type=checkbox]').change(function(){
    $(this).prop('checked') ? $(this).closest('.tdp-line.complements, .tdp-line.internet').find('.tdp-email, .tdp-contact_email').addClass('bad') : $(this).closest('.tdp-line.complements, .tdp-line.internet').find('.tdp-email, .tdp-contact_email').removeClass('bad');
  }).change();
  $('.tdp-line.more .tdp-npai input[type=checkbox]').change(function(){
    $(this).prop('checked') ? $(this).closest('.tdp-object').find('.tdp-address').addClass('bad') : $(this).closest('.tdp-object').find('.tdp-address').removeClass('bad');
  }).change();
  
  // TOPBAR
  $('#tdp-top-bar .tdp-top-widget > a.group').mouseenter(function(){
    $(this).parent().find('.tdp-submenu').fadeIn('medium')
      .css('display','inline-block');
  });
  $('#tdp-top-bar .tdp-submenu, #tdp-top-bar .tdp-top-widget > a.group').mouseleave(function(){
    setTimeout(function(){
      if ( $('#tdp-top-bar .tdp-top-widget .tdp-submenu:hover').length + $('#tdp-top-bar .tdp-top-widget > a.group:hover').length == 0 )
        $('#tdp-top-bar .tdp-top-widget .tdp-submenu').fadeOut('fast');
    },500);
  });
  
  // ADDING CONTACTS TO GROUPS (from list)
  $('#tdp-content .sf_admin_row').unbind('click'); // remove framework bind
  $('#tdp-content .sf_admin_row > :not(.sf_admin_list_td_list_see_orgs)').click(function(){
    if ( $(this).is(subobjects_elts) )
    {
      // tick the box and highlight the professional only if there is something to deal with
      if ( $(this).closest('tr').find('.sf_admin_batch_checkbox[name="professional_ids[]"]').length > 0 )
      {
        $(this).closest('tr').find(subobjects_elts).toggleClass('ui-state-highlight');
        $(this).closest('tr').find('.sf_admin_batch_checkbox[name="professional_ids[]"]')
          .prop('checked',$(this).closest('tr').find(subobjects_elts).hasClass('ui-state-highlight'))
          .change();
      }
    }
    else
    {
      $(this).closest('tr').find('> :not('+subobjects_elts+')').toggleClass('ui-state-highlight');
      $(this).closest('tr').find('.sf_admin_batch_checkbox[name="ids[]"]')
        .prop('checked',$(this).closest('tr').find('> :not('+subobjects_elts+'):first').hasClass('ui-state-highlight'))
        .change();
    }
  });
  $('#tdp-content .sf_admin_batch_checkbox').change(function(){
    if ( $(this).closest('tr').find('.sf_admin_batch_checkbox[name="professional_ids[]"]:checked').length > 0 )
      $(this).closest('tr').find(subobjects_elts).addClass('ui-state-highlight');
    else
      $(this).closest('tr').find(subobjects_elts).removeClass('ui-state-highlight');
    
    $('#tdp-side-groups label').unbind('click');
    if ( $('.sf_admin_batch_checkbox:checked').length > 0 )
    {
      $('#tdp-side-bar').addClass('add-to');
      $('#tdp-side-groups input[type=checkbox]:checked').removeAttr('checked');
      $('#tdp-side-groups label').click(function(){
        $(this).closest('li').find('input[type=checkbox]').click().prop('checked',true);
        $.post($('#tdp-side-bar .batch-add-to.group').prop('href'),$('#tdp-side-bar form').serialize()+'&'+$('#tdp-content').serialize(),function(data){
          data = $.parseHTML(data);
          
          $('#tdp-content input[type=checkbox]:checked').click().removeAttr('checked').change();
          $('#tdp-side-groups input[type=checkbox]:checked').removeAttr('checked');
          $('.sf_admin_flashes').replaceWith($(data).find('.sf_admin_flashes').hide());
          $('.sf_admin_flashes').fadeIn('slow');
          setTimeout(function(){
            $('.sf_admin_flashes > *').fadeOut('slow',function(){
              $(this).remove();
            });
          },3000);
        });
        
        return false;
      });
    }
    else
    {
      $('#tdp-side-bar').removeClass('add-to');
      $('#tdp-side-bar label').unbind('click');
    }
  });
  $('#tdp-content #sf_admin_list_batch_checkbox').removeAttr('onclick').click(function(){
    // tick the box and highlight the contact only if there is something to deal with
    $('#tdp-content .sf_admin_batch_checkbox[name="ids[]"]').click();
  })
  .closest('tr').find('.sf_admin_list_th_list_professional_id').append('<input id="sf_admin_list_batch_checkbox_pro" type="checkbox" />');
  $('#tdp-content #sf_admin_list_batch_checkbox_pro').removeAttr('onclick').click(function(){
    // tick the box and highlight the professional only if there is something to deal with
    $('#tdp-content .sf_admin_batch_checkbox[name="professional_ids[]"]').click();
  });
  
  // no newsletter
  $('.tdp-email_no_newsletter, .tdp-contact_email_no_newsletter').each(function(){
    $(this).find('input').prop('title',$(this).find('label').html());
  });
  
  // hide culture if useless
  if ( $('.tdp-culture select option').length < 2 )
    $('.tdp-culture').hide();
        
  // slide down objects
  var showupfct = function(){
    $('#tdp-content .sf_admin_list .sf_admin_action_showup a').unbind().click(function(){
      return LI.tdp_open_object(this);
    });
  }
  showupfct();
  window.list_scroll_end.push(showupfct);
});

// CONTENT: SEEING CONTACT'S ORGANISMS
LI.tdp_show_orgs = function()
{
  $('#tdp-content .sf_admin_list_td_list_see_orgs').unbind().click(function(){
    if ( !$(this).closest('table').hasClass('see-orgs') )
    {
      $(this).closest('table').addClass('see-orgs');
      $(this).closest('table').find('.sf_admin_list_td_list_see_orgs span').removeClass('ui-icon-seek-next').addClass('ui-icon-seek-prev');
    }
    else
    {
      $(this).closest('table').removeClass('see-orgs');
      $(this).closest('table').find('.sf_admin_list_td_list_see_orgs span').removeClass('ui-icon-seek-prev').addClass('ui-icon-seek-next');
    }
    
    return false;
  });
  
  // reproducing the professional groups (remarkables) in the personal section
  $('.sf_admin_list .sf_admin_list_td_groups_picto .professional').remove();
  $('.sf_admin_list .sf_admin_row').each(function(){
    $(this).find('.sf_admin_list_td_list_professional_groups_picto .picto').clone()
      .addClass('professional')
      .appendTo($(this).find('.sf_admin_list_td_groups_picto'));
  });
  
  // Normalize the lines' heights
  seeorgs = $('#tdp-content table.see-orgs').length > 0;
  if ( seeorgs )
    $('#tdp-content .sf_admin_list_td_list_see_orgs:first').click();
  
  $('#tdp-content .sf_admin_list tr').each(function(){
    $(this).get(0).size = $(this).find('.sf_admin_list_td_name').height();
  });
  $('#tdp-content .sf_admin_list_td_list_see_orgs:first').click();
  $('#tdp-content .sf_admin_list tr').each(function(){
    if ( $(this).get(0).size < (height = $(this).find('.sf_admin_list_td_list_contact').height()) )
      $(this).get(0).size = height;
  });
  $('#tdp-content .sf_admin_list_td_list_see_orgs:first').click();
  $('#tdp-content .sf_admin_list tr').each(function(){
    $(this).height($(this).get(0).size+2);
  });
  
  if ( seeorgs )
    $('#tdp-content .sf_admin_list_td_list_see_orgs:first').click();
}

LI.tdp_submit_forms = function(i = 0)
{
  if ( i < $('.tdp-subobject form').length )
  {
    // checks if an update is required
    if ( $('.tdp-subobject form').eq(i).find('[data-update]').length == 0 )
    {
      LI.tdp_submit_forms(i+1);
      return;
    }
    
    $('.tdp-subobject form').eq(i).find('select[multiple] option').prop('selected',true);
    
    var id = $('.tdp-subobject form').eq(i).attr('data-id');
    $.ajax({
      url: $('.tdp-subobject form').eq(i).prop('action'),
      type: 'POST',
      data: $('.tdp-subobject form').eq(i).serialize()
    })
    .done(function(data) {
      data = $.parseHTML(data);
      
      // retrieving corresponding subobject
      var subobject = $('[name="professional_'+id+'[id]"][value="'+$(data).find('[name="professional_'+id+'[id]"]').val()+'"]')
        .closest('.sf_admin_edit');
      if ( subobject.length == 0 )
        subobject = $('.sf_admin_edit.tdp-object-new');
      
      // flashes
      subobject.find('.sf_admin_flashes')
        .replaceWith($(data).find('.sf_admin_flashes'));
      setTimeout(function(){
        $('[name="professional[id]"][value="'+$(data).find('[name="professional_'+id+'[id]"]').val()+'"]')
          .closest('.sf_admin_edit')
          .find('.sf_admin_flashes > .notice').fadeOut('medium',function(){ $(this).remove(); });
      },6000);
      
      // errornous fields
      if ( !subobject.hasClass('tdp-object-new') || subobject.find('.tdp-organism_id input, .tdp-contact_id input').val() != '' )
      $(data).find('.errors').each(function(){
        subobject.find('.tdp-'+$(this).closest('.sf_admin_form_row').prop('class').replace(/^.*sf_admin_form_field_([\w_]+).*$/g,'$1'))
          .addClass('ui-state-error').addClass('ui-corner-all')
          .append($(this));
      });
      
      i++;
      LI.tdp_submit_forms(i);
    })
    .fail(function(){
      i++;
      LI.tdp_submit_forms(i);
    });
  }
  else
  {
    $('.form_phonenumbers .sf_admin_flashes').remove();
    
    // included forms  
    $('.tdp-object form form').submit();
    
    if ( $('.tdp-subobject .errors').length == 0 ) // no error
      $('.tdp-object #sf_admin_content > form').unbind().submit();
    else // at least one error, stopping the process
    {
      $('#transition .close').click();
      $('.tdp-object .sf_admin_flashes').fadeOut('fast',function(){
        $(this).replaceWith(
          $('.tdp-subobject .errors').first()
            .closest('.tdp-subobject')
            .find('.sf_admin_flashes')
            .clone(true)
            .hide()
            .fadeIn('medium')
        );
      });
    }
  }
}

LI.tdp_open_object = function(elt)
{
  $('#transition').fadeIn();
  var tobeclosed = $(elt).hasClass('tdp-opened');
  $('#tdp-content .fg-button.tdp-opened').not(elt).click();
  
  if ( tobeclosed )
  {
    var tr = $(elt).closest('tr').next();
    tr.find('iframe').contents().find('#tdp-top-bar .update').click();
    setTimeout(function(){
      $('#transition .close').click();
      tr.remove();
      $(elt).removeClass('tdp-opened');
    },2000);
  }
  else
  {
    var colspan = $(elt).closest('tr').find('> td').length;
    var iframe = $('<div><iframe src="'+$(elt).prop('href')+'"></iframe></div>')
      .addClass('inner-edition').addClass('ui-widget').addClass('ui-corner-all');
    iframe.find('iframe').load(function(){
      $(elt).addClass('tdp-opened');
      $('#transition .close').click();
      $(this).contents().find('html').addClass('tdp-iframe');
      $(this).contents().find('a[href]').prop('target', '_parent');
      $(this).parent().slideDown('slow', function(){ $('#tdp-content .inner-actions').fadeIn(); });
    });
    $('<tr></tr>').addClass('tdp-content').addClass('ui-widget-content')
      .append($('<td colspan="'+colspan+'"></td>').append(iframe))
      .insertAfter($(elt).closest('tr'));
  }
  return false;
}

// FILTERS
LI.tdp_filters = function()
{
  // stolen in web/sfAdminThemejRollerPlugin/js/jroller.js
  $('.sf_admin_filter').dialog({
   autoOpen: false,
    width: $(document).width() > 1450 ? 1100 : 600,
    height: $(window).height() - 50,
    close: function(evt, ui){
      $('#sf_admin_filter_button').removeClass('ui-state-active');
    },
    open: function(evt,ui){
      $('#sf_admin_filter_reset, #sf_admin_filter_submit').hide();
      $('#sf_admin_filter_button').addClass('ui-state-active');
      setTimeout(function(){
        $('.ui-dialog-buttonpane button .ui-button-text').each(function(){
          if ( $(this).html() == 'Reset' )
            $(this).html($('#sf_admin_filter_reset').html());
          if ( $(this).html() == 'Filter' )
            $(this).html($('#sf_admin_filter_submit').val());
        });
      },200);
      $('#sf_admin_filter').animate({ scrollTop: 0 }, 'slow');
    },
    buttons: {
      'Filter': function() {
          $(this).dialog("close");
          $('#sf_admin_filter_submit').closest('form').submit();
        },
        'Reset': function() {
          $(this).dialog("close");
          location.href = $('#sf_admin_filter_reset').attr('href');
        }
      }
    });
}

// SIDEBAR
LI.tdp_side_bar = function()
{
  // READ ONLY: deactivating every field if the user has no credential for modification
  if ( $('#tdp-top-bar .action.update[href=#]').length == 1 )
    $('#tdp-side-bar .tdp-object-groups .new').remove();
  
  $('#tdp-side-bar li').each(function(){
    $(this).prop('title',$(this).find('label').html());
  });
  $('#tdp-side-bar input[type=checkbox]').click(function(){
    $('#tdp-update-filters').get(0).blink();
    if ( $(this).closest('.tdp-side-widget').is('#tdp-side-categories') )
      $('#sf_admin_filter .sf_admin_filter_field_organism_category_id select option[value="'+$(this).val()+'"]')
        .prop('selected',$(this).prop('checked'))
        .change();
    if ( $(this).closest('.tdp-side-widget').is('#tdp-side-groups') )
    {
      if ( $(this).prop('checked') )
        $('<option></option>')
          .val($(this).val())
          .prop('selected', true)
          .appendTo($('#sf_admin_filter .sf_admin_filter_field_groups_list select').change());
      else
        $('#sf_admin_filter .sf_admin_filter_field_groups_list select option[value="'+$(this).val()+'"]')
          .remove()
          .change();
    }
  });
  
  // integrated search
  $('#tdp-side-bar #list-integrated-search input[type=text]')
    .keydown(function(){
      if ( $(this).closest('#list-integrated-search').find('label').text() == $(this).val() )
        $(this).val('').removeClass('no-text');
    })
    .keyup(function(){
      if ( '' == $(this).val() )
      {
        $(this).val($(this).closest('#list-integrated-search').find('label').text())
          .prop('title',$(this).val())
          .addClass('no-text');
      }
    })
    .focusout(function(){
      $(this).keyup();
    })
    .keyup();
  if ( typeof LI.list_integrated_search_init == 'function' )
    LI.list_integrated_search_init();
  
  // filters
  $('#tdp-side-bar .filters').submit(function(){
    $('#sf_admin_filter form').submit();
    return false;
  });
  
  // emails
  $('#tdp-side-bar #tdp-side-emails .emails-object, #tdp-side-bar #tdp-side-emails .emails-subobject').each(function(){
    if ( $(this).find('.archive').length > 0 )
      $(this).find('.archive:last').after('<li class="more"><a href="#">...</a></li>');
  });
  $('#tdp-side-bar #tdp-side-emails .more a').click(function(){
    $(this).closest('.emails-object, .emails-subobject').find('.archive').fadeIn();
    $(this).remove();
    return false;
  });
}

// the auto-city / zip postalcode
setTimeout(function(){
  var focusout = function(elt){
    $(elt).closest('.sf_admin_form_row').fadeOut();
    $('.tdp-city input').focus();
    $('.tdp-postalcode input').focus();
  }
  
  if ( LI.zipcitiesOnZipLoaded == undefined )
    LI.zipcitiesOnZipLoaded = [];
  LI.zipcitiesOnZipLoaded.push(function(json){
    // choice of the only option available
    if ( Object.keys(json).length == 1 )
      return focusout($('.sf_admin_form_field_cities select option:first').click());
    // focusin if length > 1
    $('.sf_admin_form_field_cities select')
      .prop('size', Object.keys(json).length < 6 ? Object.keys(json).length : 6)
      .closest('.sf_admin_form_row').fadeIn();
    // focusout
    $('.sf_admin_form_field_cities select option').click(function(){ focusout(this) });
    // the placeholder
    $('.sf_admin_form_field_city .tdp-subtitle').css('z-index', -1);
  });
}, 750);
