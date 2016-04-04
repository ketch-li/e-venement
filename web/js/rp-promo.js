$(document).ready(function(){
  $('.mod-promo_code .sf_admin_action_list .fg-button').prop('href',
    $('.sf_admin_action_list .fg-button').prop('href')+'?mct_id='+$('#member_card_type_promo_code_member_card_type_id').val()
  );

  // playing w/ promo codes to make it more ergonomic
  if ( $(this).closest('form').find('.sf_admin_form_field_promo_code_del').length > 0 )
  $('.mod-member_card_type .sf_admin_form_field_promo_codes input.promo-code-id').each(function(){
    
    // existing declinations
    var label = $(this).closest('table').find('.promo-code-name').parent();
    var del = $('<a></a>')
      .prop('href', $(this).closest('form').find('.sf_admin_form_field_promo_code_del').prop('href'))
      .attr('data-id', $(this).val())
      .prop('title', $.trim($(this).closest('form').find('.sf_admin_form_field_promo_code_del').text()))
      .prop('target', '_blank')
      .text($(this).closest('form').find('.sf_admin_form_field_promo_code_del').text())
      .addClass('fg-button').addClass('ui-state-default').addClass('fg-button-icon-left').addClass('ui-priority-secondary').addClass('li-delete')
      .prepend($('<span></span>').addClass('ui-icon').addClass('ui-icon-trash'))
      .mouseenter(function(){ $(this).addClass('ui-state-hover'); })
      .mouseleave(function(){ $(this).removeClass('ui-state-hover'); })
      .click(function(){
        var elt = this;
        LI.window_transition();
        $.get($(this).prop('href'), { promo_code_id: $(this).attr('data-id') }, function(){
          $('#transition .close').click();
          $(elt).closest('table').closest('tr').fadeOut(function(){ $(this).remove(); });
        });
        return false;
      })
    ;
    label.append(' ').append(del);
  });

});
