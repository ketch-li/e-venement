<script type="text/javascript">
  <?php if ( $sf_user->hasFlash('notice') || $sf_user->hasFlash('error') ): ?>
    setTimeout(function(){
      window.close();
    },3000);
  <?php else: ?>
    window.close();
  <?php endif ?>
</script>
<?php include_partial('global/flashes') ?>
