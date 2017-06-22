<?php

/**
 * MemberCardType form.
 *
 * @package    symfony
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MemberCardTypeForm extends BaseMemberCardTypeForm
{
  protected $user;
  
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->user = sfContext::getInstance()->getUser();
    
    $this->widgetSchema['users_list']
      ->setOption('expanded',true)
      ->setOption('order_by',array('username',''));
    $this->widgetSchema   ['product_declination_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'url' => cross_app_url_for('pos', 'declination/ajax'),
      'model' => 'ProductDeclination',
    ));
    $this->validatorSchema['product_declination_id']->setOption('query', Doctrine::getTable('ProductDeclination')->createQuery('pd')
      ->leftJoin('pd.Product p')
      ->andWhereIn('p.meta_event_id IS NULL OR p.meta_event_id', array_keys($this->user->getMetaEventsCredentials()))
      ->leftJoin('p.Prices price')
      ->leftJoin('price.Users pu')
      ->andWhere('pu.id = ?', $this->user->getId())
    );
    
    $this->widgetSchema['nb_tickets_mini']
      ->setAttribute('pattern', '\d+')
      ->setAttribute('min', 0)
      ->setAttribute('type', 'number')
    ;
    
    $this->widgetSchema['price_id']
      ->setOption('table_method', 'getPriceList');
    
    $tinymce = array(
      'config'  => array(
        'extended_valid_elements' => 'html,head,body,hr[class|width|size|noshade],iframe[src|width|height|name|align],style',
        'convert_urls' => false,
        'urlconvertor_callback' => 'email_urlconvertor',
        'paste_as_text' => false,
        'plugins' => 'textcolor link image',
        'toolbar1' => 'formatselect fontselect fontsizeselect | link image | forecolor backcolor | undo redo',
        'toolbar2' => 'bold underline italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent blockquote',
        'force_br_newlines' => false,
        'force_p_newlines'  => false,
        'forced_root_block' => '',
      ),
    );
    $this->widgetSchema['public_details'] = new liWidgetFormTextareaTinyMCE($tinymce);
    
    // promo code
    if ( sfContext::hasInstance() && !sfContext::getInstance()->getUser()->hasCredential('pr-card-promo-edit') )
    {
      $this->widgetSchema   ['promo_codes'] = new sfWidgetFormInputHidden;
      $this->validatorSchema['promo_codes'] = new sfValidatorPass;
    }
    else
      $this->embedRelation('PromoCodes as promo_codes');
  }
}
