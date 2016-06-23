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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_helper('I18N', 'Date', 'CrossAppLink') ?>
<?php include_partial('default/assets') ?>

<div class="about-home">
  <?php include_partial('global/about') ?>
</div>

<div id="sf_admin_container">
  <?php include_partial('global/flashes') ?>

  <div id="sf_admin_content">
  <div class="welcome ui-grid-table ui-widget ui-corner-all ui-helper-reset ui-helper-clearfix">
    <div class="ui-widget-content ui-corner-all" id="error">
      <div class="ui-widget-header ui-corner-all fg-toolbar">
        <h2><?php echo __('Exception!') ?></h2>
      </div>
      <p><?php echo __('A software malfunction occured') ?></p>
      <p><?php echo __('Possible causes:') ?></p>
      <ul>
        <li><p><?php echo __('You tried to delete an element that had ties to other parts of the software (eg: delete a user that has tickets).') ?></p></li>
        <li><p><?php echo __('You tried to add an element that already exists (eg: create a product with the same code as an existing one).') ?></p></li>
      </ul>
      <?php $client = sfConfig::get('project_about_client', array('url' => '')) ?>
      <p><?php echo __('Please try again. If the problem persists, contact your administrator at:') ?></p> 
      <p><?php echo link_to($client['url'], $client['url']); ?></p>
      <p>
        <?php echo link_to(__('&#8617'), $sf_request->getReferer(), array('id' => 'goback', 'height' => '300', 'width' => '300')) ?>
        <?php echo link_to(__('&#x2302'), '@homepage', array('id' => 'home')) ?>
      </p>
    </div>

    <div class="ui-widget-content ui-corner-all" id="company">
      <div class="ui-widget-header ui-corner-all fg-toolbar">
        <h2><?php echo __('Libre Informatique', array(), 'messages') ?></h2>
      </div>
      <?php include_partial('global/libre-informatique') ?>
    </div>
  </div>
  </div>

  <div id="sf_admin_footer">
    <?php include_partial('default/list_footer') ?>
  </div>

  <?php include_partial('default/themeswitcher') ?>
  <?php if(! $sf_request->hasParameter('debug')) use_javascript('goBack.js') ?>
</div>

