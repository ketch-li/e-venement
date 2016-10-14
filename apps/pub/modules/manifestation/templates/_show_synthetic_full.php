<?php if ( !isset($available) ) $available = 0 ?>
<?php
$online_limit = 0;
if ( isset($manifestation) )
  $online_limit = $manifestation->online_limit;
?>
<?php foreach ( $gauges as $gauge ): ?>
  <?php if ( $gauge->online
        && ( $free = $gauge->value
    - $gauge->printed
    - $gauge->ordered
    - (sfConfig::get('app_tickets_count_demands',false) ? $gauge->asked : 0)
    - (isset($vel['no_online_limit_from_manifestations']) && $vel['no_online_limit_from_manifestations']  ? 0 : $online_limit)
  ) > 0 ): ?>
    <?php $available++ ?>
  <?php endif ?>
<?php endforeach ?>

<?php if ( $available == 0 ): ?>
  <?php include_partial('show_full') ?>
<?php endif ?>
