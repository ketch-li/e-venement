<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of testOcDecisionHelperTask
 *
 * @author Marcos Bezerra de Menezes <marcos.bezerra@libre-informatique.fr>
 */
class testOcDecisionHelperTask extends sfBaseTask
{
  public function configure()
  {
    $this->namespace = 'e-venement';
    $this->name      = 'test-oc-decision-helper';
    $this->briefDescription = 'Test the Online Choices Decision Helper service';
    $this->detailedDescription = "";

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'default'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));
  }

  public function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration, $options['env']);
    echo 'Hello, World!';
    $this->logSection('Dump', 'Nothing to dump');

    // get the service we want to test
    $service = sfContext::createInstance($this->configuration)->getContainer()->get('oc_decision_helper');

    $data = $this->getSampleData();
    print_r($data);
    $service->init($data);
    print_r($service->getAllTimeSlots());
    print_r($service->getAllManifestations());
    print_r($service->getAllParticipants());
    $service->process($data);
  }

  /**
   * @return array [$timeSlots, $manifestations, $contacts]
   */
  protected function getSampleData()
  {
    return [
        ['id' => 1, 'name' => 'Ambassadeur A', 'manifestations' => [
            ['id' => 1, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 2, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 3, 'time_slot_id' => 1, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
            ['id' => 4, 'time_slot_id' => 2, 'gauge_free'=>1, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 5, 'time_slot_id' => 2, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
        ]],
        ['id' => 2, 'name' => 'Ambassadeur B', 'manifestations' => [
            ['id' => 1, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 2, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 3, 'time_slot_id' => 1, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
            ['id' => 5, 'time_slot_id' => 2, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 6, 'time_slot_id' => 2, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
        ]],
        ['id' => 3, 'name' => 'Ambassadeur C', 'manifestations' => [
            ['id' => 1, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 2, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 3, 'time_slot_id' => 1, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
            ['id' => 4, 'time_slot_id' => 2, 'gauge_free'=>1, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 5, 'time_slot_id' => 2, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 6, 'time_slot_id' => 2, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
        ]],
        ['id' => 4, 'name' => 'Ambassadeur D', 'manifestations' => [
            ['id' => 1, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 2, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 3, 'time_slot_id' => 1, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
            ['id' => 4, 'time_slot_id' => 2, 'gauge_free'=>1, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 5, 'time_slot_id' => 2, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 6, 'time_slot_id' => 2, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
        ]],
        ['id' => 5, 'name' => 'Ambassadeur E', 'manifestations' => [
            ['id' => 1, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 2, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 3, 'time_slot_id' => 1, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
            ['id' => 4, 'time_slot_id' => 2, 'gauge_free'=>1, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 5, 'time_slot_id' => 2, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 6, 'time_slot_id' => 2, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
        ]],
        ['id' => 1, 'name' => 'Ambassadeur A', 'manifestations' => [
            ['id' => 1, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 2, 'time_slot_id' => 1, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 3, 'time_slot_id' => 1, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
            ['id' => 4, 'time_slot_id' => 2, 'gauge_free'=>1, 'rank' => 1, 'accepted' => 'none'],
            ['id' => 5, 'time_slot_id' => 2, 'gauge_free'=>2, 'rank' => 2, 'accepted' => 'none'],
            ['id' => 6, 'time_slot_id' => 2, 'gauge_free'=>3, 'rank' => 3, 'accepted' => 'none'],
        ]],
    ];
  }
}