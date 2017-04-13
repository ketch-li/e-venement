<?php if ( $sf_user->hasCredential('kiosk-texts') ): ?>
  <li class="menu-setup-kiosk"><a><?php echo __('Kiosk',null,'menu')?></a>
    <ul class="third">
      <li><a href="<?php echo cross_app_url_for('grp','group_workspace/index') ?>"><?php echo __('Texts',array(),'menu') ?></a></li>
    </ul>
  </li>
<?php endif ?>