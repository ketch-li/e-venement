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
*    Copyright (c) 2006-2017 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class ResendOldEmailsTask extends sfProjectSendEmailsTask
{
  protected function configure() 
  {
    $this->addArguments(array(
      new sfCommandArgument('path', sfCommandArgument::REQUIRED, 'The file to extract')
    ));
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'Application', 'rp'),
      new sfCommandOption('message-limit', null, sfCommandOption::PARAMETER_OPTIONAL, 'The maximum number of messages to send', 0),
      new sfCommandOption('time-limit', null, sfCommandOption::PARAMETER_OPTIONAL, 'The time limit for sending messages (in seconds)', 3600),
      new sfCommandOption('delay', null, sfCommandOption::PARAMETER_OPTIONAL, 'The delay to wait between 2 emails', sfConfig::get('app_options_email_delay',5)),
    ));
    
    $this->namespace = 'e-venement';
    $this->name = 'resend-old-emails';
    $this->briefDescription = 'Resend email from a list';
    $this->detailedDescription = <<<EOF
      The [aptaw:resend-old-emails|INFO] Resend emails from a csv list containing ids:
      [./symfony e-venement:resend-old-emails csv_path --env=dev|INFO]
EOF;
  }

  protected function importCSV($path)
  {
    if (($handle = fopen($path, "r")) !== FALSE)
    {
      fgetcsv($handle, 0, ","); // skip first line
      
      while ( ($data = fgetcsv($handle, 0, ",") ) !== FALSE )
      {
        $id = $data[0];
        $q = "INSERT INTO tmp_email (eid) VALUES ($id)";
        $st = $this->con->execute($q);
      }
    }
  }

  protected function execute($arguments = array(), $options = array())
  {
    $path = $arguments['path'];

    sfContext::createInstance($this->configuration);
    $dbm = new sfDatabaseManager($this->configuration);
    $dbm->initialize($this->configuration);
    $this->con = Doctrine_Manager::getInstance()->connection();

    // Create temp table to store emails
    $q = "
      CREATE TEMP TABLE tmp_email (
        eid integer
      );
    ";
    $st = $this->con->execute($q);

    // Import data
    $this->importCSV($path);

    // Set the sent property to false
    $q = "
      UPDATE email
      SET sent = false
      WHERE id IN (
        SELECT eid 
        FROM tmp_email
      );
    ";
    $st = $this->con->execute($q);
    
    $q = "
      SELECT eid FROM tmp_email;
    ";
    $st = $this->con->execute($q);
    $ids = $st->fetchAll(Doctrine_Core::FETCH_COLUMN);
    
    //$this->logSection($this->name, print_r($ids, true));
    
    if ( count($ids) > 0 )
    {
      $emails = Doctrine_Query::create()
        ->from('Email e')
        ->andWhereIn('e.id', $ids)
        ->execute();
      
      $this->logSection($this->name, sprintf('sending %d emails', $emails->count()));
      
      foreach ($emails as $email) {
        $email->isATest(false);
        $email->save();
      }      
    }

    $spool = $this->getMailer()->getSpool()->setFlushDelay($options['delay']);

    parent::execute($arguments, $options);
  }
}
