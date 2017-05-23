<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ManifestationService
 *
 * @author Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class ManifestationsService extends EvenementService
{
    public function buildQuery(sfGuardUser $user, $transaction_id = 0, $alias = 'm')
    {
        // init
        $transaction_id = $transaction_id ? $transaction_id : 0;
        if ( $transaction_id.'' === ''.intval($transaction_id) ) {
            $transaction_id = 0;
        }
        
        // query
        $q = Doctrine::getTable('Manifestation')->createQuery($alias,true)
          ->leftJoin("$alias.PriceManifestations pm")
          ->leftJoin('pm.Price pmp WITH pmp.hide = FALSE')
          ->leftJoin("$alias.Gauges g WITH g.onsite = TRUE OR g.id IN (SELECT tck.gauge_id FROM Ticket tck WHERE tck.transaction_id = $transaction_id)")
          ->leftJoin('g.PriceGauges pg')
          ->leftJoin('pg.Price pgp WITH pgp.hide = FALSE')
          ->leftJoin('g.Workspace w')
          ->leftJoin('w.Order wuo ON wuo.workspace_id = w.id AND wuo.sf_guard_user_id = '.$user->getId())
          ->orderBy("et.name, met.name, $alias.happens_at, $alias.duration, wuo.rank, w.name, $alias.id")
          ->leftJoin('pmp.WorkspacePrices pmpwp WITH pmpwp.workspace_id = w.id')
          ->leftJoin('pmp.UserPrices      pmpup WITH pmpup.sf_guard_user_id = '.$user->getId())
          ->leftJoin('pgp.WorkspacePrices pgpwp WITH pgpwp.workspace_id = w.id')
          ->leftJoin('pgp.UserPrices      pgpup WITH pgpup.sf_guard_user_id = '.$user->getId())
          ->leftJoin('w.WorkspaceUsers wsu WITH wsu.sf_guard_user_id = '.$user->getId())
          ->andWhere('wsu.sf_guard_user_id IS NOT NULL')
        ;
        
        return $q;
    }
    
    public function completeQueryWithContact(Doctrine_Query $q, $contact_id = NULL)
    {
        if ( $contact_id !== NULL && $contact_id.'' !== ''.intval($contact_id) ) {
            return $q;
        }
        
        $q
            
            // available prices
            ->andWhere('(FALSE')
                ->orWhere('pmpup.price_id IS NOT NULL')
                ->orWhere('pmpwp.price_id IS NOT NULL')
                ->orWhere('pgpup.price_id IS NOT NULL')
                ->orWhere('pgpwp.price_id IS NOT NULL')
                ->orWhere('FALSE)')
            
            // member card linked prices
            ->andWhere('(FALSE')
                ->orWhere('pgp.member_card_linked = FALSE')
                ->orWhere('pmp.member_card_linked = FALSE');
            if ( $contact_id ) {
              $q->orWhere('pgp.id IN (SELECT mcp1.price_id FROM MemberCardPrice mcp1 LEFT JOIN mcp1.MemberCard mc1 LEFT JOIN mc1.Contact cc1 WHERE cc1.id = ?)', $contact_id)
                ->orWhere('pmp.id IN (SELECT mcp2.price_id FROM MemberCardPrice mcp2 LEFT JOIN mcp2.MemberCard mc2 LEFT JOIN mc2.Contact cc2 WHERE cc2.id = ?)', $contact_id);
            }
            $q->orWhere('FALSE)')
            
        ;
        
        return $q;
    }
}
