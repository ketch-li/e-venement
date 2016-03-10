<?php
class liOnlinePaymentOntheSpotPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    require_once __DIR__.'/../lib/OnthespotPayment.class.php';
  }
}
