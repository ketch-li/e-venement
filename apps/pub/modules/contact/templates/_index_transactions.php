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
<div id="transactions">
<h2><?php echo __('Your commands') ?> :</h2>
<p class="help">(<?php echo __('You can review your previous orders by clicking on its number') ?>)</p>

<div id="sf_admin_container">
<div id="sf_admin_content">
<div class="sf_admin_list">
  <table cellspacing="0">
    <thead>
      <tr>
        <th class="sf_admin_text sf_admin_list_th_list_name"><?php echo __('Id') ?></th>
        <th class="sf_admin_date sf_admin_list_th_list_date"><?php echo __('Date') ?></th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th colspan="2"></th>
      </tr>
    </tfoot>
    <tbody>
      <?php $cpt = 0 ?>
      <?php foreach ( $transactions as $t ): ?>
      <?php
        // is related to a Hold
        if ( $t->getRaw('HoldTransaction') instanceof HoldTransaction && $t->HoldTransaction->id )
          continue;
        
        // has at least one normal ticket
        $cpt = array('price' => 0, 'sold' => 0);
        foreach ( $t->Tickets as $ticket )
        {
          if ( !is_null($ticket->price_id) )
            $cpt['price']++;
          if ( $ticket->isSold() )
            $cpt['sold']++;
        }
        if ( $cpt == 0 )
          continue;
      ?>
      <?php if ( $t->Order->count() > 0 && $t->Tickets->count() > 0 || $cpt['sold'] > 0 || $t->getPrice() > 0  || ($t->closed == false && $current_transaction != $t->id) ): ?>
        <tr class="sf_admin_row <?php echo $cpt%2 == 0 ? '' : 'odd' ?> transaction-<?php echo $t->id ?>">
          <td class="sf_admin_text sf_admin_list_td_list_id">#<a href="<?php echo url_for('transaction/show?id='.$t->id) ?>" class="transaction"><?php echo $t->id ?></a></td>
          <td class="sf_admin_date sf_admin_list_td_list_date"><?php echo format_date($t->created_at) ?></td>
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



