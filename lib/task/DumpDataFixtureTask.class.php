<?php

class DumpDataFixtureTask extends sfBaseTask
{
  
    protected function configure() 
    {
      $this->addArguments(array(
        new sfCommandArgument('table', sfCommandArgument::REQUIRED | sfCommandArgument::IS_ARRAY, 'The table to dump')
      ));
      $this->addOptions(array(
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      ));
      
      $this->namespace = 'e-venement';
      $this->name = 'dump-table';
      $this->briefDescription = 'Dump table to fixtures';
      $this->detailedDescription = <<<EOF
        The [aptaw:dump-table|INFO] Dump specified table name to yml fixture file:
        [./symfony e-venement:dump-table table_name [table_name] --env=dev|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array()) 
    {
      if ( !array_key_exists('table', $arguments) && count($arguments['table']) == 0 ) 
      {
        $this->logSection('Dump', 'Nothing to dump');
      }
      else
      {
        $this->logSection('Dump', 'Start dumping...');

        $databaseManager = new sfDatabaseManager($this->configuration);
        $data = new Doctrine_Data();
        $parser = new Doctrine_Parser_Yml();

        foreach ($arguments['table'] as $table_name) 
        {
          $this->logSection('Dump', 'Dumping table '.$table_name);
          
          $table = Doctrine::getTable($table_name);
                  
          if ( $table->hasRelation('Translation') ) 
          {
            $results = Doctrine::getTable($table_name)->createQuery('tn')
              ->leftJoin("tn.Translation ct")
              ->fetchArray();
            
            $parser->dumpData(array($table_name => $results), sfConfig::get('sf_root_dir').'/data/fixtures/dump/'.$table_name.'.yml');
          } 
          else 
          {
            $data->exportData(sfConfig::get('sf_root_dir').'/data/fixtures/dump', 'yml', array($table_name), true);
          }
        }

        $this->logSection('Dump', 'Tables dumped');  
      }
    }
}

?>