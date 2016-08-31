<a href="<?php echo sfConfig::get('app_seller_software_url', 'http://www.e-venement.org/') ?>">e-venement</a>
<?php echo __("the free and open source ticketing software",array(),'footer') ?> 
-
&copy; 2006-<?php echo date('Y') ?> 
<a href="http://www.libre-informatique.fr/">Libre Informatique</a>
<br/>
<?php echo __('Licensed under',array(),'footer') ?> 
<a href="http://www.gnu.org/licenses/gpl.html">GNU/GPL</a>
-
<?php echo __('Enhanced by',array(),'footer') ?> 
<a href="http://www.symfony-project.org/">Symfony</a>,
<a href="http://www.php.net/">PHP</a>,
<a href="http://www.postgresql.org/">PostgreSQL</a>

<?php // auto adding tickets linked to member cards ?>
<?php echo get_class($sf_user->getAttribute('pub.mc.autoadd_tickets',false)); ?>
<script type="text/javascript"><!--
  var url = '<?php echo url_for('ticket/autoAdd') ?>';
  var tickets = [];
<?php if ( ($mcps = $sf_user->getAttribute('pub.mc.autoadd_tickets',false)) && $mcps->count() > 0 ): ?>
<?php foreach ( $mcps as $mcp ): ?>
  tickets.push({
    price_id: <?php echo $mcp->price_id ?>,
    event_id: <?php echo $mcp->event_id ?>,
    quantity: <?php echo $mcp->quantity ?>
  });
<?php endforeach ?>
  $.ajax({
    url: url,
    data: { tickets: tickets },
    success: function(){ location.reload(); },
    error: function(){ location.reload(); }
  });
  $('body').html('Loading...');
<?php $sf_user->setAttribute('pub.mc.autoadd_tickets', false) ?>
<?php endif ?>
--></script>
