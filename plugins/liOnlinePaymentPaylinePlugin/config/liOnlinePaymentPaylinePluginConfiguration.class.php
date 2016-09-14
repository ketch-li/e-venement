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
*    Copyright (c) 2006-2016 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class liOnlinePaymentPaylinePluginConfiguration extends sfPluginConfiguration
{
  const paylineVersion = 4;
  public function setup()
  {
    require_once __DIR__.'/../lib/PaylinePayment.class.php';
    $this->dispatcher->connect('pub.cart.done', array($this, 'cartDone'));
  }
  
  public function cartDone(sfEvent $event)
  {
    $transaction = $event->getSubject()->getUser()->getTransaction();
    $event['action']->executeResponse($event['request']);
    $event->getSubject()->getUser()->setTransaction($transaction); // this is to avoid side-effects when using not-current-transaction
  }
}
