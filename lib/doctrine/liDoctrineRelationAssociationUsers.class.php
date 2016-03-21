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
class liDoctrineRelationAssociationUsers extends Doctrine_Relation_Association
{
  public static function create(Doctrine_Relation $rel)
  {
    if ( ! $rel instanceof Doctrine_Relation_Association )
      throw new liEvenementException(sprintf('%s is usable only with a Doctrine_Relation_Association template.', 'liDoctrineRelationAssociationUsers'));
    if ( ! $rel->getTable() instanceof sfGuardUserTable )
      throw new liEvenementException(sprintf('You cannot use %s for something else that sfGuardUserTable relations whereas %s is used here.', 'liDoctrineRelationAssociationUsers', get_class($rel->getTable())));
    return new self($rel->getDefinition());
  }
  
  public static function isSubDomain($domain)
  {
    $current = sfConfig::get('project_internals_users_domain', '');
    return !$current || $current == '.' // if not set or root
      ? true
      : preg_match(sprintf('/^(.*\w\.)?%s$/', $current), $domain) === 1; // if $domain finishes with ".$current"
  }
  
  public static function removeUpperUsersFromCollection($users)
  {
    foreach ( $users as $key => $user )
    if ( $user->Domain->count() > 0 )
    if ( !liDoctrineRelationAssociationUsers::isSubDomain($user->Domain[0]->name) )
      unset($users[$key]);
    return $users;
  }
  
  /**
   * @see sfDoctrineRelationAssociation::fetchRelatedFor()
   * @throws liEvenementException
   **/
  public function fetchRelatedFor(Doctrine_Record $record)
  {
    $table = $this->definition['refTable'];
    $component = $this->definition['refTable']->getComponentName();
    
    $q = $this->getTable()->createQuery('a')
      ->leftJoin("a.$component _subalias")
      ->andWhere(sprintf('_subalias.%s = ?',$this->getLocalRefColumnName()), $record->getIncremented())
    ;
    $coll = $q->execute();
    $coll->setReference($record, $this);
    return $coll;
  }
}
