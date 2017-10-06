<a id="anchor-dl-tickets" href="<?php echo url_for('contact/downloadTickets') ?>" target="_blank">
    <?php echo __('Download all my pending tickets') ?>
</a>
<a id="anchor-full-history" href="#">
    <?php echo __('My full history') ?>
</a>

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
