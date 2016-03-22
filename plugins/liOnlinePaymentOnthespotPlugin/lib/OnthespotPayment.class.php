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
*    Copyright (c) 2006-2016 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
  
  class OnthespotPayment extends OnlinePayment
  {
    const name = 'onthespot';
    protected $options = array();
    
    public static function create(Transaction $transaction)
    {
      return new self($transaction);
    }
    
    public static function getTransactionIdByResponse(sfWebRequest $request)
    {
      return $request->getParameter('transaction_id', false);
    }
    
    public function response(sfWebRequest $request)
    {
      return array('success' => true, 'amount' => $transaction->getPrice(true, true));
    }
    
    protected function __construct(Transaction $transaction)
    {
      foreach ( array(
        'button_text' => __('Payment by other means'),
        'autofollow' => true,
      ) as $key => $value )
        $this->options[$key] = sfConfig::get('app_payment_'.$key, $value);
      
      // the transaction and the amount
      parent::__construct($transaction);
    }
    
    public function render(array $attributes = array())
    {
      if ( !isset($attributes['onclick']) )
        $attributes['onclick'] = "javascript: return $(this).find('span').length > 0 ? confirm($(this).find('span').text()) : true";
      
      $attrs = '';
      foreach ( $attributes as $name => $value )
        $attrs .= " $name=\"$value\"";
      $info = $this->options['button_text'];
      return '<a href="'.url_for('cart/onthespot?id='.$this->transaction->id).'" '.$attrs.'>'.$info.'</a>';
    }

    public function __toString()
    {
      try {
        return $this->render(array(
          'class' => $this->options['autofollow'] ? 'autofollow' : '',
          'id' => 'onthespot-link'
        ));
      }
      catch ( sfException $e )
      {
        return 'An error occurred creating the link with the bank';
      }
    }
    
    public function createBankPayment(sfWebRequest $request)
    {
      $bank = new BankPayment;
      
      if (! $request instanceof sfWebRequest )
        throw new liOnlineSaleException('We cannot save the raw data from the bank.');
      
      $bank->code = 0;
      $bank->payment_certificate = 0;
      $bank->authorization_id = 0;
      $bank->merchant_id = 0;
      $bank->customer_ip_address = $_SERVER['REMOTE_ADDR'];
      $bank->capture_mode = 0;
      $bank->transaction_id = $this->transaction->id;
      $bank->amount = $this->transaction->getPrice(true,true);
      $bank->raw = $_SERVER['QUERY_STRING'];
      
      return $this->BankPayment = $bank;
    }
  }
