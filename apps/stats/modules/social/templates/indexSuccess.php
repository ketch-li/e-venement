<?php use_helper('CrossAppLink') ?>
<?php include_partial('attendance/filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <a name="chart-title"></a>
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Social statistics',null,'menu') ?></h1>
  </div>
  <?php include_partial('global/chart_jqplot', array(
            'id'    => 'fs',
            'data'  => cross_app_url_for('stats', 'social/json'),
            'width' => '100%',
            'class' => 'charts-4'
          )) 
    ?>
  <?php include_partial('global/chart_jqplot', array(
            'id'    => 'fq',
            'data'  => cross_app_url_for('stats', 'social/json'),
            'width' => '100%',
            'class' => 'charts-4'
          )) 
    ?>
  <?php include_partial('global/chart_jqplot', array(
            'id'    => 'tor',
            'data'  => cross_app_url_for('stats', 'social/json'),
            'width' => '100%',
            'class' => 'charts-4'
          )) 
    ?>
<?php include_partial('chart_fs') ?>
<?php include_partial('chart_fq') ?>
<?php include_partial('chart_tor') ?>
	<div class="clear"></div>
</div>

<?php use_javascript('/js/jqplot/plugins/jqplot.pieRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
<?php use_javascript('stats-social?'.date('Ymd')) ?>

