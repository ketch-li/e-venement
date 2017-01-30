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
class userPolicyActions extends sfActions
{
    public function executeIndex(sfWebRequest $request) {        
        if ($url = $this->getFileUrl())
            $this->redirect($url);
        else {
            $this->form = $this->getForm();
            $this->getUser()->setFlash('error', __('No user policy file.'));
        }
    }

    public function executeEdit(sfWebRequest $request)
    {
      $this->form = $this->getForm();
      $this->getWidgets($this->form);
    }
    
    public function executeUpdate(sfWebRequest $request)
    {
      $this->form = $this->getForm();      
      $this->getWidgets($this->form);
      $values = $request->getPostParameters();
        
      // user policy
      foreach ( $request->getFiles() as $upload => $content )
      {
        $content = $content['file'];
        if ( $content['error'] == 0 ) 
        {            
            $fname = 'bo_policy.pdf';
            Doctrine::getTable('Picture')->createQuery('f')
              ->delete()
              ->where('f.name = ?', $fname)
              ->execute();
            
            $file = new Picture();
            $file->name = $fname;
            $file->content = base64_encode(file_get_contents($content['tmp_name']));
            $file->type = $content['type'];
            $file->save();            
          }
      }
      
      $this->getUser()->setFlash('success', __('The file has been successfully modified.'));      
      $this->redirect('userPolicy/edit');
    }

    protected function getForm()
    {
        sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
        $this->link = $this->getFileUrl(); 
        return new sfForm;
    }

    protected function getWidgets($form) {
        $ws = $form ->getWidgetSchema();
        $vs = $form ->getValidatorSchema();
        $ws['file'] = new sfWidgetFormInputFile(array('label' => __('User policy file')));      
        $ws->setNameFormat('policy[%s]');
        $vs['file'] = new sfValidatorFile(array(
            'required' => false,
            'mime_categories' => array('pdf' => array('application/pdf', 'application/x-pdf')),
            'mime_types'      => 'pdf'
        ));        
    }

    private function getFileUrl() {
        $file = Doctrine::getTable('Picture')->findOneByName('bo_policy.pdf');
        if($file) {
            return $file->getUrl();
        } else {
            return null;
        }            
    }

}
