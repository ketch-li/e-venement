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
  require_once __DIR__.'/vendor/lib/paylineSDK.php';
  
  class PaylinePayment extends OnlinePayment
  {
    const name = 'Payline';
    protected $id, $key;
    
    public static function create(Transaction $transaction)
    {
      return new self($transaction);
    }
    
    public static function getTransactionIdByResponse(sfWebRequest $request)
    {
      if ( !$request->getParameter('eve-tid',0) )
        die();
      return $request->getParameter('eve-tid',0);
    }
    
    public function response(sfWebRequest $request)
    {
      $this->createBankPayment($request)->save();
      $r = array(
        'success' => $this->BankPayment->transaction_id == self::getTransactionIdByResponse($request) ? $this->BankPayment->code == '00000' : false,
        'transaction_id' => $this->BankPayment->transaction_id ? $this->BankPayment->transaction_id : self::getTransactionIdByResponse($request),
        'amount' => $this->BankPayment->amount/100,
      );
      error_log('liOnlinePaymentPaylinePlugin: Transaction #'.$r['transaction_id'].($r['success'] ? '' : ' not').' paid.');
      return $r;
    }
    
    protected function getPayline()
    {
      // the configuration
      $this->id       = sfConfig::get('app_payment_id');
      $this->key      = sfConfig::get('app_payment_key');
      $this->contract = sfConfig::get('app_payment_contract','');
      $this->proxy    = sfConfig::get('app_payment_proxy', array());
      $this->production = sfConfig::get('app_payment_mode','prod') == 'prod';
      $this->currency = sfConfig::get('app_payment_currency','978');
      $this->urls     = sfConfig::get('app_payment_url',array());
      $this->autofollow = sfConfig::get('app_payment_autofollow', true);
      
      $this->proxy = array();
      foreach ( array('host', 'port', 'login', 'password') as $field )
      if ( !isset($this->proxy[$field]) )
        $this->proxy[$field] = '';
      
      $payline = new paylineSDK($this->id, $this->key, $this->proxy['host'], $this->proxy['port'], $this->proxy['login'], $this->proxy['password'], $this->production ? paylineSDK::ENV_PROD : paylineSDK::ENV_HOMO);
      sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
      $payline->cancelURL = url_for($this->urls['cancel'],true);
      $payline->returnURL = url_for($this->urls['normal'].'?eve-tid='.$this->transaction->id,true);
      $payline->notificationURL = url_for($this->urls['automatic'].'?eve-tid='.$this->transaction->id,true);
      
      return $payline;
    }
    
    protected function __construct(Transaction $transaction)
    {
      // the transaction and the amount
      parent::__construct($transaction);
      // the configuration
      $this->payline = $this->getPayline();
    }
    
    public function getUrl()
    {
      $payment = array(
        'payment' => array(
          'amount'   => $this->value*100,
          'currency' => $this->currency,
          'action'   => 101,   // default
          'mode'     => 'CPT', // default
          'contractNumber' => $this->contract,
        ),
        'order'   => array(
          'ref'      => $this->transaction->id,
          'amount'   => $this->value*100,
          'currency' => $this->currency,
        ),
      );
      
      $result = $this->payline->doWebPayment($payment);
      return $result['redirectURL'];
    }
    public function getMethod()
    {
        return 'GET';
    }
    public function getArguments()
    {
        return [];
    }
    
    public function render(array $attributes = array())
    {
      $payment = array(
        'payment' => array(
          'amount'   => $this->value*100,
          'currency' => $this->currency,
          'action'   => 101,   // default
          'mode'     => 'CPT', // default
          'contractNumber' => $this->contract,
        ),
        'order'   => array(
          'ref'      => $this->transaction->id,
          'amount'   => $this->value*100,
          'currency' => $this->currency,
        ),
      );
      
      $result = $this->payline->doWebPayment($payment);
      $r = '';
      if ( $this->autofollow )
      {
        if ( $result['result']['code'] == '00000' )
        {
          $attr = '';
          foreach ( $attributes as $key => $value )
            $attr .= $key.'="'.$value.'" ';
          $r .= '<a href="'.$result['redirectURL'].'" '.$attr.'>Payline</a>';
          header('Location: '.$result['redirectURL']);
        }
        else
        {
          sfContext::getInstance()->getUser()->setFlash('error', $err = 'An error occurred with Payline/Payline, try again...');
          error_log('liOnlinePaymentPaylinePlugin: '.$err);
          header('Location: '.$_SERVER['HTTP_REFERER']);
        }
      }
      else
      {
        if ( $result['result']['code'] == '00000' )
        {
          $attr = '';
          foreach ( $attributes as $key => $value )
            $attr .= $key.'="'.$value.'" ';
          $r .= '<a href="'.$result['redirectURL'].'" '.$attr.'>Payline</a>';
        }
        $r .= ' ';
        $r .= '<pre>'.print_r($payment,true).'</pre>';
        $r .= '<pre>'.print_r($result,true).'</pre>';
        $r .= 'Response URL: '.$this->payline->notificationURL;
      }
      return $r;
    }

    public function __toString()
    {
      try {
        return $this->render(array(
          'class' => sfConfig::get('app_payment_autofollow',true) ? 'autofollow' : '',
          'id' => 'payment-form'
        ));
      }
      catch ( sfException $e )
      {
        return 'An error occurred creating the link with the bank';
      }
    }
    
    public function createBankPayment(sfWebRequest $request)
    {
      $response = array();
      $response['version'] = $request->getParameter('version', liOnlinePaymentPaylinePluginConfiguration::paylineVersion);
      if (!( $token = $response['token'] = $request->getParameter('token', false) ))
      {
        $r['success'] = false;
        $r['amount'] = 0;
        throw new liOnlineSaleException('Error reading the Payline token ('.print_r($_POST,true).')');
      }
      $response = $this->payline->getWebPaymentDetails($response);
      
      $bank = new BankPayment;
      
      $bank->payment_certificate = $token;
      $bank->code = $response['result']['code'];
      $bank->return_context = $response['result']['shortMessage'];
      $bank->complementary_info = $response['result']['longMessage'];
      
      $bank->authorization_id = $response['transaction']['id'];
      $bank->payment_date = $response['transaction']['date'];
      $bank->payment_means = $response['transaction']['threeDSecure'] == 'N' ? 'weak' : 'secured';
      $bank->response_code = $response['transaction']['isPossibleFraud'] ? 'suspicious' : 'safe';
      
      $bank->amount = $response['payment']['amount'];
      $bank->currency_code = $response['payment']['currency'];
      $bank->merchant_id = $response['payment']['contractNumber'];
      
      $bank->receipt_complement = $response['authorization']['number'];
      $bank->card_number = isset($response['card']['number']) ? $response['card']['number'] : NULL;
      $bank->transaction_id = isset($response['order']) ? $response['order']['ref'] : $this->getTransactionIdByResponse($request);
      
      $bank->raw = json_encode($response);
      
      return $this->BankPayment = $bank;
    }
    
    public function getProviderTransactionId()
    {
      return $this->BankPayment instanceof BankPayment ? $this->BankPayment->authorization_id : false;
    }
  }
