<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js'); ?>
<?php use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css'); ?>
<?php include_partial('global/ariane',array('active' => 0)) ?>
<h1><?php echo !$sf_user->hasContact() ? __('New account') : __('My account') ?></h1>
<?php include_partial('edit_header') ?>
<?php if ( $form->getErrorSchema()->count() > 0 ): ?>
<ul class="errors">
  <?php foreach ( $form->getErrorSchema()->getErrors() as $name => $error ): ?>
  <?php if ( !isset($form[$name]) ): ?>
    <li class="error error-<?php echo $error->getCode() ?>">
      <?php echo __($error) ?>
    </li>
  <?php endif ?>
  <?php endforeach ?>
</ul>
<?php endif; $errors = $form->getErrorSchema()->getErrors() ?>
<?php echo $form->renderFormTag(url_for('contact/update'), array('id' => 'contact-form', 'autocomplete' => 'on')) ?>
  <?php echo $form->renderHiddenFields() ?>
  <?php foreach ( $form->getWidgetSchema()->getPositions() as $name ): ?>
  <?php if ( !($form[$name]->getWidget() instanceof sfWidgetFormInputHidden) ): ?>
  <<?php echo $name != 'special_groups_list' ? 'p' : 'div' ?> class="<?php echo $name ?> field <?php if ( isset($errors[$name]) ) echo 'error' ?>">
    <?php echo $form[$name]->renderLabel() ?>
    <span class="<?php echo $name ?>"><?php echo $form[$name] ?></span>
    <?php if ( $name == 'organism' ): ?>
      <?php echo link_to(__('delete'),'contact/del?pro_id='.$contact->Professionals[0]->id) ?>
      <span class="help" style="display:block;">
        <span class="ui-icon ui-icon-help floatleft"></span>
        <?php echo sfConfig::get('app_contact_organism', array())['help'] ?>
      </span>
    <?php endif ?>
    <span class="error"><?php if ( isset($errors[$name]) ) echo __($errors[$name]) ?></span>
  </<?php echo $name != 'special_groups_list' ? 'p' : 'div' ?>>
    <?php elseif ( $name == 'special_groups_list' ): ?>
  <?php endif ?>
  <?php endforeach ?>
  <p class="submit">
    <input type="submit" name="submit" value="<?php echo __('Validate', null, 'sf_admin') ?>" tabindex="1000" />
    <?php if ( in_array('liOnlineExternalAuthOpenIDConnectPlugin', $sf_context->getRawValue()->getConfiguration()->getPlugins()) ): ?>
    <button type="submit" name="openidconnect" value="openidconnect" tabindex="1001">
      <?php echo __('Validate and access my third party account') ?>
    </button>
    <?php endif ?>
  </p>
  <div class="text"><?php echo pubConfiguration::getText('app_texts_contact_bottom','') ?></div>
</form>
<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#contact-form .field').click(function(){
      $(this).find('input, textarea, select').first().focus();
    });
  });
--></script>
