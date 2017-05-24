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
class CountryService extends EvenementService
{
    public function getAllCountries($culture)
    {
        $q = Doctrine::getTable('Country')->createQuery('c', true)->leftJoin('CountryTranslation ct')->where("ct.lang ='$culture'");
    
        return $q->fetchArray();
    }
}
