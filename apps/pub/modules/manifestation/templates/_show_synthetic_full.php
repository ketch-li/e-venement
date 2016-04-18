<?php if ( !isset($available) ) $available = 0 ?>
<?php foreach ( $gauges as $gauge ): ?>
  <?php if (( $free = $gauge->value
    - $gauge->printed
    - $gauge->ordered
    - (sfConfig::get('app_tickets_count_demands',false) ? $gauge->asked : 0)
    - (isset($vel['no_online_limit_from_manifestations']) && $vel['no_online_limit_from_manifestations'] && isset($manifestation) ? 0 : $manifestation->online_limit)
  ) > 0 ): ?>
    <?php $available++ ?>
  <?php endif ?>
<?php endforeach ?>

<?php if ( $available == 0 ): ?>
  <?php include_partial('show_full') ?>
<?php endif ?>
