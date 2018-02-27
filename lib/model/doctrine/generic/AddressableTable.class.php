<?php

/**
 * AddressableTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class AddressableTable extends PluginAddressableTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object AddressableTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Addressable');
    }
    
    public function __construct($name, Doctrine_Connection $conn, $initDefinition)
    {
      parent::__construct($name, $conn, $initDefinition);
      $this->getTemplate('Doctrine_Template_Searchable')->getPlugin()
        ->setOption('analyzer', new MySearchAnalyzer());
    }
}
