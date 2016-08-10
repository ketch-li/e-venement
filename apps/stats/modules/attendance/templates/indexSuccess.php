<?php include_partial('filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('filters_buttons') ?>
    <h1><?php echo __('Gauge filling') ?></h1>
  </div>
  <?php include_partial('global/chart_jqplot', array(
		  	    'id'    => 'attendance',
		  	    'data'  => cross_app_url_for('stats', 'attendance/json'),
		  	    'width' => '900',
		  	    'name' => __('Attendance')
		  	  )) 
		?>
</div>

<?php use_javascript('/js/jqplot/plugins/jqplot.barRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.categoryAxisRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.canvasAxisTickRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.canvasTextRenderer.js') ?>
<?php use_javascript('stats-attendance?'.date('Ymd')) ?>