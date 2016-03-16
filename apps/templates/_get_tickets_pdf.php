<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="title" content="e-venement, Billet" />
    <title>e-venement, Billet</title>
    <link rel="shortcut icon" href="/images/logo-evenement.png" />
    <script type="text/javascript" data-script-url="<?php echo $path = '/private/print-simplified-tickets.js'; $path = sfConfig::get('sf_web_dir').$path; ?>">
      <?php if ( file_exists($path) ): ?>
        <?php echo file_get_contents($path) ?>
      <?php endif ?>
    </script>
  </head>
  <body class="pdf app-<?php echo $sf_context->getConfiguration()->getApplication() ?>">
    <div id="content"><?php include_partial('global/get_tickets',array('tickets_html' => $tickets_html)) ?></div>
  </body>
</html>
