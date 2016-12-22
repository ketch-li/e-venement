<?php include_partial('global/ariane',array('active' => 3)) ?>
<h1><?php echo __('Your contact') ?></h1>
<?php include_partial('contact/edit_header') ?>
<?php //include_partial('global/form_errors',array('form' => $form)) ?>
<?php if ( !$sf_user->getTransaction()->contact_id && isset($login) ): ?>
<?php echo $login->renderFormTag(url_for('login/validate'), array('autocomplete' => 'on', 'id' => 'login', )) ?>
  <?php $client = sfConfig::get('project_about_client',array()) ?>
  <h2><?php echo __('I already have an account or already bought tickets to %%organization%%', array('%%organization%%' => sfConfig::get('app_informations_title',$client['name']))) ?></h2>
  <?php include_partial('login/login', array('form' => $login, 'buttons' => false,)) ?>
  <input type="submit" value="<?php echo __('Login') ?>" name="submit" class="submit" />
  <input type="hidden" name="register" value="true" />
</form>
<?php endif ?>
<?php echo $form->renderFormTag(url_for('cart/order'.(isset($specific_transaction) && $specific_transaction ? '?transaction_id='.$specific_transaction->id : '')), array('id' => 'contact-form', 'autocomplete' => 'on')) ?>
  <h2>
    <?php if ( !$sf_user->getTransaction()->contact_id ): ?>
      <?php echo __('I am new in this store') ?>
    <?php else: ?>
      <?php echo __('I check my details') ?>
    <?php endif ?>
  </h2>
  <?php include_partial('global/register',array('form' => $form)) ?>
  <p class="submit"><input type="submit" name="submit" value="<?php echo __('Order') ?>" /></p>
  <div class="text"><?php echo pubConfiguration::getText('app_texts_contact_bottom','') ?></div>
</form>

<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#contact-form .field').click(function(){
      $(this).find('input, textarea, select').first().focus();
    });
  });
--></script>
