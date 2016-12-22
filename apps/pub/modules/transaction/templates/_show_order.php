<?php if ( in_array('liOnlineExternalAuthOpenIDConnectPlugin', $sf_data->getRaw('sf_context')->getConfiguration()->getPlugins())
        && pubConfiguration::getText('app_texts_terms_conditions') ): ?>
<p id="terms_and_conditions">
  <input type="checkbox" name="cgv" value="ok" id="terms_conditions" />
  <label for="terms_conditions"><?php echo pubConfiguration::getText('app_texts_terms_conditions') ?></label>
</p>
<?php endif ?>
<div id="actions">
<?php if ( $transaction->id == $sf_user->getTransactionId() ): ?>
<?php if ( ($txt = pubConfiguration::getText('app_member_cards_complete_your_passes', false)) && $sf_user->getTransaction()->MemberCards->count() ): ?>
<?php if ( $txt === true ) $txt = __('Complete your passes'); ?>
<div class="actions mc_pending">
<?php echo link_to($txt,'manifestation/index?mc_pending=') ?>
</div>
<?php endif ?>
<div class="actions index">
<?php echo link_to(__('Continue shopping'),'@homepage') ?>
</div>
<div class="actions register">
<?php if ( in_array('liOnlineExternalAuthOpenIDConnectPlugin', $sf_data->getRaw('sf_context')->getConfiguration()->getPlugins())
        && pubConfiguration::getText('app_texts_terms_conditions') ): ?>
  <?php echo link_to(__('Checkout'),'cart/register', array('title' => __('You must accept the terms & conditions before validating your order.'))) ?>
<?php else: ?>
  <?php echo link_to(__('Checkout'),'cart/register') ?>
<?php endif ?>
</div>
<div class="actions empty">
<?php echo link_to(__('Empty your cart'),'cart/empty') ?>
</div>
<?php else: ?>
<div class="actions register">
<?php echo link_to(__('Payment'),'cart/register?transaction_id='.$transaction->id) ?>
</div>
<?php endif ?>
</div>
