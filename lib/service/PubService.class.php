<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PubService
 *
 * @author Baptiste SIMON <baptiste.simon@libre-informatique.fr>
 */
class PubService extends EvenementService
{
    public function getToken($transaction_id)
    {
        return md5($transaction_id.'|*|*|'.sfConfig::get('project_eticketting_salt', 'e-venement'));
    }
}
