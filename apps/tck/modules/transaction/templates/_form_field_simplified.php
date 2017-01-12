<?php use_stylesheet('tck-touchscreen-simplified?'.date('Ymd')) ?>
<?php use_javascript('tck-touchscreen-simplified?'.date('Ymd')) ?>
<?php use_javascript('jquery.nicescroll.min.js') ?>
<?php use_helper('Number') ?>

<form action="#" method="get">
  <div class="header simplified-top-block">
    <ul class="products-types ui-widget-content ui-corner-all">
      <li data-bunch-id="manifestations"><?php echo __('Manifestations') ?></li><?php
      if ( $sf_user->hasCredential('tck-museum') ): ?><li data-bunch-id="museum"><?php echo __('Museum') ?></li><?php endif
      ?><?php if ( $sf_user->hasCredential('tck-pos') ): ?><li data-bunch-id="store"><?php echo __('Store', null, 'menu') ?></li><?php endif ?>
    </ul>
    <div class="contact ui-widget-content ui-corner-all"></div>
  </div>
  <div class="content simplified-top-block">
    <ul class="bunch manifestations ui-widget-content ui-corner-all" data-bunch-id="manifestations">
      <li class="search"><input type="text" name="search" value="" placeholder="<?php echo __('Search for a declination') ?>" data-url="<?php echo url_for('transaction/getStore?simplified=true&q=SEARCH_VAL') ?>" autocomplete="off" /></li>
      <li class="categories" data-text-categories="<?php echo $cats = __('Categories') ?>" data-text-best-sales="<?php echo __('Best sales') ?>" data-categories-url="<?php echo cross_app_url_for('pos', 'category/ajax?keep-order=true') ?>" data-products-url="<?php echo url_for('transaction/getStore?simplified=1&category_id=CATID') ?>">
        <span class="category"><?php echo $cats ?></span>
      </li>
    </ul>
    <?php $conf = sfConfig::get('app_transaction_manifestations', array('display_time_interval' => '+1 month')) ?>
    <input
      type="hidden"
      name="manifestations-display-interval"
      value="<?php echo date('Y-m-d H:i:s', strtotime(!isset($conf['display_time_limit']) ? '+1 month' : $conf['display_time_limit'])); ?>"
      data-conf="<?php echo $conf['display_time_limit'] ?>"
    />

    <ul class="prices ui-widget-content ui-corner-all">
    </ul>
    <ul class="payments ui-widget-content ui-corner-all">
      <li class="value"><input type="number" name="simplified[payment_value]" value="" placeholder="<?php echo __('Value') ?>" /></li>
      <li id="ept-transaction-simplified" style="display: none">
        <p><?php echo __('EPT transaction...') ?></p>
        <button class="cancel-ept-transaction"><?php echo __('Cancel') ?></button>
      </li>
      <li class="detail"><input type="text" name="simplified[payment_detail]" value="" placeholder="<?php echo __('Detail') ?>" /></li>
      <li class="gift_coupon"><input type="text" name="simplified[gift_coupon]" value="" placeholder="<?php echo __('Gift coupon') ?>" /></li>
    </ul>
    <ul class="cart ui-widget-content ui-corner-all">
      <li class="print end">
        <button name="s" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button fg-button-icon-left">
          <?php echo __('Print and deliver') ?>
          <span class="ui-icon ui-icon-print"></span>
        </button>
      </li>
      <li class="paid end">
        <div class="left">
          <span class="category"><?php echo __('Total') ?></span>
        </div>
        <div class="right">
          <span class="value" data-value="0"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></span>
        </div>
      </li>
      <li class="total end">
        <div class="left">
          <span class="category"><?php echo __('To pay') ?></span>
          <span class="qty" data-qty="0">0</span>
        </div>
        <div class="right">
          <span class="value" data-value="0"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></span>
        </div>
      </li>
      <li class="topay end">
        <div class="left">
          <span class="payment_missing category"><?php echo __('Still missing') ?></span>
          <span class="payment_change category" style="display:none;"><?php echo __('Give change') ?></span>
        </div>
        <div class="right">
          <span class="value"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></span>
        </div>
      </li>
    </ul>
  </div>
</form>
