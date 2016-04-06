<?php use_helper('Number') ?>
<?php use_helper('Slug') ?>
<?php
  // calculating the quantity of products already in the cart
  $values = $prices = $products = array();
  foreach ( $sf_user->getTransaction()->BoughtProducts as $bp )
  {
    if ( $bp->Declination->product_id == $declination->product_id )
      $products[] = $bp;
    if ( $bp->product_declination_id == $declination->id )
    {
      $values[$bp->price_id] = $bp->value;
      if ( !isset($prices[$bp->price_id]) )
        $prices[$bp->price_id] = 0;
      $prices[$bp->price_id]++;
    }
  }
?>
<table class="prices">
<?php if ( $declination->Product->PriceProducts->count() > 0 ): ?>
<tbody>
<?php foreach ( $declination->Product->PriceProducts as $pp ): ?>
<?php if ( $pp->Price->PricePOS->count() > 0 ): ?>
  <tr data-price-id="<?php echo $pp->price_id ?>">
    <td class="price">
      <?php echo $pp->Price->description ? $pp->Price->description : $pp->Price ?>
    </td>
    <td class="value">
      <?php if ( !is_null($pp->value) ): ?>
        <?php echo format_currency($pp->value,$sf_context->getConfiguration()->getCurrency()) ?>
      <?php else: ?>
        <input
          type="text"
          pattern="\d+"
          size="2"
          name="store[free-price]"
          value="<?php echo isset($values[$pp->price_id]) ? floatval($values[$pp->price_id]) : sfConfig::get('project_tickets_free_price_default',1) ?>"
          <?php if ( isset($values[$pp->price_id]) ): ?>readonly="readonly"<?php endif ?>
        />&nbsp;<?php echo $sf_context->getConfiguration()->getCurrency() ?>
      <?php endif ?>
    </td>
    <td class="quantity">
      <form method="post" action="<?php echo url_for('store/mod') ?>" target="_blank" class="price_qty">
        <input type="hidden" name="store[declination_id]" value="<?php echo $declination->id ?>" />
        <input type="hidden" name="store[price_id]" value="<?php echo $pp->Price->id ?>" />
        <input type="hidden" name="store[free-price]" value="<?php echo isset($values[$pp->price_id]) ? $values[$pp->price_id] : sfConfig::get('project_tickets_free_price_default',1) ?>" />
        <select name="store[qty]">
          <?php
            // calculating how many products we can buy at once
            $general = sfConfig::get('app_store_max_per_product', 9) - count($products);
            $max = !$declination->use_stock || $max > $general ? $general : $max;
          ?>
          <?php foreach ( !is_null($pp->value) ? range(0, $max) : range(0,1) as $val ): ?>
            <option <?php echo isset($prices[$pp->price_id]) && $prices[$pp->price_id] == $val ? 'selected="selected"' : '' ?> value="<?php echo $val ?>">
              <?php echo $val ?>
            </option>
          <?php endforeach ?>
        </select>
      </form>
    </td>
    <td class="total"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></td>
  </tr>
<?php endif ?>
<?php endforeach ?>
</tbody>
<?php endif ?>
<tfoot>
  <tr>
    <td class="price"></td>
    <td class="value"></td>
    <td class="quantity"></td>
    <td class="total"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></td>
  </tr>
</tfoot>
<thead>
  <tr>
    <td class="price"><?php echo __('Price') ?></td>
    <td class="value"><?php echo __('Value') ?></td>
    <td class="quantity"><?php echo __('Quantity') ?></td>
    <td class="total"><?php echo __('Total') ?></td>
  </tr>
</thead>
</table>
