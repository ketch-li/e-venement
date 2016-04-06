<?php use_javascript('helper') ?>
<script type="text/javascript">
  function ticket_payment_form(data)
  {
    data = $.parseHTML(data);
    
    // adding the form
    $(data).find('.sf_admin_form form').prependTo('#payment');
    $('#payment form #payment_transaction_id').val('<?php echo $transaction->id ?>');
    $('#payment form').addClass('ui-widget-content ui-corner-all').append('<p class="submit"><input type="submit" name="submit" value="<?php echo __('Add') ?>" /><input type="hidden" name="_save_and_add" value="" /></p>');
    if ( $(data).find('form select[name="payment[member_card_id]"]').length > 0 )
    {
      a = $('<a href="<?php echo url_for('payment/new') ?>" class="reset"><?php echo __('Reset','','sf_admin') ?></a>');
      a.click(function(){
        $.get($(this).attr('href'),function(data){
          $('#payment form').remove();
          ticket_payment_form(data);
          ticket_payment_old(true);
        });
        return false;
      });
      $('#payment form .submit').append(a);
    }
    $('#payment form .sf_admin_actions_block').remove();
    $('#payment form .label > *').each(function(){
      var tmp = $(this).parent();
      tmp.parent().prepend($(this));
      tmp.remove();
    });
    
    // adding shortcuts
    var shortcuts = $('<p></p>');
    $('#payment form #payment_payment_method_id option').each(function(){
      if ( $(this).val() != '' )
      {
        var content = $(this).html()
          .replace(/\//g,' ')
          .replace(/\s*([a-zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ])[a-zàáâãäåçèéêëìíîïðòóôõöùúûüýÿ/]*/ig,"$1.").toUpperCase();
        shortcuts.append('<button name="'+$('#payment #payment_payment_method_id').attr('name')+'" value="'+$(this).val()+'" title="'+$(this).html()+'">'+content+'</button>');
      }
    });
    shortcuts.find('button').click(function(){
      $('#payment #payment_payment_method_id').val($(this).val());
      if ( !$('#payment #payment_value').val() )
        $('#payment #payment_value').val(LI.clear_currency($('#payment .sf_admin_list .change .sf_admin_list_td_list_value').html()));
      $('#payment form').submit();
      return false;
    });
    $('#payment form').append(shortcuts);
    
    // ajax'ing the form
    $('#payment form').submit(function(){
      $.post($(this).attr('action'),$(this).serialize(),function(data){
        $('#payment form').remove();
        ticket_payment_form(data);
        ticket_payment_old(true);
      });
      return false;
    });
  }

  function ticket_payment_related()
  {
    <?php if ( $sf_user->hasCredential('tck-cancel') && $transaction->Translinked->count() > 0 ): ?>
    $.get('<?php
      $ids = array();
      foreach ( $transaction->Translinked as $t )
        $ids[] = $t->id;
      
      echo url_for('payment/index').'?transaction_id[]='.implode('&transaction_id[]=',$ids);
    ?>',function(data){
      data = $.parseHTML(data);
      var currency = LI.get_currency($('#topay #to_pay').html());
      var fr_style = LI.currency_style($('#topay #to_pay').html()) == 'fr';
      
      total = LI.clear_currency($('#payment .total .sf_admin_list_td_list_value').html());
      related = 0;
      $(data).find('.sf_admin_list >> tbody .sf_admin_list_td_list_value').each(function(){
        related += LI.clear_currency($(this).html());
      });
      
      if ( !related )
        return ;
      
      $(data).find('.sf_admin_list >> tbody')
        .append('<tr class="sf_admin_row total ui-widget-content"><td></td><td colspan="2" class="sf_admin_text"><?php echo __('Really paid total') ?></td><td class="sf_admin_text sf_admin_list_td_list_value">'+LI.format_currency(total+related, true, fr_style, currency)'</td><td></td></tr>')
        .prepend('<tr class="sf_admin_row label ui-widget-content"><td></td><td colspan="3" class="sf_admin_text"><?php echo __('Paybacks') ?></td><td></td></tr>');
      lines = $(data).find('.sf_admin_list >> tbody > tr').addClass('related');
      lines.find('td:first-child').hide();
      lines.find('td:last-child').html('');
      $('#payment .sf_admin_list >> tbody tr:last').addClass('end-line');
      $('#payment .sf_admin_list >> tbody').append(lines);
    });
    <?php endif ?>
  }
  function ticket_payment_old(add)
  {
    <?php if ( !sfConfig::get('app_tickets_auto_print',false) ): ?>
    add = false;
    <?php else: ?>
    if ( add == 'undefined' ) add = false;
    <?php endif ?>
    $.get('<?php echo url_for('payment/index?transaction_id='.$transaction->id) ?>',function(data){
      ticket_payment_refresh(data,add);
    });
  }
  function ticket_payment_refresh(data,add)
  {
    data = $.parseHTML(data);
    
    $('#payment .sf_admin_list').remove();
    $(data).find('.sf_admin_list')
      .appendTo('#payment')
      .find('thead, tfoot, caption, .sf_admin_action_show, .sf_admin_action_edit').remove(); 
    ticket_payment_related();
    if ( $('#payment td:first-child input[type=checkbox]').length > 0 )
      $('#payment td:first-child').hide();
    $('#payment .sf_admin_action_delete a').each(function(){
      $(this).removeAttr('onclick');
      $(this).click(function(){
        if ( confirm('<?php echo __('Are you sure?',null,'sf_admin') ?>') )
        $.get('<?php echo url_for('payment/quickDelete?transaction_id='.$transaction->id) ?>&id='+$(this).parent().parent().parent().parent().find('input[name="ids[]"]').val(),function(data){
          ticket_payment_refresh(data,add);
        });
        return false;
      });
    });
    
    var pay_total = 0;
    var currency = LI.get_currency($('#topay #to_pay').html());
    var fr_style = LI.currency_style($('#topay #to_pay').html()) == 'fr';
    $('#payment tbody .sf_admin_list_td_list_value').each(function(){
      pay_total += LI.clear_currency($(this).html());
    });
    $('#payment tbody')
      .append('<tr class="sf_admin_row ui-widget-content odd total"><td colspan="2" class="sf_admin_text"><?php echo __('Total') ?></td><td class="sf_admin_text sf_admin_list_td_list_value">'+LI.format_currency(pay_total, true, fr_style, currency)+'</td><td></td></tr>')
      .append('<tr class="sf_admin_row ui-widget-content odd topay"><td colspan="2" class="sf_admin_text"><?php echo __('To pay') ?></td><td class="sf_admin_text sf_admin_list_td_list_value"></td><td></td></tr>')
      .append('<tr class="sf_admin_row ui-widget-content odd change"><td colspan="2" class="sf_admin_text"><?php echo __('Still missing') ?></td><td class="sf_admin_text sf_admin_list_td_list_value"></td><td></td></tr>');
    ticket_process_amount(add);
  }
  
  $(document).ready(function(){
    // new
    $.get('<?php echo url_for('payment/new') ?>',ticket_payment_form);
    
    // olds
    ticket_payment_old();
  });
</script>
