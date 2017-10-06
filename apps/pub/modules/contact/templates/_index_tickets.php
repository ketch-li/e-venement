
<div class="anchor">
  <div class="anchor-tickets">
    <a id="anchor-dl-tickets" href="<?php echo url_for('contact/downloadTickets') ?>" target="_blank" class="waves-effect btn">
        <?php echo __('Download all my pending tickets') ?>
    </a>
  </div>
  <div class="anchor-history">
    <a id="anchor-full-history" href="#" class="waves-effect btn">
        <?php echo __('My full history') ?>
    </a>
  </div>
</div>
  
<script type="text/javascript">
    $(document).ready(function(){
        // hide details
        $('#manifestations, #transactions').hide();
        
        // show details
        $('#anchor-full-history').click(function(){
            $('#manifestations, #transactions').slideDown();
            $(this).fadeOut();
            return false;
        });
    });
</script>
