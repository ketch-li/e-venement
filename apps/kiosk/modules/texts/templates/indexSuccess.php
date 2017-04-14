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
<?php include_partial('global/assets') ?>
<div class="sf_admin_form ui-widget-content ui-corner-all sf_admin_edit full-lines" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Kiosk app texts') ?></h1>
  </div>
  <?php include_partial('form_header', array('form' => $form)); ?>
  <?php include_partial('global/flashes') ?>
  <form action="<?php echo url_for('texts/update') ?>" method="post" class="data" enctype="multipart/form-data">
    <?php include_partial('global/option_form',array('form' => $form,)); ?>
    <?php include_partial('form_save'); ?>
  </form>
</div>