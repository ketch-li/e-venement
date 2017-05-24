<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <div id="sf_admin_filters_buttons" class="fg-buttonset fg-buttonset-multi ui-state-default">
      <a href="#" id="sf_admin_filter_button_top" onclick="javascript:$('#sf_admin_filter_button').click();" class="fg-button ui-state-default fg-button-icon-left ui-corner-all sf_button-toggleable"><span class="ui-icon ui-icon-search"></span><?php echo __('Filters',null,'sf_admin') ?></a>
    </div>
    <h1><?php echo __('Online sales',null,'menu') ?></h1>
  </div>
  <?php include_partial('global/chart_help'); ?>
  <?php if ( $sf_user->hasCredential('stats-pub') ): ?>
      <?php include_partial('global/chart_jqplot', array(
        'id'    => 'referers',
        'data'  => cross_app_url_for('stats', 'web_origin/json?which=referers'),
        'label' => __('Referers'),
        'class' => 'charts-4',
        'name'  => __('Online sales', null, 'menu'),
        'width' => '100%'
       )) ?>
       <?php include_partial('global/chart_jqplot', array(
        'id'    => 'campaigns',
        'data'  => cross_app_url_for('stats', 'web_origin/json?which=campaigns'),
        'label' => __('Campaigns'),
        'class' => 'charts-4',
        'name'  => __('Online sales', null, 'menu'),
        'width' => '100%'
       )) ?>
       <?php include_partial('global/chart_jqplot', array(
        'id'    => 'deal_done',
        'data'  => cross_app_url_for('stats', 'web_origin/json?which=deal_done'),
        'label' => __('Done deals'),
        'class' => 'charts-4',
        'name'  => __('Online sales', null, 'menu'),
        'width' => '100%'
       )) ?>
       <?php include_partial('global/chart_jqplot', array(
        'id'    => 'evolution',
        'data'  => cross_app_url_for('stats', 'web_origin/json?which=evolution'),
        'label' => __('Activity'),
        'class' => 'charts-4',
        'name'  => __('Online sales', null, 'menu'),
        'width' => '100%'
       )) ?>
    <?php endif ?>
    <div class="clear"></div>
</div>
<?php use_javascript('/js/jqplot/plugins/jqplot.pieRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.dateAxisRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
<?php use_javascript('stats-web-origin?'.date('Ymd')) ?>