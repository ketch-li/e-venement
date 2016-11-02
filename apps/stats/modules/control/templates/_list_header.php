<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Ticket controls',null,'menu') ?></h1>
  </div>
  <?php include_partial('global/chart_help'); ?>
      <?php include_partial('global/chart_jqplot', array(
        'id'    => 'hours',
        'data'  => cross_app_url_for('stats', 'control/json'),
        'label' => __('By hour'),
        'name'  => __('Ticket controls', null, 'menu'),
        'class' => '',
        'width' => '100%',
       )) ?>
    <div class="clear"></div>
</div>
<?php use_javascript('/js/jqplot/plugins/jqplot.barRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
<?php use_javascript('stats-control?'.date('Ymd')) ?>
