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
}
