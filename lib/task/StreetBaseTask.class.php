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
*    Copyright (c) 2006-2016 Marcos Bezerra de Menezes <marcos.bezerra@libre-informatique.fr>
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

/**
 * This task fetches data to update the Street Base
 */
class StreetBaseTask  extends sfBaseTask
{
  protected $zipCodes = array();
  protected $counter = array();
  protected $mem = 0;

  protected function configure() {
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'dev'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'default'),
      new sfCommandOption('no-headers', sfCommandOption::PARAMETER_OPTIONAL),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'street-base';
    $this->briefDescription = 'Fetches data to update the Street Base';
    $this->detailedDescription = 'Fetches data to update the Street Base';

    $this->resetCounters();
    $this->zipCodes = $this->loadZipCodes();
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration, $options['env']);
    $databaseManager = new sfDatabaseManager($this->configuration);

    // Localities ("lieux-dits")
    $localitiesUrl = 'http://data.nantes.fr/fileadmin/data/datastore/nm/urbanisme/24440040400129_NM_NM_00090/LIEUX_DITS_NM_csv.zip';
    $localitiesFile = $this->downloadCSVFile($localitiesUrl, 'LIEUX_DITS_NM');
    if (! $localitiesFile ) {
      $this->logBlock('Failed downloading and extracting '.$localitiesUrl, 'ERROR');
      return 1;
    }
    //$localitiesFile = '/tmp/LIEUX_DITS_NM/LIEUX_DITS_NM.csv';
    $this->importCSV($localitiesFile, 'locality', 0);

    // Streets
    $streetsUrl = 'http://data.nantes.fr/fileadmin/data/datastore/nm/urbanisme/24440040400129_NM_NM_00001/ADRESSES_NM_csv.zip';
    $streetsFile = $this->downloadCSVFile($streetsUrl, 'ADRESSES_NM');
    if (! $streetsFile ) {
      $this->logBlock('Failed downloading and extracting '.$streetsUrl, 'ERROR');
      return 2;
    }
    //$streetsFile = '/tmp/ADRESSES_NM/ADRESSES_NM.csv';
    $this->importCSV($streetsFile, 'street', 0);

    $this->logSection('Done', '', null, 'INFO');
    print_r($this->counter);

    return 0;
  }

  /**
   * @param string $file    full path of CSV file to be parsed
   * @param string $type    can be "locality" or "street"
   * @return bool           true for success, false for failure
   */
  protected function importCSV($file, $type, $max_iter = null)
  {
    $this->logSection('Updating DB from CSV', $file, null, 'INFO');
    $time_start = microtime(true);
    $start_datetime = time() - 60;
    $count = 0;
    
    $this->memDiff(0);
    $table = Doctrine::getTable('GeoFrStreetBase');
    $query = Doctrine_Query::create($table->getConnection(), 'liDoctrineQuery')
      ->from('GeoFrStreetBase sb');
    $form = new GeoFrStreetBaseForm;

    //$form = new GeoFrStreetBaseForm();
    //$form->getValidator($form->getCSRFFieldName())->setOption('required', false);
    //$form->setValidator('id', new sfValidatorNumber(array('required' => false)));

    if (($handle = fopen($file, "r")) !== FALSE)
    {
      $this->memDiff(0);
      $cpt = 0;
      fgetcsv($handle, 0, ","); // skip first line
      $memLoop = memory_get_usage();
      while ( ($data = fgetcsv($handle, 0, ",")) !== FALSE )
      {
        $cpt++;
        $i = 100;
        echo "Loop #$cpt\n";
        $this->memDiff($i++);
        $sb = $this->parseCSVline($data, $type);
        $this->memDiff($i++);
        $this->processRecord($sb, $query, $form);
        //$table->getConnection()->commit();
        $this->memDiff($i++);
        printf("loop mem : %d kB | %d kB\n", memory_get_usage()/1024 - $memLoop, $memLoop = memory_get_usage()/1024);
      }
    }
    else {
      throw new sfCommandException(sprintf("Could not open CSV file: %s", $file));
    }

    // delete records that have not been updated
    $this->deleteOldRecords($start_datetime, $type);

    $this->counter['lines'] += $count;
    $this->counter['import_time'] += microtime(true) - $time_start;
  }

  protected function processRecord($sb, $query, $form)
  {
    $i=0;
  
        $this->memDiff($i++);
        
        $query
          ->where('sb.zip = ?', $sb['zip'])
          ->andWhere('sb.rivoli = ?', $sb['rivoli'])
          ->andWhere('sb.num = ?', $sb['num'])
          ->limit(1)
        ;
        $this->memDiff($i++); // 1
        $street = $query->fetchOne();
        $this->memDiff('new '.$i++); // 2
        $street = $street ? $street : new GeoFrStreetBase;
        $this->memDiff('new '.$i++); // 3
        
        $this->memDiff('form '.$i++); // 4
        $form->bind($sb);
        $this->memDiff('form '.$i++);
        
        if ( $form->isValid() )
        {
          $modified = false;
          foreach ( $sb as $key => $value )
            $street->$key = $value;
          $this->memDiff('match '.$i++);
          
          $street->save($query->getConnection());
          $this->memDiff('save '.$i++);
          
          $street->free(true);
          $this->memDiff('free '.$i++);
        }
        else
          echo "ERROR: ".$form->getErrorSchema();
        
        unset($street);
        $this->memDiff('object '.$i++);
  }
  
  protected function findRecord($zip, $rivoli, $num)
  {
    $query = Doctrine_Core::getTable('GeoFrStreetBase')
      ->createQuery('sb')
      ->andWhere('sb.zip = :zip')
      ->andWhere('sb.rivoli = :rivoli')
      ->andWhere('sb.num = :num');
    $res = $query->fetchOne( array(':zip'=>$zip, ':rivoli'=>$rivoli, ':num'=>$num) );
    $query->free();
  }

  /**
   * @param array $line     array of data extacted from a CSV line
   * @param string $type    can be "locality" or "street"
   * @return array          GeoFrStreetBase
   */
  protected function parseCSVline($line, $type)
  {
    foreach($line as $k => $d)
      $line[$k] = trim(utf8_encode($d));  // data must latin1

    $sb_array = array();
    switch ($type) {
      case 'locality':
        $sb_array['locality'] = true;
        $sb_array['city'] = $line[0];
        $sb_array['zip'] = $this->findZipCode($line[0]);
        $sb_array['address'] = $line[2];
        $sb_array['rivoli'] = $line[3];
        $sb_array['iris2008'] = $line[4];
        $sb_array['longitude'] = $line[5];
        $sb_array['latitude'] = $line[6];
        $sb_array['num'] = '';
        break;
      case 'street':
        $sb_array['locality'] = false;
        $sb_array['address'] = $line[0];
        $sb_array['city'] = $line[1];
        $sb_array['num'] = $line[3];
        $sb_array['rivoli'] = $line[4];
        $sb_array['zip'] = $line[5];
        $sb_array['iris2008'] = $line[6];
        $sb_array['longitude'] = $line[7];
        $sb_array['latitude'] = $line[8];
        break;
      default:
        throw new sfCommandException(sprintf("Invalid type parameter for parseCSVline(): %s", $type));
    }
    return $sb_array;
  }

  /**
   * Downloads the zip file, extract it and return the fownloaded CSV file path
   *
   * @param string $url        url of the zip file to fetch
   * @param string $prefix     directory (relative to /tmp) where the files will be extracted
   * @return boolean|string    CSV file path or false on failure
   */
  protected function downloadCSVFile($url, $prefix)
  {
    $this->logSection('Downloading', $url, $this->strlen($url), 'INFO');
    $time_start = microtime(true);

    set_time_limit(0); //prevent timeout

    $zip_fname = tempnam(sys_get_temp_dir(), $prefix);
    $zip_file = fopen($zip_fname, "w+");

    // Download zip file
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FILE, $zip_file); //auto write to file
    curl_setopt($ch, CURLOPT_TIMEOUT, 5040);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    curl_close($ch);
    fclose($zip_file);
    if ( $res === false )
      return false;

    // Extract zipped files
    $destDir = sys_get_temp_dir() . '/' . $prefix;
    $zip = new ZipArchive;
    $res = $zip->open($zip_fname);
    if ($res === TRUE) {
        $zip->extractTo($destDir);
        $zip->close();
    } else {
        return false;
    }

    // Delete zip file
    unlink($zip_fname);

    // find CSV file
    $files = array();
    foreach (glob("$destDir/*.csv") as $file) {
      $files[] = $file;
    }
    $this->counter['download_time'] += microtime(true) - $time_start;
    return $files ? $files[0] : false;
  }

  protected function loadZipCodes() {
    return array(
      "NANTES" => "44000",
      "REZE" => "44400",
      "SAINT-AIGNAN-GRANDLIEU" => "44860",
      "BOUGUENAIS" => "44340",
      "CARQUEFOU" => "44470",
      "ST-SEBASTIEN" => "44230",
      "BOUAYE" => "44830",
      "VERTOU" => "44120",
      "LE-PELLERIN" => "44640",
      "COUERON" => "44220",
      "SAUTRON" => "44880",
      "INDRE" => "44610",
      "ST-HERBLAIN" => "44800",
      "LA-CHAPELLE-SUR-ERDRE" => "44240",
      "ORVAULT" => "44700",
      "LES-SORINIERES" => "44840",
      "SAINT-JEAN-DE-BOISEAU" => "44640",
      "THOUARE-SUR-LOIRE" => "44470",
      "SAINT-LEGER-LES-VIGNES" => "44710",
      "BASSE-GOULAINE" => "44115",
      "SAINTE-LUCE-SUR-LOIRE" => "44980",
      "LA-MONTAGNE" => "44620",
      "MAUVES-SUR-LOIRE" => "44470",
      "BRAINS" => "44830",
    );
  }

  protected function findZipCode($city) {
    return isset($this->zipCodes[$city]) ? $this->zipCodes[$city] : "";
  }

  protected function resetCounters()
  {
    $this->counter = array(
      'lines' => 0,
      'updates' => 0,
      'creations' => 0,
      'deletions' => 0,
      'db_errors' => 0,
      'validation_errors' => 0,
      'download_time' => 0,
      'import_time' => 0,
    );
    $this->mem = 0;
  }

  protected function memDiff($msg)
  {
    $old_mem = $this->mem;
    $this->mem = memory_get_usage();
    printf("- mem %s : %+d kB \n", $msg, ($this->mem - $old_mem)/1024);
  }

  /**
   * Delete all records that have not been updated after a given date
   *
   * @param integer $time     EPOCH integer
   * @param string $type      "locality" or "street"
   */
  protected function deleteOldRecords($time, $type)
  {
    $query = Doctrine_Core::getTable('GeoFrStreetBase')
      ->createQuery('sb')
      ->where('sb.locality = ?', $type == 'locality')
      ->andWhere('sb.updated_at < ?', date('Y-m-d H:i:s', $time));

    //echo $query->delete()->getSqlQuery() . "\n";
    $nb_deleted = $query->delete()->execute();
    $this->counter['deletions'] += $nb_deleted;
    return $nb_deleted;
  }

}
