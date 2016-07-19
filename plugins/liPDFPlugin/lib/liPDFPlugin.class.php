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
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

class liPDFPlugin
{
  protected $html, $pdf, $verbose_in_error_log;
  protected $composer_dir, $wkhtmltopdf;
  protected $options = array();

  public function __construct($html = false, $verbose_in_error_log = true)
  {
    $this->content = $html;
    $this->verbose_in_error_log = $verbose_in_error_log;
    $this->composer_dir = sfConfig::get('sf_lib_dir').'/vendor/composer/';
    $this->wkhtmltopdf  = $this->composer_dir.'h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64';
    
    if ( $html )
      $this->setHtml($html);
  }
  
  public function setHtml($html)
  {
    $this->html = $html;
    
    // with wkhtmltopdf
    if ( sfConfig::get('project_tickets_wkhtmltopdf', 'enabled') )
    try
    {
      if ( !is_executable($this->wkhtmltopdf) )
        throw new RuntimeException('No wkhtmltopdf executable found. Please check your setup.');
      
      require_once $this->composer_dir.'autoload.php';
      $snappy = new Knp\Snappy\Pdf($this->wkhtmltopdf);
      foreach ( $this->options as $option => $value )
        $snappy->setOption($option, $value);
      $this->pdf = $snappy->getOutputFromHtml($this->html);
      return;
    } catch ( RuntimeException $e ) {
      $this
        ->log('Printing tickets: [Error] '.$e->getMessage())
        ->log('Printing tickets: even if the executable is present, "wkhtmltopdf" is not working. Falling back to classic PDF generation.')
      ;
    }

    // without wkhtmltopdf, using DomPDF
    $pdf = new sfDomPDFPlugin();
    $pdf->setInput($html);
    $this->pdf = $pdf->render();
  }
  
  /**
   * @see Knp\Snappy\Pdf::setOption()
   */
  public function setOption($name, $value)
  {
    $this->options[$name] = $value;
    return $this;
  }
  
  public function getOptions($name = NULL)
  {
    if ( $name )
      return $this->options[$name];
    return $this->options;
  }
  
  public function getHtml()
  {
    return $this->html;
  }

  public function getPDF()
  {
    return $this->pdf;
  }
  
  protected function log($msg)
  {
    if ( $this->verbose_in_error_log )
      error_log($msg);
    return $this;
  }
  
  public function isReady()
  {
    try {
      if ( !is_executable($this->wkhtmltopdf) )
        throw new RuntimeException('No wkhtmltopdf executable found.');
      $snappy = new Knp\Snappy\Pdf($this->wkhtmltopdf);
      $snappy->getOutputFromHtml('<html><head></head><body>test</body><html>');
    } catch ( RuntimeException $e ) {
      return false;
    }
    
    return true;
  }
}


