<?php use_helper('Date','Number') ?>
<?php use_javascript('helper?'.date('Ymd')) ?>
<script type="text/javascript" language="javascript">
  //print();
  //close();
</script>
<form class="get-a-pdf" action="" method="get"><p>
  <input type="submit" name="pdf" value="<?php echo __('Get a PDF',null,'li_accounting') ?>" />
  <?php foreach ( $sf_request->getGetParameters() as $name => $value ): ?>
    <input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>" />
  <?php endforeach ?>
</p></form>
<form class="send-by-email" action="" method="get"><p>
  <input type="submit" name="email" value="<?php echo __('Send by email',null,'li_accounting') ?>" />
  <?php foreach ( $sf_request->getGetParameters() as $name => $value ): ?>
    <input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>" />
  <?php endforeach ?>
</p></form>
<?php if ( isset($modifiable) && $modifiable ): ?>
<script type="text/javascript">
$(document).ready(function(){
  var currency = LI.get_currency($('#totals .pit .float').text());
  var fr_style = LI.currency_style($('#totals .pit .float').text()) == 'fr';
  
  $('form.inline-modifications').submit(function() {
    return false;
  });
  $('form.inline-modifications button').click(function() {
    $('.inline-modifiable').each(function() {
      if ( $(this).find('textarea').length == 0 )
        $(this).html($('<textarea name="inline-modifiable"></textarea>').val($(this).html()));
      else
      {
        $(this).html($(this).find('textarea').val());
        if ( $('[name="inline-modifiable"]').length == 0 )
        {
          $.post($('form.inline-modifications').attr('action'), {
            content: $('html').html(),
            invoice_id: $('#ids .invoice_id').text(),
            order_id: $('#ids .order_id').text()
          });
        }
      }
    });
    
    // on the current line
    $('#lines tbody .qty input').change(function(){
      $(this).closest('tr').find('.pit').html(LI.format_currency(
        LI.clear_currency($(this).closest('tr').find('.up').html()) * parseInt($(this).val()),
        true, fr_style, currency));
      $(this).closest('tr').find('.tep').html(LI.format_currency(
        LI.clear_currency($(this).closest('tr').find('.pit').html()) / (1 + parseFloat($(this).closest('tr').find('.vat .percent').html()) / 100),
        true, fr_style, currency));
      $(this).closest('tr').find('.vat .value').html(LI.format_currency(
        LI.clear_currency($(this).closest('tr').find('.pit').html()) - LI.clear_currency($(this).closest('tr').find('.tep').html()),
        true, fr_style, currency));
      
      // on totals
      $('#totals .vat:not(:first)').remove();
      $('#totals .vat span:first').html($('#lines thead .vat span').html()+':');
      var elt = this;
      $.each(['vat', 'tep', 'pit'], function(i, sel){
        $('#totals .'+sel+' .float').html(LI.format_currency(0, true, fr_style, currency));
        $('#lines tbody .'+sel+' .value').each(function(){
          $('#totals .'+sel+' .float').html(LI.format_currency(
            LI.clear_currency($('#totals .'+sel+' .float').html()) + LI.clear_currency($(elt).html()),
            true, fr_style, currency
          ));
        });
      });
    });
    return false;
  });
});
</script>

<form class="inline-modifications" method="post" action="<?php echo url_for('ticket/recordAccounting') ?>"><p><button name="inline-modification"><?php echo __('Modify on-the-fly', null, 'li_accounting') ?></button></p></form>
<?php endif ?>
