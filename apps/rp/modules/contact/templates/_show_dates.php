<?php use_helper('Date') ?>
<span class="tdp-dates sf_admin_form_field_dates">
<span title="<?php echo __('Created at') ?>"><?php echo(format_datetime($contact->created_at, 'f')); ?></span>
<br/>
<span title="<?php echo __('Updated at') ?>"><?php echo(format_datetime($contact->updated_at, 'f').'<br>'.__('by').' <strong>'.$contact->last_accessor.'</strong>'); ?></span>
</span>
