<?php include_partial('global/assets') ?>

<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
    <div class="fg-toolbar ui-widget-header ui-corner-all">
        <h1><?php echo __('User policy upload', null, 'messages') ?></h1>
    </div> 
    <?php include_partial('global/flashes') ?>
    <form action="<?php echo url_for('userPolicy/update') ?>" method="post" class="data" enctype="multipart/form-data">
        <?php echo $form ?>        
        <div class="help">
          <span class="ui-icon ui-icon-help floatleft"></span>
          <?php echo __("Adding a file here will overwrite your").' <a href="'.$link.'" target="_blank">'.__('User policy').'</a>' ?>
        </div>
        
        <input type="submit" name="submit" value="<?php echo __('Validate',null,'sf_admin') ?>" />
    </form>
</div>