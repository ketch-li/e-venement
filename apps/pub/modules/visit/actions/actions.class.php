<?php

require_once __DIR__.'/../lib/visitGeneratorConfiguration.class.php';
require_once __DIR__.'/../lib/visitGeneratorHelper.class.php';
require_once __DIR__.'/../../event/actions/actionEvent.trait.php';

/**
 * visit actions.
 *
 * @package    symfony
 * @subpackage visit
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class visitActions extends autoVisitActions
{
  use actionEvent;
}
