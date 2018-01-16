<?php if ( $sf_user->hasCredential('tck-member-cards') ): ?>

<?php if ( $form->mcform['member_card_type_id']->getWidget()->getOption('query')->count() > 0 ): ?>
<h3><?php echo __('Associate member cards') ?></h3>
<div class="sf_admin_form_subform member_card">
  
    <?php foreach ( array('member_card_type_id', 'price_id', 'quantity', 'event_id') as $field ): ?>
    <div class="sf_admin_form_row sf_admin_text member_card_<?php echo $field ?>">
        <div class="widget "><?php echo $form->mcform[$field] ?></div>
    </div>
    <?php endforeach ?>
    
    <div class="sf_admin_form_row sf_admin_button member_card_submit">
        <div class="widget">
            <a
                class="fg-button-mini fg-button ui-state-default fg-button-icon-left"
                href="<?php echo url_for('manifestation/associateMemberCards?id='.$form->getObject()->id) ?>"
                onclick="javascript: 
                  var form = $('<form>').append(
                    $(this).closest('.sf_admin_form_subform').find('.widget').clone()
                    ); 
                    form.find('#member_card_price_model_price_id').val($(this).closest('.sf_admin_form_subform').find('#member_card_price_model_price_id').val());
                  $.get(
                    $(this).prop('href'), 
                    form.serialize(), 
                    function(json) { 
                      if (!json.success) {
                        LI.alert(json.errors[0], 'error');
                      } else {
                        LI.alert(json.mcs);
                      }
                      $('#transition .close').click(); 
                    }
                  ); 
                  return false;"
            >
                <span class="ui-icon ui-icon-refresh"></span>
                <?php echo __('Associate') ?>
            </a>
        </div>
    </div>
</div>
<?php endif ?>

<?php endif ?>
