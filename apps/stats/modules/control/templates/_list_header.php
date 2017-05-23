<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <div id="sf_admin_filters_buttons" class="fg-buttonset fg-buttonset-multi ui-state-default">
      <a href="#" id="sf_admin_filter_button_top" onclick="javascript:$('#sf_admin_filter_button').click();" class="fg-button ui-state-default fg-button-icon-left ui-corner-all sf_button-toggleable"><span class="ui-icon ui-icon-search"></span><?php echo __('Filters',null,'sf_admin') ?></a>
    </div>
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
