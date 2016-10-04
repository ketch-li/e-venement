$(document).ready(function(){
  // when clicking on a text field, the related radio button is selected automatically
  $('#periodicity_behaviour input[type=text], #periodicity_behaviour input.ui-datepicker-trigger').click(function(){
    if ( !$(this).parent().find('input[type=radio]').prop('checked') )
      $(this).parent().find('input[type=radio]').prop('checked',true).change();
  });
  
  // when selecting a radio button, cursor goes directly to the next text field
  $('#periodicity_behaviour input[type=radio]').change(function(){
    $(this).parent().find('input[type=text]:first').focus();
    
    // if "one_occurrence" is selected, then the "repeat every" fields are deactivated
    if ( $(this).val() == 'one_occurrence' )
      $('#periodicity_repeat input').prop('disabled',true);
    else
      $('#periodicity_repeat input').prop('disabled',false);
  }).first().change();

  //Force focus to time input after date has been picked
  $('#periodicity_one_occurrence_year').change(function(){
    $('#periodicity_one_occurrence_hour').focus();
  });

  $('#days, #days input').click(function(){
    $('#time input').prop('disabled', true);
    $('#days input').prop('disabled', false);
  });

  $('#time, #time input').click(function(){
    $('#days input').prop('checked', false);
    $('#days input').prop('disabled', true);
    $('#time input').prop('disabled', false);
  });
});
