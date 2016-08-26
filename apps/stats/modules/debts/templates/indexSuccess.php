<?php use_helper('CrossAppLink') ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h1><?php echo __('Debts evolution',array(),'menu') ?></h1>
  </div>
  <?php include_partial('global/chart_help'); ?>
  <?php if ( $sf_user->hasCredential('stats-activity') ): ?>
	  <?php include_partial('global/chart_jqplot', array(
	          'id'    => 'debts',
	          'data'  => cross_app_url_for('stats', 'debts/json'),
	          'name' => __('Debts'),
	          'width' => '900'
	         )) ?>
  <?php endif ?>
  </div>
</div>
<?php use_javascript('/js/jqplot/plugins/jqplot.dateAxisRenderer.js') ?>
  <?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
  <?php use_javascript('stats-debts?'.date('Ymd')) ?>