<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php if ( sfConfig::get('project_museums_enable', false) ): ?>
    <?php
      if ( $sf_user->hasCredential('event-event')
        || $sf_user->hasCredential('event-seated-plan')
        || $sf_user->hasCredential('event-calendar-gui')
        || $sf_user->hasCredential('event-location') ): ?>
      <li class="menu-museum">
        <ul class="second">
          <?php if ( $sf_user->hasCredential('event-event') ): ?>
          <li><a href="<?php echo cross_app_url_for('museum','event') ?>"><?php echo __('Visits',array(),'menu') ?></a></li>
          <?php endif ?>
          <?php /*
          <?php if ( $sf_user->hasCredential('event-calendar-gui') ): ?>
          <li><a href="<?php echo cross_app_url_for('museum','calendar') ?>"><?php echo __('Agenda',array(),'menu') ?></a></li>
          <?php endif ?>
          */ ?>
          <?php if ( $sf_user->hasCredential('event-location') ): ?>
          <li class="spaced"></li>
          <li><a href="<?php echo cross_app_url_for('museum','location') ?>"><?php echo __('Locations',array(),'menu') ?></a></li>
          <li><a href="<?php echo cross_app_url_for('museum','resource') ?>"><?php echo __('Resources',array(),'menu') ?></a></li>
          <?php endif ?>
          <li class="spaced"></li>
          <?php if ( $sf_user->hasCredential('event-manif') ): ?>
          <li><a href="<?php echo cross_app_url_for('museum','manifestation') ?>"><?php echo __('Periods',array(),'menu') ?></a></li>
          <?php if ( $sf_user->hasCredential(array('event-location')) ): ?>
          <li class="menu-event-use-conflicts"><a href="<?php echo cross_app_url_for('museum','conflict/index') ?>"><?php echo __('Use conflicts',array(),'menu') ?></a></li>
          <li><a class="menu-event-booking-to-confirm" href="<?php echo cross_app_url_for('museum','pending/index') ?>"><?php echo __('Bookings to confirm', null, 'menu') ?></a></li>
          <?php endif ?>
          <?php endif ?>
          <?php include_partial('global/menu_extra', array('name' => 'events')) ?>
       </ul>
        <span class="title"><?php echo __('Museum',array(),'menu') ?></span>
      </li>
    <?php endif ?>
<?php endif ?>
