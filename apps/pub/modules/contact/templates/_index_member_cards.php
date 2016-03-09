<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_helper('Number') ?>
<div id="member_cards">
<h2><?php echo sfConfig::get('app_member_cards_title',false) ? pubConfiguration::getText('app_member_cards_title') : __('Member card') ?></h2>

<div id="sf_admin_container">
<div id="sf_admin_content">
<div class="sf_admin_list">
  <table cellspacing="0">
    <thead>
      <tr>
        <th class="sf_admin_text sf_admin_list_th_list_name"><?php echo sfConfig::get('app_member_cards_title',false) ? pubConfiguration::getText('app_member_cards_title') : __('Member card') ?></th>
        <th class="sf_admin_text sf_admin_list_th_list_value"><?php echo __('Value') ?></th>
        <th class="sf_admin_text sf_admin_list_th_list_prices"><?php echo __('Associated prices still available') ?></th>
        <th class="sf_admin_date sf_admin_list_th_list_expire_at"><?php echo sfConfig::get('app_member_cards_show_expire_at', true) ? __('Expire at') : '' ?></th>
        <th class="sf_admin_date sf_admin_list_th_list_transaction_id"><?php echo __('Transaction number') ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th colspan="5"></th>
      </tr>
    </tfoot>
    <tbody>
      <?php $cpt = 0 ?>
      <?php foreach ( $member_cards as $mc ): ?>
      <?php if ( $mc->active ): ?>
      <tr class="sf_admin_row <?php echo $cpt%2 == 0 ? '' : 'odd' ?>">
        <td class="sf_admin_text sf_admin_list_td_list_name"><?php echo $mc->MemberCardType->name ?></td>
        <td class="sf_admin_text sf_admin_list_td_list_value"><?php echo format_currency($mc->value, $sf_context->getConfiguration()->getCurrency()) ?></td>
        <td class="sf_admin_text sf_admin_list_td_list_prices">
          <?php if ( $mc->MemberCardPrices->count() > 0 ): ?>
          <table>
            <thead>
              <tr>
                <th class="price"><?php echo __('Price') ?></td>
                <th class="event"><?php echo __('Event') ?></td>
                <th class="qty"><?php echo __('Quantity') ?></td>
              </tr>
            </thead>
            <tbody>
              <?php
                $prices = array();
                foreach ( $mc->MemberCardPrices as $mcp )
                {
                  $key = $mcp->price_id.'||'.$mcp->event_id;
                  if ( !isset($prices[$key]) )
                    $prices[$key] = array(
                      'qty' => 0,
                      'mcp' => $mcp,
                    );
                  $prices[$key]['qty']++;
                }
              ?>
              <?php foreach ( $prices as $price ): ?>
              <tr>
                <td class="price"><?php echo $price['mcp']->Price->description ?></td>
                <td class="event"><?php echo $price['mcp']->event_id ? link_to($price['mcp']->Event, 'event/edit?id='.$price['mcp']->event_id) : '' ?></td>
                <td class="qty"><?php echo $price['qty'] ?></td>
              </tr>
              <?php endforeach ?>
            </tbody>
          </table>
          <?php endif ?>
        </td>
        <td class="sf_admin_date sf_admin_list_td_list_expire_at"><?php echo sfConfig::get('app_member_cards_show_expire_at', true) ? format_date($mc->expire_at) : '' ?></td>
        <td class="sf_admin_date sf_admin_list_td_list_transaction_id">#<?php echo link_to($mc->transaction_id, 'transaction/show?id='.$mc->transaction_id) ?></td>
      </tr>
      <?php $cpt++ ?>
      <?php endif ?>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
</div>
</div>

</div>
