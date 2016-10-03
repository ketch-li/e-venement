<?php $museum = sfContext::getInstance()->getConfiguration()->getApplication() == 'museum' ?>
<?php $type = sfConfig::get('project_museums_type', 'museum') ?>
<div class="sf_admin_form_row">
  <label><?php echo $museum ? ($type == 'museum' ? __('Meta visit') : __('Meta opening')) : __('Meta Event') ?>:</label>
  <?php echo link_to($event->MetaEvent,'meta_event/show?id='.$event->MetaEvent->id) ?>
</div>
