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
*    Copyright (c) 2006-2017 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2017 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<script>
  var contact_url = "<?php echo url_for("contact/ajax"); ?>";
  function loadPage(mc, page) {
    $("#mc"+mc).load(contact_url, {membercard_id:mc, page:page});
  }
</script>
<?php    
  $q = Doctrine::getTable('MemberCardPrice')->createQuery('mcp')->andWhere('mcp.member_card_id = ?', $mc->id);    
  $pager = new sfDoctrinePager('MemberCardPrice',5);
  $pager->setQuery($q);
  $pager->setPage($sf_request->getParameter('page', 1));
  $pager->init();    
  $prices = array();
  foreach ( $pager->getResults() as $mcp )
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
<table>
  <tbody> 
    <?php foreach ( $prices as $price ): ?>
    <tr>
      <td class="price" style="width:40%"><?php echo $price['mcp']->Price->description ?></td>
      <td class="event" style="width:50%"><?php echo $price['mcp']->event_id ? link_to($price['mcp']->Event, 'event/edit?id='.$price['mcp']->event_id) : '' ?></td>
      <td class="qty" style="width:10%"><?php echo $price['qty'] ?></td>
    </tr>
    <?php endforeach ?>
    
  </tbody>
  <?php if ( $pager->haveToPaginate() ): ?>
  <tfoot>
    <tr><td colspan="3">
    <div class="pagination">
      <a href="javascript:loadPage(<?php echo $mc->id; ?>, 1);">&lt;&lt;</a>   
      <a href="javascript:loadPage(<?php echo $mc->id; ?>, <?php echo $pager->getPreviousPage() ?>);">&lt;</a>   
      <?php foreach ($pager->getLinks() as $page): ?>
        <?php if ($page == $pager->getPage()): ?>
          <?php echo $page ?>
        <?php else: ?>
          <a href="javascript:loadPage(<?php echo $mc->id; ?>, <?php echo $page ?>);"><?php echo $page ?></a>
        <?php endif; ?>
      <?php endforeach; ?>   
      <a href="javascript:loadPage(<?php echo $mc->id; ?>, <?php echo $pager->getNextPage() ?>);">&gt;</a>   
      <a href="javascript:loadPage(<?php echo $mc->id; ?>, <?php echo $pager->getLastPage() ?>);">&gt;&gt;</a>
    </div>
    </td></tr>
  </tfoot>
  <?php endif; ?>
</table>