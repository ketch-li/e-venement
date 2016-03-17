<?php
  if ( false && sfConfig::get('sf_web_debug', false) ) // because here the content-type is always set to PDF
  {
    echo get_partial('global/get_tickets_pdf', array('tickets_html' => $content));
    return;
  }
  
  $pdf = new liPDFPlugin(get_partial('global/get_tickets_pdf', array('tickets_html' => $content)));
  echo $pdf->getPDF();

