$(document).ready(function(){
  $('#sf_admin_actions_button').click(function(){
    setTimeout(function(){ // this is a trick to let jquery-ui do its work first
      $('.sf_admin_action_extract a').unbind('click').click(function(){
        var data = LI.tableToArray($('.sf_admin_list'), ['thead', 'tbody']);
        window.location = URL.createObjectURL(new Blob([LI.arrayToTable(data)], { type: "application/vnd.ms-excel" }));
        return false;
      });
    },500);
  });
});
