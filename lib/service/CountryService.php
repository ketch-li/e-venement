<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ManifestationService
 *
 * @author Romain SANCHEZ <romain.sanchez@libre-informatique.fr>
 */
class CountryService extends EvenementService
{
    public function getAllCountries($culture)
    {
        $q = Doctrine::getTable('Country')->createQuery('c')
          ->leftJoin("c.Translation ct WITH ct.lang = ?", $culture)
          ->orderBy('ct.name')
        ;

        return $q->fetchArray();
    }
}
