$(document).ready(function(){
  if (!( $('.field.street_name').length == 1
    && $('.field.postalcode').length == 1
    && $('.field.city').length == 1 ))
  {
    if ( window.location.debug == '#debug' )
      console.error('Nothing to check out');
    return;
  }
  
  var url = window.location.toString().replace(/^(.+\/).+\.php\/.*/,'$1')+'rp.php/geo_fr_street_base/ajaxPub'; // this is a trick to avoid writing this URL in the HTML code, so making this script more consistant / self-sufficient
  var ajaxurl = url;
    
  $('.field.street_name input, .field.street_number input').focus(function() {
    $('.field.street_name, .field.street_number').removeClass('autocompleting');
    $(this).closest('.field').addClass('autocompleting');
  });
    
  var timeouts = [];
  $('.field.street_name input, .field.street_number input').keypress(function(){
    var elt = this;
    
    // clear previous timeouts;
    while ( (timeout = timeouts.shift()) !== undefined )
      clearTimeout(timeout);
    
    // run a new one...
    timeouts.push(setTimeout(function(){
      if ( window.location.hash == '#debug' )
        console.error('Beginning address autocompletion')
      $(elt).closest('.field.street_name, .field.street_number').find('select').html('');
            
      // checks if there is something to check
      var city, zip;
      if ( (zip = $.trim($('.field.postalcode input').val())).length < 3 )
        return;
      if ( (city = $.trim($('.field.city input').val())).length < 3 )
        return;
      if ( (address = $.trim($('.field.street_name input').val())).replace("\n", '').length < 0 )
        return;
      
      $(elt).addClass('waiting-wheel');
      
      if ( window.location.hash == '#debug' )
        console.error('Maybe some autocomplete would be relevant...', ajaxurl);
      
      var ajaxParam = {
        city: city,
        zip: zip,
        address: address
      };

      if ( $(elt).closest('.field').hasClass('street_number') )
        ajaxParam['number'] = true;

      // autocomplete
      $.ajax({
        method: 'get',
        url: ajaxurl,
        data: ajaxParam,
        complete: function(){
          $(elt).removeClass('waiting-wheel');
        },
        success: function(json){
          if ( window.location.hash == '#debug' )
            console.error(json);
          if ( json.length == 0 ) {
            
            return;
          }
          
          var address;
          var addresses = $(elt).closest('.field.street_name, .field.street_number').find('select');
          $(elt).closest('.field').addClass('autocompleting');
          if ( !addresses.length )
          addresses = $('<select></select>').addClass('addresses').prop('size', 3)
            .hide().insertAfter($(elt).closest('.field.street_name input, .field.street_number input')).fadeIn()
            .each(function(){
              $(this).closest('.field.street_name, .field.street_number').find('select').not(this).remove();
            })
            .change(function(){
              var newAddr = $(this).val();
              $(this).closest('.field.street_name, .field.street_number').find('input').each(function(){
                var lines = $(this).val().split("\n");
                lines.pop();
                lines.push(newAddr);
                $(this).val(lines.join("\n"));
              });
              $(this).closest('.field.street_name, .field.street_number').find('select').fadeOut(function(){
                $(this).closest('.field.street_name, .field.street_number').removeClass('autocompleting');
                $(this).remove();
              });
              if ( $('.field.street_number').hasClass('autocompleting') ) {
                $('.field.address textarea').val($('.field.street_number input').val() + ' ' + $('.field.street_name input').val());
                $('.field.address').show();
                $('.field.street_number input, .field.street_name input').val('');
                $('.field.street_number, .field.street_name').val('').hide();
              } else {
                //$('.field.street_number input').val($('.field.street_name input').val());
                $('.field.street_number').show()
                $('.field.street_number input').focus();
                $('.field.street_number input').keypress();               
              }
            })
          ;
          while ( (address = json.shift()) )
            $('<option></option>').val(address).html(address)
              .appendTo(addresses);
        }
      });
    },300)); // the timeout
  });
});
