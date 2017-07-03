<table id="member_card_types">
  <tbody>
  <?php foreach ( $member_card_types as $type ): ?>
    <tr data-member-card-type-id="<?php echo $type->id ?>">
      <td class="raw_name"><?php echo $type ?></td>
      <td class="name"><?php echo $type->description ? $type->description : $type ?></td>
      <td class="details"><?php echo $type->getRawValue()->public_details ?></td>
      <td class="value"><?php echo format_currency($type->real_value,$sf_context->getConfiguration()->getCurrency()) ?></td>
      <td class="qty">
        x <input type="number" name="member_card_type[<?php echo $type->id ?>]" value="<?php echo isset($mct[$type->id]) ? $mct[$type->id] : 0 ?>" min="0" max="<?php echo sfConfig::get('app_member_cards_max_per_transaction', 3) ?>" />
      </td>
      <td class="operand">=</td>
      <td class="total"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></td>
    </tr>
  <?php endforeach ?>
  </tbody>
  <tfoot>
    <tr>
      <td class="raw_name"></td>
      <td class="name"></td>
      <td class="details"></td>
      <td class="value"></td>
      <td class="qty">0</td>
      <td class="operand">=</td>
      <td class="total"><?php echo format_currency(0,$sf_context->getConfiguration()->getCurrency()) ?></td>
    </tr>
  </tfoot>
</table>
