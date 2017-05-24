<?php include_partial('global/assets') ?>

<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
        <h1><?php echo __('Selling screen configuration', null, 'messages') ?></h1>
    </div> 
    <?php include_partial('global/flashes') ?>
    <?php echo $form->renderFormTag('sellingOptions/change') ?>
    <?php echo $form ?>        
    <input type="submit" name="submit" value="<?php echo __('Validate',null,'sf_admin') ?>" />
</div>