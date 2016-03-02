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

    $this->zipCodes = $this->loadZipCodes();
  }

  protected function execute($arguments = array(), $options = array())
  {
//    throw new liEvenementException('Work still in progress... contact@libre-informatique.fr for more information.');

    sfContext::createInstance($this->configuration, $options['env']);
    $databaseManager = new sfDatabaseManager($this->configuration);

    // Localities ("lieux-dits")
    $localitiesUrl = 'http://data.nantes.fr/fileadmin/data/datastore/nm/urbanisme/24440040400129_NM_NM_00090/LIEUX_DITS_NM_csv.zip';
    $localitiesFile = $this->downloadCSVFile($localitiesUrl, 'LIEUX_DITS_NM');
    if (! $localitiesFile ) {
      $this->logBlock('Failed downloading and extracting '.$localitiesUrl, 'ERROR');
      return 1;
    }
    $localities = $this->parseCSV($localitiesFile);
    if (!$localities) {
      $this->logBlock('Failed parsing '.$localitiesFile, 'ERROR');
      return 2;
    }

    // Streets
    $streetsUrl = 'http://data.nantes.fr/fileadmin/data/datastore/nm/urbanisme/24440040400129_NM_NM_00001/ADRESSES_NM_csv.zip';
    $streetsFile = $this->downloadCSVFile($streetsUrl, 'ADRESSES_NM');
    if (! $streetsFile ) {
      $this->logBlock('Failed downloading and extracting '.$streetsUrl, 'ERROR');
      return 3;
    }
    $streets = $this->parseCSV($streetsFile);
    if (!$streets) {
      $this->logBlock('Failed parsing '.$streetsFile, 'ERROR');
      return 4;
    }

    // Delete DB table content
    Doctrine_Query::create()->from('GeoFrStreetBase')->delete()->execute();

    // Import data into DB
    $this->importLocalities($localities, $options['env']);
    $this->importStreets($streets, $options['env']);

    return 0;
  }

  protected function importLocalities($localities, $env)
  {
    $this->logSection('Updating DB (localities)', '', 0, 'INFO');
    sfContext::createInstance($this->configuration, $env);
    $databaseManager = new sfDatabaseManager($this->configuration);

    foreach ($localities as $k => $d) {
      $sb = new GeoFrStreetBase;
      $sb->setLocality(true);
      $sb->setCity($d[0]);
      $zipcode = $this->findZipCode($d[0]);
      if (!$zipcode)
        continue;
      $sb->setZip($zipcode);
      $sb->setAddress($d[2]);
      $sb->setRivoli($d[3]);
      $sb->setIris2008($d[4]);
      $sb->setLongitude($d[5]);
      $sb->setLatitude($d[6]);
      $sb->save();
       print('.');
    }
  }

  protected function importStreets($streets, $env)
  {
    sfContext::createInstance($this->configuration, $env);
    $databaseManager = new sfDatabaseManager($this->configuration);

    foreach ($streets as $k => $d) {
      $sb = new GeoFrStreetBase;
      $sb->setLocality(false);
      $sb->setAddress($d[0]);
      $sb->setCity($d[1]);
      $sb->setNum($d[3]);
      $sb->setRivoli($d[4]);
      $sb->setZip($d[5]);
      $sb->setIris2008($d[6]);
      $sb->setLongitude($d[7]);
      $sb->setLatitude($d[8]);
      $sb->save();
      if ($k % 10 == 0) print('.');
    }
  }

  protected function downloadCSVFile($url, $prefix)
  {
    $this->logSection('Downloading', $url, $this->strlen($url), 'INFO');

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
    return $files ? $files[0] : false;
  }

  protected function parseCSV($file, $header = true)
  {
    $this->logSection('Parsing CSV', $file, $this->strlen($file), 'INFO');
    $rows = array();
    if (($handle = fopen($file, "r")) !== FALSE)
    {
      if ($header) fgetcsv($handle, 0, ","); // skip first line

      while (($data = fgetcsv($handle, 0, ",")) !== FALSE)
      {
          foreach($data as $k => $d)
            $data[$k] = trim(utf8_encode($d));
          $rows[] = $data;
      }
      fclose($handle);
    }
    return $rows;
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

}
