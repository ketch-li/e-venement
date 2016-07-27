<?php use_javascript('helper') ?>
<?php $ofc = isset($ofc) ? $ofc : true ?>
<?php $dl = isset($dl) ? $dl : ($sf_user->hasCredential('stats-csv') ? $sf_context->getModuleName().'/csv' : false) ?>
<span class="arrow"></span>
<a
  href="#<?php echo isset($anchor) ? $anchor : '' ?>"
  id="img-export"
  title="<?php echo __('Chart') ?>"
  class="chart ui-corner-all img-export"  
>
  <span><?php echo __('Chart') ?></span>
</a>
<?php if ( $dl ): ?>
<a
  <?php if ( $dl === true ): ?>
  href="#csv"
  data-type="<?php echo $sf_user->getExtractionType() ?>"
  <?php else: ?>
  target="_blank"
  href="<?php echo $dl ?>"
  <?php endif ?>
  title="<?php echo __('Record') ?>"
  class="record ui-corner-all"
><span><?php echo __('Record') ?></span></a>
<?php endif ?>

<?php use_javascript('chart-actions?'.date('Ymd')) ?>