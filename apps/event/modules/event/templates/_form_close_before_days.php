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
<?php $event = $form->getObject(); ?>
<div>
  <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_close_before">
    <label for="event_close_before"><?php echo __('Online sales close before'); ?></label>
    <div class="label ui-helper-clearfix"></div>
    <div class="widget ">
      <input type="text" name="event[close_before]" value="<?php echo $event->close_before; ?>" id="event_close_before" />
    </div>
  </div>

  <div class="sf_admin_form_row sf_admin_text sf_admin_form_field_close_before_days">
    <label for="close_before_days"><?php echo __('days') ?></label>
    <div class="label ui-helper-clearfix"></div>
    <input type="number" name="close_before_days" value="0" min="0" />
    <script type="text/javascript"><!--
    
      $(document).ready(function() {
        $('#event_close_before').change(function() {
          var hrs_close = parseInt($(this).val().replace(/:\d\d$/, ''))/24;
          $('.sf_admin_form_field_close_before_days input').val(Math.floor(hrs_close));
        });
        
        $('.sf_admin_form_field_close_before_days input').change(function(){
          var hrs_close = parseInt($('#event_close_before').val().replace(/:\d\d$/, ''))%24 + $(this).val()*24;
          var mins_close = $('#event_close_before').val().replace(/^-{0,1}\d+:/, '');
          $('#event_close_before').val(hrs_close+':'+mins_close);
        });
        
      });
  --></script>
  </div>

</div>