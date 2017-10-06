
$(document).ready(function(){

  $('#sf_admin_content_form').submit(function() {
    $('#sf_admin_content_form').attr('target', '_blank');
    location.reload();
  });

});