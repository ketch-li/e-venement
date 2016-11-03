<link rel="stylesheet" type="text/css" media="screen" href="/css/<?php echo $public_stylesheet ?>" />
<?php if ( $sf_context->getConfiguration()->getApplication() == 'public' ): ?>
<link rel="stylesheet" type="text/css" media="screen" href="/private/public.css?<?php echo date('Ymd') ?>" />
<?php else: ?>
<link rel="stylesheet" type="text/css" media="screen" href="/private/<?php echo $sf_context->getConfiguration()->getApplication() ?>.css?<?php echo date('Ymd') ?>" />
<?php endif ?>
