<?php $museum = $sf_context->getConfiguration()->getApplication() == 'museum' ?>
<thead>
  <tr>
    <td class="name"><?php echo __('Price') ?></td>
    <td class="qty"><?php echo __('Qty') ?></td>
    <td class="qty"><?php echo __('Value') ?></td>
    <td class="transaction"><?php echo __('Transactions') ?></td>
    <td class="contact"><?php echo $museum ? __('Visitors') : __('Spectators') ?></td>
    <td class="nb_contacts"></td>
  </tr>
</thead>
