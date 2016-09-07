<?php use_helper('Date') ?>
<?php use_helper('CrossAppLink') ?>
<?php include_partial('filters',array('form' => $form)) ?>
<div class="ui-widget ui-corner-all ui-widget-content cards">
  <div class="ui-widget-header ui-corner-all fg-toolbar">
    <h1><?php echo __('Memberships-like from %%from%% to %%to%%',array('%%from%%' => format_date($dates['from']), '%%to%%' => format_date($dates['to']))) ?></h1>
    <?php include_partial('attendance/filters_buttons') ?>
  </div>
  <?php include_partial('global/chart_help'); ?>
  <?php if ( $sf_user->hasCredential('stats-pr-cards') ): ?>
   <?php include_partial('global/chart_jqplot', array(
            'id'    => 'cards',
            'data'  => cross_app_url_for('stats', 'cards/json'),
            'width' => '900',
            'name'  => __('Member cards')
          )) 
    ?>
  <div class="ui-widget-content ui-corner-all accounting">
  <form action="" method="get">
    <p><span><?php echo __('VAT:') ?></span><span><input type="text" name="accounting[vat]" value="<?php echo isset($accounting['vat']) ? $accounting['vat'] : 0 ?>" />%</span></p>
    <?php foreach ( $cards as $card ): ?>
    <p>
      <span><?php echo __('Prices for %%price%%',array('%%price%%' => __($card['name']))) ?>:</span>
      <span><input type="text" name="accounting[price][<?php echo $card['name'] ?>]" value="<?php echo isset($accounting['price'][$card['name']]) ? $accounting['price'][$card['name']] : 0 ?>" /><?php echo $sf_context->getConfiguration()->getCurrency() ?></span>
    </p>
    <?php endforeach ?>
    <p><span></span><span><input type="submit" name="submit" value="ok" /></span></p>
  </form>
  </div>
  <p class="ui-widget-content ui-corner-all warning">
    <?php echo __('This chart is calculated on the full selected period. If a member card expires or has been created within it, the total quantity will be impacted with a fraction of this member card and not a full one.') ?>
  </p>
<?php endif ?>
</div>

<?php use_javascript('/js/jqplot/plugins/jqplot.pieRenderer.js') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.cursor.js') ?>
<?php use_javascript('stats-cards?'.date('Ymd')) ?>

