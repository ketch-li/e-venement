<?php use_helper('CrossAppLink') ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h1><?php echo __('Evolution of groups',array(),'menu') ?></h1>
  </div>
  <?php if ( $sf_user->hasCredential('stats-pr-groups') ): ?>
    <?php include_partial('global/chart_jqplot', array(
            'id'    => 'groups',
            'data'  => cross_app_url_for('stats', 'groups/json'),
            'label' => '',
            'name'  => __('Evolution of groups', null, 'menu'),
            'width' => '900'
           )) ?>
  <?php endif ?>
</div>
<?php use_javascript('/js/jqplot/plugins/jqplot.dateAxisRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
<?php use_javascript('stats-groups?'.date('Ymd')) ?>