<?php use_stylesheet('default-dashboard?'.date('Ymd')) ?>
<?php use_javascript('default-dashboard?'.date('Ymd')) ?>
<?php use_javascript('helper') ?>
<?php use_helper('CrossAppLink') ?>
<div id="dashboard">
    <?php if ( $sf_user->hasCredential('stats-pub') ): ?>
      <?php include_partial('global/chart_jqplot', array(
        'id'    => 'web-origin',
        'data'  => cross_app_url_for('stats', 'web_origin/json?which=evolution'),
        'label' => __('Online sales', null, 'menu'),
       )) ?>
    <?php endif ?>
    <?php if ( $sf_user->hasCredential('stats-geo') ): ?>
      <?php include_partial('global/chart_jqplot', array(
        'id'    => 'geo',
        'data'  => cross_app_url_for('stats', 'geo/json'),
        'label' => __('Geographical approach', null, 'menu'),
       )) ?>
    <?php endif ?>
    <?php if ( $sf_user->hasCredential('stats-prices') ): ?>
      <?php include_partial('global/chart_jqplot', array(
        'id'    => 'prices',
        'data'  => cross_app_url_for('stats', 'prices/json'),
        'label' => __('Prices', null, 'menu'),
       )) ?>
    <?php endif ?>
    <?php if ( $sf_user->hasCredential('stats-activity') ): ?>
      <?php include_partial('global/chart_jqplot', array(
        'id'    => 'debts',
        'data'  => cross_app_url_for('stats', 'debts/json'),
        'label' => __('Debts'),
       )) ?>
    <?php endif ?>
  </div>

<?php use_javascript('/js/jqplot/plugins/jqplot.pieRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.dateAxisRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
