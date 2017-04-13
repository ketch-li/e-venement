<?php $totals = array('tip' => 0, 'vat' => array(), 'pet' => 0, 'taxes' => 0) ?>
<table id="lines">
<thead><tr>
  <th class="event"><span><?php echo __('Product', null, 'li_accounting') ?></span></th>
  <th class="category"><span><?php echo __('Category', null, 'li_accounting') ?></span></th>
  <th class="date"><span><?php echo __('Date', null, 'li_accounting') ?></span></th>
  <th class="time"><span><?php echo __('Time', null, 'li_accounting') ?></span></th>
  <th class="location"><span><?php echo __('Location', null, 'li_accounting') ?></span></th>
  <th class="postalcode"><span><?php echo __('Postal code', null, 'li_accounting') ?></span></th>
  <th class="city"><span><?php echo __('City', null, 'li_accounting') ?></span></th>
  <th class="price"><span><?php echo __('Price', null, 'li_accounting') ?></span></th>
  <th class="up"><span><?php echo __('UP Net', null, 'li_accounting') ?></span></th>
  <th class="qty"><span><?php echo __('Qty', null, 'li_accounting') ?></span></th>
  <th class="seats"><span><?php echo __('Seats', null, 'li_accounting') ?></span></th>
  <th class="extra-taxes"><span><?php echo __('Taxes', null, 'li_accounting') ?></span></th>
  <th class="pit"><span><?php echo __('TIP', null, 'li_accounting') ?></span></th>
  <th class="vat"><span><?php echo __('VAT', null, 'li_accounting') ?></span></th>
  <th class="tep"><span><?php echo __('PET', null, 'li_accounting') ?></span></th>
</tr></thead>
<tbody>
<?php for ( $i = 0 ; $i < $tickets->count() ; $i++ ): ?>
<?php $ticket = $tickets[$i] ?>
<?php if ( $ticket->id > 0 ): ?>
  <tr class="ticket">
    <td class="event"><?php echo $ticket->Manifestation->Event ?></td>
    <td class="category"><?php echo $ticket->Manifestation->Event->EventCategory->name ?></td>
    <td class="date"><?php echo format_date($ticket->Manifestation->happens_at) ?></td>
    <td class="time"><?php echo __('From %%from%% to %%to%%', array('%%from%%' => format_date($ticket->Manifestation->happens_at,'HH:mm'), '%%to%%' => format_date($ticket->Manifestation->ends_at,'HH:mm'))) ?></td>
    <td class="location"><?php echo $ticket->Manifestation->Location ?></td>
    <td class="postalcode"><?php echo $ticket->Manifestation->Location->postalcode ?></td>
    <td class="city "><?php echo $ticket->Manifestation->Location->city ?></td>
    <td class="price"><?php echo $ticket->Price->description ?></td>
    <td class="up"><?php echo format_currency($ticket->value,$sf_context->getConfiguration()->getCurrency()) ?></td>
    <?php
      $qty = 0;
      $nums = array();
      $total = array('tip' => 0, 'taxes' => 0, 'vat' => 0, 'pet' => 0,);
      while ( $i < $tickets->count()
           && $tickets[$i]->manifestation_id == $ticket->manifestation_id
           && $tickets[$i]->price_id         == $ticket->price_id
           && $tickets[$i]->value            == $ticket->value )
      {
        if ( isset($nocancel) && !$nocancel || $tickets[$i]->Cancelling->count() == 0 )
        {
          $qty++;
          $local_vat = $tickets[$i]->printed_at || $tickets[$i]->integrated_at || $tickets[$i]->cancelling
            ? $tickets[$i]->vat
            : $tickets[$i]->Manifestation->Vat->value;
          if ( $tickets[$i]->numerotation )
            $nums[] = $tickets[$i]->numerotation;
          $total['taxes'] += $tickets[$i]->taxes;
          $total['tip']   += $val = $tickets[$i]->value + $tickets[$i]->taxes;
          $total['pet']   += $pet = round($val/(1+$local_vat), 2);
          $total['vat']   += $vat = $val - $pet;
          if ( !isset($totals['vat'][$local_vat]) )
            $totals['vat'][$local_vat] = 0;
          $totals['vat'][$local_vat] += $vat;
        }
        $i++;
      }
      $i--; // rollback to process the last ticket that has been ignored
    ?>
    <td class="qty"><?php echo $qty; ?></td>
    <td class="seats"><span><?php echo count($nums) > 20 ? '' : implode('<span>, </span>', $nums) ?></span></td>
    <td class="extra-taxes"><?php echo $total['taxes'] ? format_currency($total['taxes'],$sf_context->getConfiguration()->getCurrency()) : '-'; $totals['taxes'] += $total['taxes']; ?></td>
    <td class="pit"><?php echo format_currency($total['tip'],$sf_context->getConfiguration()->getCurrency()); $totals['tip'] += $total['tip']; ?></td>
    <td class="vat">
      <span class="value"><?php echo $total['vat'] > 0 ? format_currency($total['vat'],$sf_context->getConfiguration()->getCurrency()) : '-' ?></span>
      <span class="percent"><?php echo $local_vat * 100 ?></span>
    </td>
    <td class="tep"><?php echo format_currency($total['pet'],$sf_context->getConfiguration()->getCurrency()); $totals['pet'] += $total['pet'] ?></td>
  </tr>
