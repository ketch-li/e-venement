<?php

class myUser extends liGuardSecurityUser
{
	public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
	{
		parent::initialize($dispatcher, $storage, $options);

		$dispatcher->connect('kiosk.init', array($this, 'authenticate'));
	}

	public function authenticate()
	{
		$kioskUser = Doctrine::getTable('sfGuardUser')->retrieveByUsername(sfConfig::get('app_user_templating',-1));
		
		$this->signin($kioskUser, true);
	}
}
