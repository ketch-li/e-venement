<?php

class myUser extends liGuardSecurityUser
{
	public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array())
	{
		parent::initialize($dispatcher, $storage, $options);

		$kioskUser = Doctrine::getTable('sfGuardUser')->retrieveByUsername(sfConfig::get('app_user_templating',-1));
		$this->setCulture('fr');
		$this->signin($kioskUser, true);
	}
}
