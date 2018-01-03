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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
    if (!( isset($no_actions) && $no_actions ))
    $this->executeAccounting($request,true,$request->hasParameter('partial') ? (intval($request->getParameter('partial')).'' === $request->getParameter('partial') ? intval($request->getParameter('partial')) : $request->getParameter('manifestation_id')) : false);
    
    $this->partial = false;
    $this->invoice = false;
    if ( $request->hasParameter('partial') && intval($request->getParameter('manifestation_id')) > 0 )
    {
      $this->partial = true;
      foreach ( $this->transaction->Invoice as $key => $invoice )
      if ( $invoice->manifestation_id == intval($request->getParameter('manifestation_id')) )
        $this->invoice = $invoice;
      
      if ( !$this->invoice )
      {
        $this->invoice = new Invoice();
        $this->transaction->Invoice[] = $this->invoice;
      }
      $this->invoice->manifestation_id = intval($request->getParameter('manifestation_id'));
    }
    else
    {
      foreach ( $this->transaction->Invoice as $invoice )
      if ( is_null($invoice->manifestation_id) )
        $this->invoice = $invoice;
      
      if ( !$this->invoice )
        $this->invoice = new Invoice();
      $this->transaction->Invoice[] = $this->invoice;
    }
    
    $this->invoice->updated_at = date('Y-m-d H:i:s');
    $this->invoice->save();
    
    // preparing things for both PDF & HTML
    $this->data = array();
    foreach ( array('transaction', 'nocancel', 'tickets', 'invoice', 'products', 'totals', 'partial') as $var )
    if ( isset($this->$var) )
      $this->data[$var] = $this->$var;
    
    if ( $request->hasParameter('pdf') )
    {
      $this->getResponse()->setContentType('application/pdf');
      $this->getResponse()->setHttpHeader('Content-Disposition', 'attachment; filename="invoice-'.$this->invoice->id.'.pdf"');
            
      $pdf = new liPDFPlugin($this->getPartial('invoice_pdf', $this->data));

      return $this->renderText($pdf->getPDF());
    }
    
    if ( $request->hasParameter('email') )
    {
      $this->getContext()->getConfiguration()->loadHelpers(array('CrossAppLink','I18N'));
      $sf_user = $this->getUser()->getGuardUser();
      
      $pdf = new liPDFPlugin($this->getPartial('invoice_pdf', $this->data));
      $file = new Picture;
      $file->name = 'db:'.($fname = 'invoice-'.$this->invoice->id.'-'.date('YmdHis').'-'.rand(0,9999).'.pdf');
      $file->content = base64_encode($raw = $pdf->getPDF());
      $file->type = 'application/pdf';
      $file->save();
      
      $attachment = new Attachment;
      $attachment->original_name = $fname;
      $attachment->filename = $file->name;
      $attachment->mime_type = $file->type;
      $attachment->size = strlen($raw);
      
      $email = new Email;
      $email->field_subject = __('Your invoice for transaction #%%tid%%', array('%%tid%%' => $this->transaction->id), 'li_accounting');
      $email->field_from = $sf_user->email_address;
      if ( $this->transaction->contact_id )
        $email->Contacts[] = $this->transaction->Contact;
      $email->content = __('You will find your invoice from %%seller%% in this email message as an attachment.', array('%%seller%%' => sfConfig::get('app_seller_name', 'our ticketing system')), 'li_accounting');
      
      $email->Attachments[] = $attachment;
      $email->save();
      $this->redirect(cross_app_url_for('rp', 'email/edit?id='.$email->id));
    }
    
    return 'Success';
    


















