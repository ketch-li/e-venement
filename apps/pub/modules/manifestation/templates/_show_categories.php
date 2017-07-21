<?php use_helper('Number') ?>
<?php
  $groups = array();
  foreach ( $gauges as $gauge )
  if ( $gauge->online && $gauge->getFree() > $gauge->getHeldFreeSeats()->count() )
  {
    if ( !isset($groups[$gauge->group_name]) )
      $groups[$gauge->group_name] = array();
    
    foreach ( $gauge->getPriceManifestationsFiltered() as $pm )
    if ( $pm->Price->isAccessibleBy($sf_user->getRawValue(), $manifestation) )
    {
      $groups[$gauge->group_name][$pm->price_id] = array(
        'price'   => $pm->Price,
        'values'  => array('manif' => $pm->value),
      );
    }
    
    foreach ( $gauge->getPriceGaugesFiltered() as $pg )
    if ( $pg->Price->isAccessibleBy($sf_user->getRawValue(), $manifestation)
      && in_array($gauge->workspace_id, $pg->getRaw('Price')->Workspaces->getPrimaryKeys()) )
    {
      if ( !isset($groups[$gauge->group_name][$pg->price_id]) )
        $groups[$gauge->group_name][$pg->price_id] = array(
          'price'   => $pg->Price,
          'values'  => array(),
        );
      if ( isset($groups[$gauge->group_name][$pg->price_id]['values']['manif']) )
        unset($groups[$gauge->group_name][$pg->price_id]['values']['manif']);
      $groups[$gauge->group_name][$pg->price_id]['values'][$pg->id] = floatval($pg->value);
    }
  }
  
  // forcing the price order
  foreach ( $groups as $name => $group )
  {
    $arr = array();
    foreach ( $group as $id => $price )
      $arr[$id] = max($price['values']);
    arsort($arr);
    $new = array();
    foreach ( $arr as $id => $value )
      $new[$id] = $group[$id];
    $groups[$name] = $new;
  }
  
  // to be sure...
  ksort($groups);
  
  if ( $sf_user->hasContact() && sfConfig::get('app_options_pass_price_first') )
  foreach ($sf_user->getContact()->getActiveMembercards()->merge($sf_user->getTransaction()->MemberCards->getRawValue()) as $MemberCard)
  {
    $pm = $MemberCard->MemberCardType->MemberCardPriceModels->toKeyValueArray('id', 'price_id')->getRawValue();

    foreach (array_reverse($groups) as $name => $prices)
    {
      $gps = array();
      
      foreach ($prices as $id => $price)
      {
        if ( in_array($price['price']->id, $pm) )
        {
          $gps = array($id => $price) + $gps;
        }
        else
        {
          $gps[$id] = $price;
        }
      }
      
      $groups[$name] = $gps;
    }
  }

?>
<ul><?php foreach ( $groups as $name => $prices ): ?>
  <?php if ( count($prices) > 0 ): ?>
  <li>
    <form action="<?php echo url_for('ticket/addCategorizedTicket') ?>" method="get">
    <span class="category" title="<?php echo $name ?>">
      <?php echo $name ?>
      <input type="hidden" name="price_new[group_name]" value="<?php echo $name ?>" />
      <input type="hidden" name="price_new[manifestation_id]" value="<?php echo $manifestation->id ?>" />
    </span>
    <select class="prices" name="price_new[price_id]"><?php foreach ( $prices as $id => $price ): ?>
      <?php if ( $price['price']->isAccessibleBy($sf_user->getRawValue(), $manifestation) ): ?>
      <option value="<?php echo $id ?>">
        <?php echo $price['price']->description ? $price['price']->description : $price['price'] ?>
        <?php foreach ( $price['values'] as $key => $value ) $price['values'][$key] = format_currency($value,$sf_context->getConfiguration()->getCurrency()); ?>
        (<?php echo implode(', ', array_unique($price['values'])) ?>)
      </option>
      <?php endif ?>
    <?php endforeach ?></select>
    <span class="qty">
      <?php
        $vel = sfConfig::get('app_tickets_vel',array());
        $max = isset($vel['max_per_manifestation']) && $vel['max_per_manifestation']
          ? $vel['max_per_manifestation'] : 9;
      ?>
      <a href="#" data-val="-1" class="minus">-</a><input type="text" pattern="\d+" name="price_new[qty]" value="1" data-max-value="<?php echo $max ?>" /><a href="#" class="plus" data-val="1">+</a>
    </span>
    <button name="add" value=""><?php echo __('Add') ?></button>
    </form>
  </li>
  <?php endif ?>
<?php endforeach ?></ul>
