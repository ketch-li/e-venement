<?php

/**
 * SurveyQuery
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    e-venement
 * @subpackage model
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class SurveyQuery extends PluginSurveyQuery
{
  public function render($value = null, $attributes = array(), $errors = array())
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Slug'));
    
    $widget = new $this->type;
    $widget->setLabel($this->name);
    $slug = slugify($this->name);
    
    return $widget->render($slug, $value, $attributes, $errors);
  }
  public function renderLabel()
  {
    sfApplicationConfiguration::getActive()->loadHelpers(array('Slug'));
    $slug = slugify($this->name);
    
    return '<label for='.$slug.'>'.$this->name.'</label>';
  }
}
