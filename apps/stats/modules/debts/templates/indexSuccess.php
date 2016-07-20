
<?php include_partial('global/chart_jqplot', array(
        'id'    => 'debts',
        'data'  => cross_app_url_for('stats', 'debts/json'),
        'label' => __('Debts evolution',null,'menu'),
        'width' => '900'
       )) ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.dateAxisRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
<?php use_javascript('stats-debts?'.date('Ymd')) ?>
