<?php use_helper('Number') ?>
<?php if ( $form->getObject()->BoughtProducts->count() > 0 ): ?>
<div class="sf_admin_form_row">
  <label><?php echo __('List of linked products') ?>:</label>
  <table class="bought_products_list ui-widget ui-corner-all ui-widget-content">
  <tbody>
    <?php foreach ( $form->getObject()->BoughtProducts as $bp ): ?>
    <tr>
      <td class="transation_id">#<?php echo cross_app_link_to($bp->transaction_id,'tck','transaction/edit?id='.$bp->transaction_id) ?></td>
      <td class="ticket_id"><?php echo '#'.$bp->id ?></td>
      <td class="price_name"><?php echo $bp->price_name ?></td>
      <td class="ticket_value"><?php echo format_currency($bp->value,$sf_context->getConfiguration()->getCurrency()); ?></td>
      <td class="ticket_manifestation"><?php if ( $bp->product_declination_id ): ?>
        <?php echo cross_app_link_to($bp->Declination->Product,'pos','product/show?id='.$bp->Declination->product_id) ?>
      <?php endif ?></td>
    </tr>
    <?php endforeach ?>
  </tbody>
  </table>
</div>
<?php endif ?>
