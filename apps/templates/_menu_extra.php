<?php
/*
            // submenus added through a plugin
            $submenus = $sf_data->getRaw('sf_context')->getConfiguration()->getAppendedMenus($menu);
            foreach ( $submenus as $label => $submenu )
            {
              $submenus[$i18n=__($label,null,isset($submenu['i18n']) ? $submenu['i18n'] : null)]['url']
                = cross_app_url_for($submenu['url']['app'], $submenu['url']['route']);
              if ( $label != $i18n )
                unset($submenus[$label]);
            }
            sfConfig::set('project_menu_'.$menu, array_merge(sfConfig::get('project_menu_'.$menu, array()), $submenus));
*/
?>
<?php $extras = sfConfig::get('project_menu_'.$name, array()) ?>
<?php if ( count($extras) > 0 ): ?>
  <li class="spaced"></li>
<?php endif ?>
<?php foreach ( $extras as $label => $props ): ?>
  <?php if ( !isset($props['credential']) || isset($props['credential']) && $sf_user->hasCredential($props['credential']) ): ?>
  <li <?php if ( isset($props['extra_properties']) && is_array($props['extra_properties']) ) foreach ( $props['extra_properties'] as $name => $value ): ?>
    <?php echo $name ?>="<?php echo $value ?>"
    <?php endforeach ?>
  >
    <a href="<?php echo $props['url'] ?>"
       <?php if ( isset($props['target']) ) echo 'target="'.$props['target'].'"'; ?>
    ><?php echo $label ?></a>
  </li>
  <?php endif ?>
<?php endforeach ?>
