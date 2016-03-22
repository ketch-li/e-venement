<?php

/**
 * OrganismTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class OrganismTable extends PluginOrganismTable
{
  public function findWithTickets($id)
  {
    $q = $this->createQuery('o')
      ->andWhere('o.id = ?',$id)
    ;
    
    if ( !sfContext::hasInstance() )
      $q->leftJoin('p.Transactions ptr');
    else
    {
      $sf_user = sfContext::getInstance()->getUser();
      $ws = array_keys($sf_user->getWorkspacesCredentials());
      $ws[] = 0;
      $me = array_keys($sf_user->getMetaEventsCredentials());
      $me[] = 0;
      $q->leftJoin('p.Transactions ptr WITH ptr.id IN (SELECT ttck3.transaction_id FROM Ticket ttck3 LEFT JOIN ttck3.Manifestation mm3 LEFT JOIN mm3.Event ee3 LEFT JOIN mm3.Gauges gg3 WHERE gg3.workspace_id IN ('.implode(',',$ws).') AND ee3.meta_event_id IN ('.implode(',',$me).'))');
    }
    $q->leftJoin('ptr.Payments payment')
      ->orderBy('c.name, c.firstname, pt.name, p.name');
    $organism = $q->fetchOne();
    return $organism;
  }

  public function createQueryByGroupId($id)
  {
    $q = $this->createQuery();
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Groups g")
      ->select("$a.id, $a.name, $a.postalcode, $a.city, g.id, count(p.id) AS nb_professionals")
      ->andWhere('g.id = ?',$id)
      ->orderBy("$a.name, $a.postalcode, $a.city")
      ->groupBy("$a.id, $a.name, $a.postalcode, $a.city, g.id");
    return $q;
  }

  public function createQueryByEmailId($id)
  {
    $q = $this->createQuery();
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Emails e")
      ->andWhere('e.sent = TRUE')
      ->andWhere('e.id = ?',$id)
      ->orderby('name, city');
    return $q;
  }
  public function createQuery($alias = 'a')
  {
    $p  = 'p'  != $alias ? 'p'  : 'p1';
    $c  = 'c'  != $alias ? 'c'  : 'c1';
    $pt = 'pt' != $alias ? 'pt' : 'pt1';
    $pn = 'pn' != $alias ? 'pn' : 'pn1';
    $oc = 'oc' != $alias ? 'oc' : 'oc1';
    
    $query = parent::createQuery($alias)
      ->leftJoin("$alias.Professionals $p")
      ->leftJoin("$p.ProfessionalType $pt")
      ->leftJoin("$p.Contact $c")
      ->leftJoin("$alias.Phonenumbers $pn")
      ->leftJoin("$alias.Category $oc");

    if ( sfContext::hasInstance() && ($sf_user = sfContext::getInstance()->getUser()) && $sf_user->getId() )
    if ( in_array(sfConfig::get('project_internals_pr_scope', 'none'), array('permissive', 'restrictive')) )
    {
      $query
        ->leftJoin("$p.Groups $gp")
        ->leftJoin("$gp.User $gpu")
        ->leftJoin("$alias.Groups $gc")
        ->leftJoin("$gc.User $gcu")
      ;
      switch ( sfConfig::get('project_internals_pr_scope', 'none') ) {
      case 'restrictive':
        $query->andWhere("$gcu.id = ? OR $gpu.id = ?", array($sf_user->getId(), $sf_user->getId()));
        break;
      case 'permissive':
        $query->andWhere("$gcu.id = ? OR $gpu.id = ? OR $gc.id IS NULL AND $gp.id IS NULL", array($sf_user->getId(), $sf_user$
        break;
      }
    }

    return $query;
  }
  
  public function fetchOneByName($name)
  {
    return $this->createQuery('o')
      ->andWhere('name = ?',$name)
      ->orderBy('created_at DESC')
      ->fetchOne();
  }

    /**
     * Returns an instance of this class.
     *
     * @return object OrganismTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Organism');
    }
}
