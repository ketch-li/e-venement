    $(document).ready(function(){
      $('.sf_admin_form_field_cities select').keyup(function(e){
        if ( e.which == 13 )
        {
          $('.sf_admin_form_field_country input[type=text]').focus();
          $(this).find('option:selected').click();
        }
      });
      
      var timeouts = [];
      $('.field.postalcode input, .sf_admin_form_field_postalcode input, .tdp-postalcode input').keyup(function(e){
        if ( $(this).val().length > 2 )
        {
          // clear previous timeouts;
          while ( (timeout = timeouts.shift()) !== undefined )
            clearTimeout(timeout);
          
          var elt = this;
          $(elt).addClass('waiting-wheel');
          
          // setTimeout to avoid burst requests
          timeouts.push(setTimeout(function(){
            $.ajax({
              url: $('.field.cities select, .sf_admin_form_field_cities select').attr('data-url'),
              method: 'get',
              data: { q: $(elt).val() },
              dataType: 'json',
              complete: function(){
                $(elt).removeClass('waiting-wheel');
              },
              success: function(json){
                if ( window.location.hash == '#debug' )
                  console.error('Zip results', json);
                
                $('.field.cities select, .sf_admin_form_field_cities select').html('');
                $.each(json, function(key, val) {
                  $('.field.cities select, .sf_admin_form_field_cities select')
                    .append($('<option>'+val+'</option>').val($.trim(key)))
                    .find('option:first-child').attr('selected',true);
                });
                $('.field.cities select option, .sf_admin_form_field_cities select option').click(function(){
                  if ($(this).val() != '...') {
                    $('.field.postalcode input, .sf_admin_form_field_postalcode input, .tdp-postalcode input').val($.trim($(this).val().replace(/.+ %%(\d+)%%$/,'$1')));
                    $('.field.city input, .sf_admin_form_field_city input, .tdp-city input').val($.trim($(this).val().replace(/ %%\d+%%$/,'')));      
                  }
                }).parent().change();
                
                if ( LI.zipcitiesOnZipLoaded != undefined )
                $.each(LI.zipcitiesOnZipLoaded, function(i, fct){
                  if ( typeof(fct) != 'function' )
                    return;
                  fct(json);
                });
              }
            });
          },300));
        }
        if ( e.which == 40 )
          $('.sf_admin_form_field_cities select').focus();
      });
    });

    if ( LI == undefined )
      var LI = {};
    if ( LI.zipcitiesOnZipLoaded == undefined )
      LI.zipcitiesOnZipLoaded = [];
