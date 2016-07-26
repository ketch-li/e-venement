if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  $('[type=text][value=""]:first').focus();
  
  $('[name=all][type=checkbox]').click(function(){
    $('[type=text]:first').focus();
  });
  
  $('form').submit(function(){
    if ( $('[name=all][type=checkbox]:checked').length == 0 )
      return true;
    
    var values = [];
    $('[type=text]').each(function(){
      if ( $.trim($(this).val()) )
        values.push($(this));
    });
    if ( values.length == 0 )
      return false;
    return LI.validate_rfid(values[0].val());
  });
});

LI.validate_rfid = function(rfid)
{
  var empty = [];
  $('[type=text]').each(function(){
    if ( !$.trim($(this).val()) )
      empty.push($(this));
  });
  
  if ( empty.length == 0 )
    return true;
  else $.each(empty, function(i, input){
    input.focus().val(rfid);
  });
  
  return true;
}
