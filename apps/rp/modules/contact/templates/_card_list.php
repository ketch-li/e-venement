<script type="text/javascript"><!--
  if ( LI == undefined )
    var LI = {};
  
  $(document).ready(function(){
    LI.get_member_card_index(1);
  });
  
  LI.extractParam = function(url, param)
  {
    var result = null;
    var paramstr = url.split('?');
    var params = paramstr[1].split('&');
    
    for(var i=0;i<params.length;i++) {
      var value = params[i].split('=');
      if (value[0] == param) {
        result = value[1];
        break;
      }
    }
    
    return result;
  }
  
  LI.get_member_card_index = function(page_param)
  {
    $.get('<?php echo url_for('member_card/index?contact_id='.$contact->id.'&page=') ?>' + page_param, function(data) {
      data = $.parseHTML(data);
      
      if ( $('#member-cards .list > table').length > 0 )
        $('#member-cards .list > table').replaceWith($(data).find('.sf_admin_list > table'));
      else
        $(data).find('.sf_admin_list > table')
          .appendTo('#member-cards .list');
      
      $('#member-cards .list').addClass('sf_admin_list');
      $('#member-cards .list > table').find('caption').remove();
      $('#member-cards .list > table a').unbind('click').click(function() {

        var page_param = LI.extractParam($(this).attr('href'), 'page');
        LI.get_member_card_index(page_param);
        
        return false;
      });
      
      $('#member-cards .list > table > tbody a').unbind();
      
      if ( LI.get_member_card_index_callbacks != undefined ) {
        $.each(LI.get_member_card_index_callbacks, function(i, fct){ fct(); });
      }
    });
  }
  
--></script>
