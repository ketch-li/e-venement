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
  protected $counter = array();
  protected $mem = 0;
  protected $verbosity = 0;
  protected $sendEmail = 0;
  protected $emailContent = "";

  protected function configure() {
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environement', 'task'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application', 'rp'),
      new sfCommandOption('cities-url', null, sfCommandOption::PARAMETER_OPTIONAL, '', null),
      new sfCommandOption('localities-url', null, sfCommandOption::PARAMETER_OPTIONAL, '', null),
      new sfCommandOption('verbosity', null, sfCommandOption::PARAMETER_OPTIONAL, '0: nothing / 1: summary at end of task / 2: summary + memory usage', 0),
      new sfCommandOption('email', null, sfCommandOption::PARAMETER_OPTIONAL, '0: no emails / 1: email on failure / 2: email on success or failure', 0),
      new sfCommandOption('max-iter', null, sfCommandOption::PARAMETER_OPTIONAL, 'max nb of lines to import from each CSV file (0 = no limit)', 0),
    ));
    $this->namespace = 'e-venement';
    $this->name = 'street-base';
    $this->briefDescription = 'Fetches data to update the Street Base';
    $this->detailedDescription = 'Fetches data to update the Street Base';

    $this->resetCounters();
    $this->emailContent = "";
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration, $options['env']);
    $databaseManager = new sfDatabaseManager($this->configuration);

    sfConfig::set('sf_debug', false);

    $this->verbosity = (int)$options['verbosity'];
    $this->sendEmail = (int)$options['email'];
    $max_iter = $options['max-iter'];

    // Localities ("lieux-dits")
    // $localitiesUrl = 'http://data.nantes.fr/fileadmin/data/datastore/nm/urbanisme/24440040400129_NM_NM_00090/LIEUX_DITS_NM_csv.zip';
    if ( $localitiesUrl = $options['localities-url'] )
    {
      $localitiesFile = $this->downloadCSVFile($localitiesUrl, 'LIEUX_DITS_NM');
      if (! $localitiesFile ) {
        $this->logError('Failed downloading and extracting '.$localitiesUrl);
        $this->emailContent .= "ERROR : Failed downloading and extracting $localitiesUrl \n";
        $this->sendEmail('error');
        return 1;
      }
      $this->importCSV($localitiesFile, 'locality', $max_iter);
    }

    // Streets
    //$streetsUrl = 'http://data.nantes.fr/fileadmin/data/datastore/nm/urbanisme/24440040400129_NM_NM_00001/ADRESSES_NM_csv.zip';
    if ( $streetsUrl = $options['streets-url'] )
    {
      $streetsFile = $this->downloadCSVFile($streetsUrl, 'ADRESSES_NM');
      if (! $streetsFile ) {
        $this->logError('Failed downloading and extracting '.$streetsUrl);
        $this->emailContent .= "ERROR : Failed downloading and extracting $streetsUrl \n";
        $this->sendEmail('error');
        return 2;
      }
      $this->importCSV($streetsFile, 'street', $max_iter);
    }

    $this->logInfo('Done', '');

    if ($this->verbosity > 0)
      print_r($this->counter);

    $this->emailContent .= print_r($this->counter, 1) . "\n";
    $this->sendEmail('success');

    return 0;
  }

  /**
   * @param string $file    full path of CSV file to be parsed
   * @param string $type    can be "locality" or "street"
   * @return bool           true for success, false for failure
   */
  protected function importCSV($file, $type, $max_iter = null)
  {
    $this->logInfo('Updating DB from CSV', $file);
    $time_start = microtime(true);
    $start_datetime = time() - 60;
    $count = 0;

    $this->memDiff(0);
    $table = Doctrine::getTable('GeoFrStreetBase');
    $query = Doctrine_Query::create($table->getConnection(), 'liDoctrineQuery')
      ->from('GeoFrStreetBase sb');
    $form = new GeoFrStreetBaseForm;

    if (($handle = fopen($file, "r")) !== FALSE)
    {
      $this->memDiff(0);
      $cpt = 0;
      fgetcsv($handle, 0, ","); // skip first line
      $memLoop = memory_get_usage()/1024;
      while ( ($data = fgetcsv($handle, 0, ",")) !== FALSE )
      if ( !$max_iter || $max_iter >= ($cpt + 1))
      {
        $cpt++;
        $i = 100;
        if ($this->verbosity > 1)
          echo "Loop #$cpt\n";
        $this->memDiff($i++);
        $sb = $this->parseCSVline($data, $type);
        $this->memDiff($i++);
        $this->processRecord($sb, $query, $form);
        //$table->getConnection()->commit();
        $this->memDiff($i++);
        if ($this->verbosity > 1)
          printf("loop mem : %d kB | %d kB\n", memory_get_usage()/1024 - $memLoop, $memLoop = memory_get_usage()/1024);
      }
    }
    else {
      throw new sfCommandException(sprintf("Could not open CSV file: %s", $file));
    }

    // delete records that have not been updated
    $this->deleteOldRecords($start_datetime, $type);

    $this->counter['lines'] += $cpt;
    $this->counter['import_time'] += microtime(true) - $time_start;
  }

  protected function processRecord($sb, $query, $form)
  {
    $i=0;

    $this->memDiff($i++);

    $query
      ->where('sb.zip = ?', $sb['zip'])
      ->andWhere('sb.city = ?', $sb['city'])
      ->andWhere('sb.address = ?', $sb['address'])
      ->limit(1)
    ;
    $this->memDiff($i++); // 1
    $street = $query->fetchOne();
    $this->memDiff('new '.$i++); // 2
    $new = $street ? false : true;
    $street = $street ? $street : new GeoFrStreetBase;
    $this->memDiff('new '.$i++); // 3

    $this->memDiff('form '.$i++); // 4
    $form->bind($sb);
    $this->memDiff('form '.$i++); // 5

    if ( $form->isValid() )
    {
      $modified = false;
      foreach ( $sb as $key => $value )
        $street->$key = $value;
      $street->updated_at = 'now';
      $this->memDiff('match '.$i++);

      if ( $street->isModified() )
      {
        $street->save($query->getConnection());
        if ($new)
          $this->counter['creations']++;
        else
          $this->counter['updates']++;
        $this->memDiff('save '.$i++);
      }

      $street->free(true);
      $street = null;
      $this->memDiff('free '.$i++);
    }
    else
    {
      echo "ERROR: ".$form->getErrorSchema()."\n";
      $this->counter['validation_errors']++;
    }

    unset($street);
    $this->memDiff('object '.$i++);
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
        $sb_array['zip'] = $line[4];
        $sb_array['address'] = $line[2];
        $sb_array['rivoli'] = $line[3];
        $sb_array['iris2008'] = $line[5] ? $line[5] : null;
        $sb_array['longitude'] = $line[6];
        $sb_array['latitude'] = $line[7];
        $sb_array['num'] = '';
        break;
      case 'street':
        $sb_array['locality'] = false;
        $sb_array['address'] = $line[0];
        $sb_array['city'] = $line[1];
        $sb_array['num'] = $line[3];
        $sb_array['rivoli'] = $line[4];
        $sb_array['zip'] = $line[5];
        $sb_array['iris2008'] = $line[6] ? $line[6] : null;
        $sb_array['longitude'] = $line[9];
        $sb_array['latitude'] = $line[10];
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
    $this->logInfo('Downloading', $url);
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
    if ($this->verbosity > 1) {
      $old_mem = $this->mem;
      $this->mem = memory_get_usage();
      printf("- mem %s : %+d kB \n", $msg, ($this->mem - $old_mem)/1024);
    }
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

  protected function logInfo($section, $msg) {
    $this->logSection($section, $msg, null, 'INFO');
    $this->emailContent .= "$section :\n\t$msg\n\n";
  }

  protected function logError($msg) {
    $this->logBlock($msg, 'ERROR');
    $this->emailContent .= "ERROR :\n\t$msg\n\n";
  }

  /**
   * @param string $type    'success' or 'failure'
   */
  protected function sendEmail($type)
  {
    $subject = "[e-venement] ";
    $client = sfConfig::get('project_about_client');
    $clientName = $client && isset($client['name']) ? $client['name'] : 'no name Client';

    switch($type) {
      case 'failure':
        if ($this->sendEmail < 1)
          return;
        $subject .= "StreetBaseTask ERROR report for $clientName";
        break;
      case 'success':
        if ($this->sendEmail < 2)
          return;
        $subject .= "StreetBaseTask report for $clientName";
        break;
    }

    $firm = sfConfig::get('project_about_firm');
    $recipient = $firm && isset($firm['email']) ? $firm['email'] : null;
    if ( !$recipient ) {
      $this->logError('Could not send email (no recipient)');
      return;
    }

    $this->logSection('Sending email report', "to: $recipient", null, 'INFO');
    $this->logSection('Sending email report', "subject: $subject", null, 'INFO');

    $email = new Email;
    $email->isATest(false);
    $email->setNoSpool(true);
    $email->field_subject = $subject;
    $email->to = $recipient;
    $email->field_from = $recipient;
    $email->content = str_replace("\n", "<br>", $this->emailContent);
    $email->content_text = $this->emailContent;
    $email->save();
  }

}
