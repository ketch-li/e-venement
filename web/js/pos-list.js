$(document).ready(function(){
  $('.sf_admin_list_td_list_stocks .data').each(function(){
    LI.posRenderStocks(
      JSON.parse($(this).text()),
      $(this).closest('.sf_admin_list_td_list_stocks').find('.jqplot')
    );
  })
  
  $('.sf_admin_list_td_list_stocks .jqplot').click(function(e){
    if ( e.ctrlKey||e.metaKey )
      $(this).middleclick();
    else
      window.location = $(this).closest('tr').find('.sf_admin_action_edit a').click().prop('href')+'#sf_fieldset_stocks';
  })
  .middleclick(function(e){
    window.open($(this).closest('tr').find('.sf_admin_action_edit a').prop('href')+'#sf_fieldset_stocks');
  });
  
  $('.sf_admin_list_td_list_category span').each(function(){
    var color;
    if ( color = $(this).css('background-color') )
      $(this).closest('td').css('background-color', color);
  });
});
