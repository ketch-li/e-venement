<?php

class layoutComponents  extends sfComponents
{
  public function initialize($context, $moduleName, $actionName)
  {
    parent::initialize($context, $moduleName, $actionName);
    $this->initLayout($this->request);
  }

  public function executeStylesheets()
  {
    $this->public_stylesheet = ($this->theme && $this->layout && $this->layout != 'old') ?
      sprintf("public/%s-%s.css", $this->layout, $this->theme) :
      'public.css';
  }

  public function executeBodyClass()
  {
    $class = ($this->layout && $this->layout != 'old') ? $this->layout : 'default';
    $this->body_class = 'layout-' . $class;
  }

  public function executeLayoutSwitcher()
  {
    if ($this->getUser()->getAttribute('_layout')) {
      $this->displaySwitcher = true;
      $root_dir = sfConfig::get('sf_root_dir');

      $this->layouts = array();
      foreach (glob("$root_dir/web/scss/public/layout/_*.scss") as $filename)
        $this->layouts[] = substr(basename($filename, '.scss'), 1);
      $this->layouts[] = 'old';

      $this->themes = array();
      foreach (glob("$root_dir/web/scss/public/theme/_*.scss") as $filename)
        $this->themes[] = substr(basename($filename, '.scss'), 1);
    }
    else
      $this->displaySwitcher = false;
  }

  private function initLayout(sfWebRequest $request)
  {
    // Set layout
    if (!sfConfig::get('app_options_unlock_layout')) {
      $this->getUser()->getAttributeHolder()->remove('_layout');
      $this->getUser()->getAttributeHolder()->remove('_theme');
    }
    else {
      $layout = $request->getParameter('_layout');
      if ($layout) {
        if ($layout == 'reset') {
          $this->getUser()->getAttributeHolder()->remove('_layout');
          $this->getUser()->getAttributeHolder()->remove('_theme');
        }
        else
          $this->getUser()->setAttribute('_layout', $layout);
      }
    }
    $this->layout = $this->getUser()->getAttribute('_layout', sfConfig::get('app_options_layout'));

    // Set theme
    if (sfConfig::get('app_options_unlock_layout')) {
      $theme = $request->getParameter('_theme');
      if ($theme && $this->layout)
          $this->getUser()->setAttribute('_theme', $theme);
    }
    $this->theme = $this->getUser()->getAttribute('_theme', sfConfig::get('app_options_theme'));
  }

}
