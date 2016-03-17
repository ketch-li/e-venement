  <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_form_shipping_fees">
    <div class="widget">
      <p>
        <?php echo __('To %%to%% from %%from%%: fees %%currency%%%%fees%%', array(
          '%%from%%'  => '<input type="number" name="min" value="" />',
          '%%to%%'    => '<input type="number" name="max" value="" />',
          '%%fees%%'  => '<input type="number" name="fees" value="" step="0.01" />',
          '%%currency%%' => $sf_context->getConfiguration()->getCurrency(),
        )) ?>
        <input type="submit" onclick="javascript: $(this).closest('p').remove(); return false;" name="remove" value="x" />
      </p>
    </div>
    <script type="text/javascript">
      $(document).ready(function(){
        $('.sf_admin_form_field_form_shipping_fees input[type=number]').click(function(){ return false; }).change(function(){
          if ( $(this).val() === '' )
            return;
          
          var completed = {};
          completed[$(this).prop('name')] = $(this).val();
          $(this).siblings().each(function(){
            if ( $(this).val() === '' )
              return;
            completed[$(this).prop('name')] = $(this).val();
          });
          
          if ( completed.fees == undefined )
            return;
          
          var target = $(this).parent().clone();
          var source = $(this).parent();
          $.each(['min', 'max', 'fees'], function(i, name){
            target.find('[name="'+name+'"]').val(source.find('[name="'+name+'"]').val());
            source.find('[name="'+name+'"]').val('');
          });
          target.insertBefore(source).find('input[type=submit]').show();
        });
        
        $('.sf_admin_form_field_form_shipping_fees input[type=submit]:last').hide();
        
        // completing the GUI with system values
        $.each(JSON.parse($('.sf_admin_form_field_shipping_fees textarea').val()), function(i, range){
          $.each(Object.keys(range), function(i, name){
            $('.sf_admin_form_field_form_shipping_fees .widget input:last').parent().find('[name="'+name+'"]').val(range[name]).change();
          });
        });
        
        $('.sf_admin_form form').submit(function(){
          var res = [];
          $('.sf_admin_form_field_form_shipping_fees .widget > *').each(function(){
            if ( $(this).find('[name="fees"]').length == 0 )
              return;
            
            var elt = this;
            var data = {};
            $.each(['min', 'max', 'fees'], function(i, name){
              data[name] = $(elt).find('[name="'+name+'"]').val();
            });
            if ( data.fees )
              res.push(data);
          });
          $('.sf_admin_form_field_shipping_fees textarea').val(JSON.stringify(res, null, 4));
        });
      });
    </script>
  </div>
