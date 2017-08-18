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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require __DIR__.'/payplug_php/init.php';

class PayplugPayment extends OnlinePayment
{
  const name = 'payplug';
  protected $value = 0;
  protected $payplug = null;
  
  public static function create(Transaction $transaction)
  {
    self::config();
    return new self($transaction);
  }
  
  public function __construct(Transaction $transaction)
  {
    parent::__construct($transaction);
    $this->payplug = \Payplug\Payment::create($this->getRequestOptions());
  }
  
  // generates the request
  public function render(array $attributes = array())
  {
    if ( !sfContext::hasInstance() )
      return (string)$this;
    
    sfContext::getInstance()->getActionStack()->getFirstEntry()->getActionInstance()->redirect($this->getUrl());
    return '';
  }
  
  public static function getPayment()
  {
    self::config();
    $input = file_get_contents('php://input');
    
    try {
      $resource = \Payplug\Notification::treat($input);
      if ( $resource instanceof \Payplug\Resource\Payment )
        return $resource;
      throw new \Payplug\Exception\PayplugException('Error reading a payment from upstream');
    }
    catch (\Payplug\Exception\PayplugException $exception) {
      error_log('PayPlug: '.$exception);
      throw $exception;
    }
  }
  
  public static function getTransactionIdByResponse(sfWebRequest $parameters)
  {
    return self::getPayment()->metadata['order'];
  }
  public function response(sfWebRequest $request)
  {
    $bank = $this->createBankPayment($request);
    $bank->save();
    return array('success' => $bank->code == 'paid', 'amount' => $bank->amount);
  }
  
  public function createBankPayment(sfWebRequest $request)
  {
    $bank = new BankPayment;
    $payment = $this->getPayment();
    
    // the BankPayment Record
    $bank->code                 = $payment->is_paid ? 'paid' : 'other';
    $bank->payment_certificate  = $payment->id;
    $bank->authorization_id     = $payment->id;
    $bank->merchant_id          = sfConfig::get('app_payment_id', 'test@test.tld');
    $bank->capture_mode         = self::name;
    $bank->transaction_id       = $payment->metadata['order'];
    $bank->amount               = $payment->amount/100;
    $bank->currency_code        = $payment->currency;
    $bank->complementary_code   = $payment->is_3ds ? '3ds' : null;
    $bank->card_number          = $payment->card->last4;
    $bank->complementary_info   = $payment->card->brand;
    if ( $payment->failure )
    {
      $bank->bank_response_code = $payment->failure->code;
      $bank->data_field         = $payment->failure->message;
    }
    $bank->payment_time         = $payment->created_at;
    $bank->return_context       = $payment->is_live ? 'prod' : 'test';
    $bank->raw                  = file_get_contents("php://input");
    
    return $bank;
  }
  
  public function getUrl()
  {
    return $this->payplug->hosted_payment->payment_url;
  }
  
  public function getArguments()
  {
    return [];
  }
  
  public function getMethod()
  {
    return '';
  }
  
  public function getRequestOptions(Transaction $transaction = NULL, $amount = NULL)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
    
    if ( is_null($transaction) )
      $transaction = $this->transaction;
    if ( is_null($amount) )
      $amount = $this->value*100;
    
    $config_urls = sfConfig::get('app_payment_url', array());
    foreach ( $config_urls as $key => $url )
      $config_urls[$key] = url_for($url, true);
    
    $options = array(
      'amount'    => $amount,
      'currency'  => sfConfig::get('app_payment_currency', 'EUR'),
      'metadata'  => array(
        'order'     => $transaction->id,
        'customer'  => $transaction->contact_id,
        'origin'    => 'e-voucher '.sfConfig::get('software_about_version','v2'),
      ),
      'hosted_payment'   => array(
        'cancel_url' => $config_urls['cancel'],
        'return_url' => $config_urls['normal'],
      ),
      'notification_url' => $config_urls['automatic'],
    );
    
    if ( $transaction->contact_id )
    {
      $options['customer'] = array(
        'first_name' => $transaction->Contact->firstname,
        'last_name'  => $transaction->Contact->name,
      );
      if ( $transaction->Contact->email )
        $options['customer']['email'] = $transaction->Contact->email;
    }
    
    return $options;
  }
  
  public static function config()
  {
    \Payplug\Payplug::setSecretKey(sfConfig::get('app_payment_password', 'pass'));
  }
  
  public function __toString()
  {
    return '
      <a href="'.$this->getUrl().'" class="'.(sfConfig::get('app_payment_autofollow', true) ? 'autofollow' : '').'" target="_top">
        <img src="https://www.payplug.fr/static/merchant/images/logo-large.png" alt="PayPlug" />
      </a>
    ';
  }
  
  protected static function getMd5FromRequest(array $options)
  {
    return md5(json_encode($options).sfConfig::get('app_payment_salt'));
  }
}
