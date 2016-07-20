<?php include_partial('attendance/filters',array('form' => $form)) ?>
<?php use_helper('Date') ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  	<a name="chart-title"></a>
  	<div class="ui-widget-header ui-corner-all fg-toolbar">
    	<?php include_partial('attendance/filters_buttons') ?>
    	<h1><?php echo __('Tickets by price',null,'menu') ?></h1>
  	</div>
	<?php include_partial('show_criterias') ?>
	<?php include_partial('global/chart_jqplot', array(
	  	    'id'    => 'printed',
	  	    'data'  => cross_app_url_for('stats', 'prices/json'),
	  	    'label' => __('Printed tickets'),
	  	    'width' => '100%',
	  	    'class' => 'charts-4'
	  	  )) 
	?>
	<?php include_partial('global/chart_jqplot', array(
	  	    'id'    => 'ordered',
	  	    'data'  => cross_app_url_for('stats', 'prices/json'),
	  	    'label' => __('Ordered tickets'),
	  	    'width' => '100%',
	  	    'class' => 'charts-4'
	  	  )) 
	?>
	<div class="clear"></div>
	<?php if ( sfConfig::get('project_count_demands',false) ): ?>
	<?php include_partial('global/chart_jqplot', array(
	  	    'id'    => 'asked',
	  	    'data'  => cross_app_url_for('stats', 'prices/json'),
	  	    'label' => __('Asked tickets'),
	  	    'width' => '100%',
	  	    'class' => 'charts-4'
	  	  )) 
	?>
	<?php endif ?>
	<?php include_partial('global/chart_jqplot', array(
	  	    'id'    => 'all',
	  	    'data'  => cross_app_url_for('stats', 'prices/json'),
	  	    'label' => __('Global repartition'),
	  	    'width' => '100%',
	  	    'class' => 'charts-4'
	  	  )) 
	?>
	<div class="clear"></div>
</div>

<?php use_javascript('/js/jqplot/plugins/jqplot.pieRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
<?php use_javascript('stats-prices?'.date('Ymd')) ?>