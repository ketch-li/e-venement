<?php
  // member cards
  if ( sfConfig::get('project_cards_enable', false) )
  if ( $sf_user->hasCredential('pr-card-view') )
  echo link_to(__('Cards'),isset($object) && $sf_context->getModuleName() == 'contact' ? 'contact/card?id='.$object->id : 'member_card/check',array(
    'title' => isset($object) && $sf_context->getModuleName() == 'contact'
      ? __("%%c%%'s member cards",array('%%c%%' => $object))
      : __('Member card check',null,'menu'),
  ));
  
  $active_filters = false;
  if ( !isset($object) )
  foreach ( $hasFilters->getRawValue() as $key => $value )
  if ( $value )
  {
    $active_filters = true;
    break;
  }
  
  // groups
  if ( $sf_user->hasCredential('pr-group') )
  {
    echo link_to(__('Groups'), $active_filters ? $sf_context->getModuleName().'/group' : '@group',array(
      'title' => $active_filters ? __('Export to group') : __('Group List'),
    ));
  }
?>
<?php if ( is_object($object) ): // same condition bellow! ?>
<div class="tdp-submenu">
<?php
  echo link_to(__('Versions'), $sf_context->getModuleName().'/version?id='.$object->id.'&v='.($object->version == 1 ? 1 : $object->version-1));
  //echo link_to(__('Archives'), $sf_context->getModuleName().'/archives?id='.$object->id);
  if ( $sf_user->hasCredential('pr-contact-csv') )
    echo link_to(__('vCard'), $sf_context->getModuleName().'/vcf?id='.$object->id, array('target' => '_blank'));
  if ( $sf_user->hasCredential('pr-contact-new') )
    echo link_to(__('Duplicate'), $sf_context->getModuleName().'/duplicate?id='.$object->id);
?>
</div><a onclick="javascript: return false;" target="_blank" class="action actions group" href="<?php echo url_for('@'.$sf_context->getModuleName()) ?>">Actions</a>
<?php elseif ( $active_filters ): ?>
<?php
  // emailing
  if ( $sf_user->hasCredential('pr-emailing') )
    echo link_to(__('Emailing'), $sf_context->getModuleName().'/emailing',array('title' => __('Create emailing')));
?>
<?php endif ?>
