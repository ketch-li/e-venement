if ( LI == undefined )
  var LI = {};

$(document).ready(function(){
  var select = $('<select></select>')
    .prop('size', 2)
    .attr('data-url', $('.config.zip-url').attr('data-url'))
    .click(function(){ $(this).closest('.field').fadeOut(); })
  ;
  $('<p></p>').addClass('field').addClass('cities')
    .append('<label></label>')
    .append(select)
    .insertAfter('.field.city');
  
  $('.field.postalcode input').prop('autocomplete', 'off');
  
  // handler
  if ( LI.zipcitiesOnZipLoaded == undefined )
    LI.zipcitiesOnZipLoaded = [];
  LI.zipcitiesOnZipLoaded.push(function(json){
    if ( Object.keys(json).length == 1 )
    {
      $('.field.cities select option:first').click()
        .closest('.field').fadeOut();
      return;
    }
    $('.field.cities select')
      .prop('size', Object.keys(json).length < 10 ? Object.keys(json).length : 10)
      .closest('.field').fadeIn();
  });
});
