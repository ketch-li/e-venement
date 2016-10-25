<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" itemscope itemtype="http://schema.org/Event" prefix="og: http://ogp.me/ns#">
  <head>
    <?php $module_name = $sf_context->getModuleName() ?>
    <?php $sf_response->setTitle(sfConfig::get('app_informations_title')) ?>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <meta property="og:url" content="<?php echo $sf_request->getUri() ?>" />
    <meta property="og:type" content="article" />
    <link rel="shortcut icon" href="<?php echo image_path('logo-evenement.png') ?>" />
    <?php include_stylesheets() ?>
    <?php if ($sf_context->getConfiguration()->getApplication() == 'pub') include_component('layout', 'stylesheets') ?>
    <?php include_javascripts() ?>
  </head>
  <body class="<?php include_partial('global/body_classes') ?>">
    <?php if ($sf_context->getConfiguration()->getApplication() == 'pub') include_component('layout', 'layoutSwitcher') ?>
    <div id="client-header"></div>
    <div id="content">
      <?php include_partial('global/oplog') ?>
      <?php echo $sf_content ?>
      <?php if ( sfConfig::get('app_social_media_display', false) ): ?>
        <?php include_partial('global/social_networks') ?>
      <?php endif ?>
    </div>
    <div id="client-footer"></div>
    <div id="client-infos"></div>
    <ul id="menu" class="first">
      <?php include_partial('global/public_choices') ?>
    </ul>
    <div id="footer">
      <?php include_partial('global/footer') ?>
      <?php include_partial('global/date') ?>
      <?php include_partial('global/cart_widget') ?>
    </div>
  </body>
</html>
