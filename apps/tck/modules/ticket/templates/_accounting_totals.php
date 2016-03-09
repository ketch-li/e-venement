<div id="totals"><table>
  <tr class="tep">
    <td><span><?php echo __('Total excl. tax:', null, 'li_accounting') ?></span></td>
    <td class="float"><?php echo format_currency(round($totals['tip'] - $totals['vat']['total'],2),$sf_context->getConfiguration()->getCurrency()) ?></td>
  </tr>
  <?php
    foreach ( $totals['vat'] as $key => $value )
    if ( $key != 'total' && $value != 0 ):
  ?>
  <tr class="vat">
    <td><?php echo __('VAT %%p%%:',array('%%p%%' => ($key*100).'%'), 'li_accounting') ?></td>
    <td class="float"><?php echo format_currency(round($value,2),$sf_context->getConfiguration()->getCurrency()) ?></td>
  </tr>
  <?php endif ?>
  <tr class="pit">
    <td><span><?php echo __('Total incl. taxes:', null, 'li_accounting') ?></span></td>
    <td class="float"><?php echo format_currency(round($totals['tip'],2),$sf_context->getConfiguration()->getCurrency()) ?></td>
  </tr>
</table></div>
