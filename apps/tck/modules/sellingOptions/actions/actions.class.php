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
  
  protected function getForm()
  {
    return new OptionTckForm;
  }
  
  public function executeUpdate(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('I18N');
    $this->form = $this->getForm();
    $params = $request->getPostParameters();

    if ($params)
    {
      $this->form->bind($params, array());
      
      if ( !$this->form->isValid() )
      {
        $this->getUser()->setFlash('error',__('Your form cannot be validated.'));
        return $this->setTemplate('index');
      }
      
      $user_id = NULL;
      
      $cpt = $this->form->save($user_id);
      $this->getUser()->setFlash('notice',__('The option has been successfully modified.'));
      $this->redirect('sellingOptions/index');
    }
  }
}
