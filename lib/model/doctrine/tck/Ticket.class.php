<?php

/**
 * Ticket
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Ticket extends PluginTicket
{
  public function preSave($event)
  {
    if ( is_null($this->price_id) && !is_null($this->price_name) && !is_null($this->manifestation_id) )
    {
      $q = Doctrine::getTable('PriceManifestation')->createQuery('pm')
        ->leftJoin('pm.Manifestation m')
        ->leftJoin('pm.Price p')
        ->andWhere('m.id = ?',$this->manifestation_id)
        ->andWhere('p.name = ?',$this->price_name)
        ->orderBy('pm.updated_at DESC');
      $pm = $q->execute()->get(0);
      $this->price_id = $pm->price_id;
      $this->value    = $pm->value;
    }
    
    // the transaction's last update
    $q = new Doctrine_Query();
    $q->from('Transaction')
      ->where('id = ?',$this->transaction_id)
      ->set('updated_at','NOW()')
      ->update();
    $q->execute();
    
    parent::preSave($event);
  }
  
  public function preInsert($event)
  {
    // cancellation ticket with member cards
    if ( $this->Price->member_card_linked && !($this->printed || $this->integrated ) && !is_null($this->cancelling) )
    {
      foreach ( $this->Transaction->Contact->MemberCards as $card )
      if ( strtotime($card->created_at) <= strtotime('now')
        && strtotime($card->expire_at) > strtotime('now') )
      {
        if ( !isset($models) )
          $models = Doctrine::getTable('MemberCardPriceModel')->createQuery('mcpm')
            ->andWhere('mcpm.price_id = ?',$this->price_id)
            ->execute();
        
        foreach ( $models as $model )
        if ( $card->name == $model->member_card_name && $model->price_id == $this->price_id )
        {
          $mcp = new MemberCardPrice;
          $mcp->price_id = $this->price_id;
          $mcp->member_card_id = $card->id;
          $mcp->save();
        }
      }
    }
    
    return parent::preInsert($event);
  }
  
  public function preUpdate($event)
  {
    // only for normal tickets and member cards
    if ( $this->Price->member_card_linked && ($this->printed || $this->integrated ) && is_null($this->cancelling) && is_null($this->duplicate) )
    {
      $q = Doctrine::getTable('MemberCard')->createQuery('mc')
        ->leftJoin('mc.Contact c')
        ->leftJoin('c.Transactions t')
        ->leftJoin('mc.MemberCardPrices mcp')
        ->leftJoin('mcp.Event e')
        ->leftJoin('e.Manifestations m')
        ->andWhere('t.id = ?',$this->transaction_id)
        ->andWhere('mc.created_at <= ?',date('Y-m-d H:i:s'))
        ->andWhere('mc.expire_at >  ?',date('Y-m-d H:i:s'))
        ->andWhere('mcp.price_id = ?',$this->price_id)
        ->andWhere('(mcp.event_id IS NULL OR m.id = ?)',$this->manifestation_id)
        ->orderBy('mcp.event_id');
      $card = $q->fetchOne();
      
      if ( $card && $card->MemberCardPrices->count() > 0 )
      {
        $card->MemberCardPrices[0]->delete();
        $this->member_card_id = $card->id;
      }
      else
      {
        $this->printed = false;
        throw new liEvenementException("No more ticket left on the contact's member card");
      }
    }
    
    return parent::preUpdate($event);
  }
  
  public function getBarcode($salt = '')
  {
    return md5('#'.$this->id.'-'.$salt);
  }
  
  public function getIdBarcoded()
  {
    $c = ''.$this->id;
    $n = strlen($c);
    for ( $i = 12-$n ; $i > 0 ; $i-- )
      $c = '0'.$c;
    return $c;
  }
  
  public function __toString()
  {
    return '#'.$this->id;
  }
}
