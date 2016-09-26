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
<?php use_javascript('event') ?>

<?php $type = isset($type) ? $type : 'event' ?>

<div class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <a class="fg-button ui-state-default fg-button-icon-left list" href="<?php echo $url = url_for('manifestation/goToListWith'.ucfirst($type).'?id='.$form->getObject()->id) ?>">
    <span class="ui-icon ui-icon-disk"></span>
    <?php echo __('Get list') ?>
  </a>
  <div class="manifestation_list">
  </div>
  <?php $url = url_for('manifestation/'.$type.'List?id='.$form->getObject()->id) ?>
  <script type="text/javascript">var manifestation_list_url = '<?php echo $url ?>';</script>
</div>
