$(document).ready(function(){
  if (!( $('.sf_admin_form_field_address').length == 1 && $('.sf_admin_form_field_postalcode').length == 1 && $('.sf_admin_form_field_city').length == 1 ))
    return;
  
  var timeouts = [];
  $('.sf_admin_form_field_address textarea').keypress(function(){
    var elt = this;
    
    // clear previous timeouts;
    while ( (timeout = timeouts.shift()) !== undefined )
      clearTimeout(timeout);
    
    // run a new one...
    timeouts.push(setTimeout(function(){
      if ( window.location.hash == '#debug' )
        console.error('Beginning address autocompletion')
      $(elt).closest('.tdp-address').find('select').remove();
      
      // checks if there is something to check
      var city, zip;
      if ( (zip = $.trim($('.sf_admin_form_field_postalcode input').val())).length < 3 )
        return;
      if ( (city = $.trim($('.sf_admin_form_field_city input').val())).length < 3 )
        return;
      if ( (address = $.trim($('.sf_admin_form_field_address textarea').val())).replace("\n", '').length < 5 )
        return;
      
      $(elt).addClass('waiting-wheel');
      
      if ( window.location.hash == '#debug' )
        console.error('Maybe some autocomplete would be relevant...');
      
      // autocomplete
      $.ajax({
        method: 'get',
        url: '/rp_dev.php/geo_fr_street_base/ajax',
        data: {
          city: city,
          zip: zip,
          address: address
        },
        complete: function(){
          $(elt).removeClass('waiting-wheel');
        },
        success: function(json){
          if ( window.location.hash == '#debug' )
            console.error(json);
          if ( json.length == 0 )
            return;
          
          var address;
          var addresses = $('<select></select>').addClass('addresses').prop('size', 3)
            .appendTo($(elt).closest('.tdp-address'))
            .change(function(){
              var newAddr = $(this).val();
              $(this).closest('.tdp-address').find('textarea').each(function(){
                var lines = $(this).val().split("\n");
                console.error(lines);
                lines.pop();
                console.error(lines);
                lines.push(newAddr);
                console.error(lines);
                $(this).val(lines.join("\n"));
              });
              $(this).remove();
            });
          while ( (address = json.shift()) )
            $('<option></option>').val(address).html(address)
              .appendTo(addresses);
        }
      });
    },300)); // the timeout
  });
});
