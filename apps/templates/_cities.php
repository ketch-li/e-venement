<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_cities">
  <select name="cities" size="3">
    <option></option>
  </select>
  <script type="text/javascript">
    $(document).ready(function(){
      $('.sf_admin_form_field_cities select').keyup(function(e){
        if ( e.which == 13 )
        {
          $('.sf_admin_form_field_country input[type=text]').focus();
          $(this).find('option:selected').click();
        }
      });
      
      var timeouts = [];
      $('.sf_admin_form_field_postalcode input, .tdp-postalcode input').keyup(function(e){
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
              url: '<?php echo cross_app_url_for('rp','postalcode/ajax') ?>',
              method: 'get',
              data: { q: $(elt).val() },
              dataType: 'json',
              complete: function(){
                $(elt).removeClass('waiting-wheel');
              },
              success: function(json){
                $('.sf_admin_form_field_cities select').html('');
                $.each(json, function(key, val) {
                  $('.sf_admin_form_field_cities select')
                    .append('<option value="'+key+'">'+val+'</option>')
                    .find('option:first-child').attr('selected',true);
                });
                $('.sf_admin_form_field_cities select option').click(function(){
                  $('.sf_admin_form_field_postalcode input, .tdp-postalcode input').val($(this).val().replace(/.+ %%(\d+)%%$/,'$1'));
                  $('.sf_admin_form_field_city input, .tdp-city input').val($(this).val().replace(/ %%\d+%%$/,''));
                });
              }
            });
          },300));
        }
        if ( e.which == 40 )
          $('.sf_admin_form_field_cities select').focus();
      }).keyup();
    });
  </script>
</div>
