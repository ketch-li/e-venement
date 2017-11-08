
<?php //use_stylesheet('print-tickets.default.css', '', array('media' => 'all')) ?>
<?php //if ( sfConfig::has('app_tickets_control_left') ) use_stylesheet('print-tickets.controlleft.css', '', array('media' => 'all')) ?>
<?php //if ( sfConfig::get('app_tickets_specimen',false) ) use_stylesheet('print-tickets-specimen', '', array('media' => 'all')) ?>

<!--
<input id="update_ticket" value="Actualiser" title="Actualiser l'affichage du billet" class="ui-widget-content ui-state-default ui-corner-all ui-widget fg-button" type="button">
-->


<script type="text/javascript">

var newStyle = null;
var frame = null;
var tck_classes = {};

$(document).ready(function(){
  
  $('#sf_fieldset_template').append('<iframe id="mc_sample" src="<?php echo url_for('contact/cardSample'); ?>"></iframe>')
  $('iframe#mc_sample').attr('src', '<?php echo url_for('contact/cardSample'); ?>');
    
  $('iframe#mc_sample').load(function() {
    frame = $(this)[0].contentDocument.body;

    $('iframe#mc_sample').contents().find('.page div, .page .member_card div, .page .member_card div p, .page .member_card div p span').attrchange({
      trackValues: true,
      callback: function(e) {
        console.log(e);
        
        var tck_path = [];
        var obj = $(this);
        var obj_class = obj.attr('class').split(' ')[0];
        
        while(obj_class != 'page') {
          console.log(obj_class);
          tck_path.unshift(obj_class);
          obj = obj.parent();
          obj_class = obj.attr('class').split(' ')[0];
        }
        
        var tck_parent = tck_path.join(':');
        //var tck_class = $(this).attr('class').split(' ')[0];
        var tck_style = $(this).attr('style');
        var res = '';
        
        tck_classes[tck_parent] = tck_style;
        
        $.each(tck_classes, function(i, v) {
          res += '.' + i.replace(/:/g, ' .') + ' { ' + v + ' }\n'
        });
        
        $('#custom_card_css').val(res);
      }
    });

    var styles = $('#custom_card_css').val().split('\n');
    $.each(styles, function(){
      var str = this.toString().trim();
      if ( str ) {
        var reg = /(\.(.*) \.(.*)) \{ (.*) \}/gi;
        var group = reg.exec(str);
        tck_classes[group[2]+':'+group[3]] = group[4];
        
        $.each(group[4].split(';'), function() {
          var prop = this.toString().trim();
          var cssp = prop.split(':');

          $('iframe#mc_sample').contents().find(group[1]).css(cssp[0], cssp[1]);
        });
      }
    });
  });
});

</script>


<?php

?>