<?php endif ?>
<?php endfor ?>
<?php for ( $i = 0 ; $i < count($products) ; $i++ ): ?>
<?php $product = $products[$i] ?>
<?php if ( $product->id > 0 ): ?>
  <tr class="product">
    <td class="event"><?php echo (string)$product ?></td>
    <td class="time" colspan="2"><?php echo $product->code ?></td>
    <td class="location" colspan="2"><?php echo $product->declination ?></td>
    <td class="price"><?php echo $product->price_id ? $product->Price->description : $product->price_name ?></td>
    <td class="up"><?php echo format_currency($product->value,$sf_context->getConfiguration()->getCurrency()) ?></td>
    <?php
      $qty = 0;
      $total = array('tip' => 0, 'taxes' => 0, 'vat' => 0, 'pet' => 0,);
      while ( $i < $products->count()
           && $products[$i]->code == $product->code
           && $products[$i]->price_id         == $product->price_id
           && $products[$i]->value            == $product->value )
      {
        $qty++;
        $total['tip']   += $val = $products[$i]->value + $products[$i]->shipping_fees;
        $total['taxes'] += $products[$i]->shipping_fees;
        $total['pet']   += $pet = round($products[$i]->value/(1+$products[$i]->vat), 2) + round($products[$i]->shipping_fees/(1+$products[$i]->shipping_fees_vat), 2);
        $total['vat']   += $vat = $val - $pet;
        if ( !isset($totals['vat'][$products[$i]->vat]) )
          $totals['vat'][$products[$i]->vat] = 0;
        $totals['vat'][$products[$i]->vat] += $vat;
        $i++;
      }
      $i--; // rollback to process the last ticket that has been ignored
    ?>
    <td class="qty"><?php echo $qty; ?></td>
    <td class="seats"></td>
    <td class="extra-taxes"><?php echo $total['taxes'] ? format_currency($total['taxes'],$sf_context->getConfiguration()->getCurrency()) : '-'; $totals['taxes'] += $total['taxes']; ?></td>
    <td class="pit"><?php echo format_currency($total['tip'],$sf_context->getConfiguration()->getCurrency()); $totals['tip'] += $total['tip']; ?></td>
    <td class="vat">
      <span class="value"><?php echo format_currency($total['vat'],$sf_context->getConfiguration()->getCurrency()) ?></span>
      <span class="percent"><?php echo $product->vat * 100 ?></span>
    </td>
    <td class="tep"><?php echo format_currency($total['pet'],$sf_context->getConfiguration()->getCurrency()); $totals['pet'] += $total['pet'] ?></td>
  </tr>
<?php endif ?>
<?php endfor ?>
</tbody>
</table>
