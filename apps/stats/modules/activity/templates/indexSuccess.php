
<?php include_partial('attendance/filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <?php include_partial('attendance/filters_buttons') ?>
    <h1><?php echo __('Ticketing activity',array(),'menu') ?></h1>
  </div>
  <?php include_partial('global/chart_help'); ?>
  <?php if ( $sf_user->hasCredential('stats-activity') ): ?>
  <?php include_partial('global/chart_jqplot', array(
		  	    'id'    => 'activity',
		  	    'name'  => __('Ticketing activity', null, 'menu'),
		  	    'data'  => cross_app_url_for('stats', 'activity/json'),
		  	    'width' => '900'
		  	  )) 
  ?>
  <?php include_partial('global/chart_jqplot', array(
		  	    'id'    => 'activity-hour',
		  	    'name'  => __('Ticketing activity by hour', null, 'menu'),
		  	    'data'  => cross_app_url_for('stats', 'activity/json?denomination=hour'),
		  	    'width' => '900'
		  	  )) 
	?>
  <?php endif ?>
</div>

<?php use_javascript('/js/jqplot/plugins/jqplot.barRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.canvasAxisTickRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.canvasTextRenderer.js') ?>
<?php use_javascript('stats-activity?'.date('Ymd')) ?>
