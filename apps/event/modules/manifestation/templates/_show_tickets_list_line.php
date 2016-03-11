    <td class="name"><?php echo $price ?></td>
    <td class="qty"><?php echo $qty ?></td>
    <td class="price"><?php echo format_currency($value,$sf_context->getConfiguration()->getCurrency()) ?></td>
    <td class="transaction"><?php echo implode('<br/>',$transaction->getRawValue()) ?></td>
    <td class="contact"><?php echo implode('<br/>',$contact->getRawValue()) ?></td>
    <td class="nb_contacts"><?php if ( count($contact) > 0 ): ?><span class="nb"><?php $cpt = 0; foreach ( $contact as $c ) if ( $c != '&nbsp;' ) $cpt++; echo $cpt ?></span>/<span class="total"><?php echo count($contact) ?></span><?php endif ?></td>
