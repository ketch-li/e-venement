<?php

/**
 * ContactTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ContactTable extends PluginContactTable
{
  public function retreiveList()
  {
    return $this->createQuery('c')
      ->leftJoin('o.Phonenumbers oph')
    ;
  }
  public function createQueryByEmailId($id)
  {
    $q = $this->createQuery();
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Emails ce")
      ->leftJoin("p.Emails pe")
      ->andWhere('(TRUE')
      ->andWhere('ce.sent = TRUE')
      ->andWhere('ce.id = ?',$id)
      ->orWhere('pe.id = ?',$id)
      ->andWhere('pe.sent = TRUE')
      ->andWhere('TRUE)')
      ->orderby('name','firstname');
    return $q;
  }
  
  public function createQueryByGroupId($id)
  {
    $id = intval($id);
    $q = $this->createQuery();
    $a = $q->getRootAlias();
    $q->leftJoin("$a.ContactGroups gc ON $a.id = gc.contact_id AND gc.group_id = $id")
      ->leftJoin("p.ProfessionalGroups gp ON p.id = gp.professional_id AND gp.group_id = $id")
      ->andWhere('(gc.group_id = ? OR gp.group_id = ?)',array($id,$id))
      ->orderby('name','firstname');
    return $q;
  }
  
  public function createQuery($alias = 'a')
  {
    $p  = 'p'   != $alias ? 'p'   : 'p1';
    $pt = 'pt'  != $alias ? 'pt'  : 'pt1';
    $o  = 'o'   != $alias ? 'o'   : 'o1';
    $gp = 'gp'  != $alias ? 'gp'  : 'gp1';
    $gc = 'gc'  != $alias ? 'gc'  : 'gc1';
    $gpu = 'gpu'!= $alias ? 'gpu' : 'gpu1';
    $gcu = 'gcu'!= $alias ? 'gcu' : 'gcu1';
    $u  = 'u'   != $alias ? 'u'   : 'u1';
    $pn = 'pn'  != $alias ? 'pn'  : 'pn1';
    $y  = 'y'   != $alias ? 'y'   : 'y1';
    
    $query = parent::createQuery($alias)
      ->leftJoin("$alias.Professionals $p")
      ->leftJoin("$p.ProfessionalType $pt")
      //->leftJoin("$p.Groups $gp")
      //->leftJoin($gp.'.Picture pic'.$gp)
      //->leftJoin("$gp.User $gpu")
      ->leftJoin("$p.Organism $o")
      //->leftJoin("$alias.Groups $gc")
      //->leftJoin($gc.'.Picture pic'.$gc)
      //->leftJoin("$gc.User $gcu")
      ->leftJoin("$alias.Phonenumbers $pn")
      ->leftJoin("$alias.YOBs $y")
      ->andWhere("$alias.confirmed = ?", true);
    
    return $query;
  }

  public function findWithTickets($id)
  {
    $q = $this->createQuery('c')
      ->leftJoin('c.EventArchives ea')
      ->andWhere('c.id = ?',$id)
      ->orderBy('ea.happens_at DESC')
    ;
    
    if ( !sfContext::hasInstance() )
      $q->leftJoin('c.Transactions transac WITH transac.professional_id IS NULL')
        ->leftJoin('p.Transactions ptr')
        ->leftJoin('c.DirectTickets dc')
      ;
    else
    {
      $sf_user = sfContext::getInstance()->getUser();
      $ws = array_keys($sf_user->getWorkspacesCredentials());
      $ws[] = 0;
      $me = array_keys($sf_user->getMetaEventsCredentials());
      $me[] = 0;
      $q->leftJoin('c.Transactions transac WITH transac.professional_id IS NULL AND transac.id IN (SELECT ttck.transaction_id FROM Ticket ttck LEFT JOIN ttck.Manifestation mm LEFT JOIN mm.Event ee LEFT JOIN mm.Gauges gg WHERE gg.workspace_id IN ('.implode(',',$ws).') AND ee.meta_event_id IN ('.implode(',',$me).'))')
        ->leftJoin('c.DirectTickets dc WITH dc.id IN (SELECT ttck2.transaction_id FROM Ticket ttck2 LEFT JOIN ttck2.Manifestation mm2 LEFT JOIN mm2.Event ee2 LEFT JOIN mm2.Gauges gg2 WHERE gg2.workspace_id IN ('.implode(',',$ws).') AND ee2.meta_event_id IN ('.implode(',',$me).'))')
        ->leftJoin('p.Transactions ptr WITH ptr.id IN (SELECT ttck3.transaction_id FROM Ticket ttck3 LEFT JOIN ttck3.Manifestation mm3 LEFT JOIN mm3.Event ee3 LEFT JOIN mm3.Gauges gg3 WHERE gg3.workspace_id IN ('.implode(',',$ws).') AND ee3.meta_event_id IN ('.implode(',',$me).'))')
      ;
    }
    $q->leftJoin('transac.Payments payment');
    $contact = $q->fetchOne();
    return $contact;
  }
  
  public function fetchOneById($id, $clear = true)
  {
    if ( ''.intval($id) !== ''.$id || intval($id) <= 0 )
      return false;
    
    return !$clear
      ? $this->createQuery('c')->andWhere('c.id = ?',$id)->orderBy('c.id')->fetchOne()
      : $this->createQuery('c')->where('c.id = ?',$id)->orderBy('c.id')->fetchOne();
  }
  
  public function doSelectOnlyGrp(Doctrine_Query $q)
  {
    $a = $q->getRootAlias();
    $q->leftJoin("p.ContactEntries ce")
      ->andWhere('ce.id IS NOT NULL');
    return $q;
  }
  
    /*
     * Returns an instance of this class.
     *
     * @return object ContactTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Contact');
    }
}
