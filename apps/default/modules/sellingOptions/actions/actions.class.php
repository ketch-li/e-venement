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
*    Copyright (c) 2006-2016 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
class sellingOptionsActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
      $this->form = $this->getForm();
    }
    
    public function executeChange(sfWebRequest $request)
    {
      $this->form = $this->getForm();      
      $params = $request->getParameter('Options');
      
      if ($params) {
          $this->form->bind($params);
          if ( $this->form->isValid() )
          {
              if (array_key_exists('selling_option', $params)) {
                  if (!$this->group->getPermissions()->search($this->permission)) {
                      $gp = new sfGuardGroupPermission();
                      $gp->setGroup($this->group);
                      $gp->setPermission($this->permission);
                      $gp->save();
                      $this->getUser()->setFlash('success', 'The option has been successfully modified.');
                  }
              } else {
                  $q = Doctrine_Query::create()
                    ->delete('sfGuardGroupPermission u')
                    ->where('u.group_id = ?', 14)
                    ->andWhere('u.permission_id = ?', 186);
                  $q->execute();
                  $this->getUser()->setFlash('success', 'The option has been successfully modified.');
              }
          }
          else
            $this->getUser()->setFlash('error', 'Please, try again...');          
      }
      
      $this->redirect('sellingOptions/index');
    }

    protected function getForm()
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
        
        $form = new sfForm;
        $ws = $form ->getWidgetSchema();
        $vs = $form ->getValidatorSchema();
        
        $this->group = Doctrine::getTable('sfGuardGroup')->findOneByName('tck-operator');
        $this->permission = Doctrine::getTable('sfGuardPermission')->findOneByName('tck-print-ticket-cp');
        
        $ws['selling_option'] = new sfWidgetFormInputCheckbox(array('label' => __('Force postal code')), array('value' => 1));      
        $vs['selling_option'] = new sfValidatorBoolean();
        $ws->setNameFormat('Options[%s]');

        $form->setDefault('selling_option', $this->group->getPermissions()->search($this->permission));
        
        return $form;
    }

}
