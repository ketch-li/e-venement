$(document).ready(function(){
  $('.sf_admin_list_td_dates').each(function(){
    // constructing theorical content
    var options = [];
    $(this).find('li a').each(function(){
      options.push(this);
    });
    
    // creating the select widget and its behavior
    var select = $('<select></select>')
      .prepend('<option></option>')
      .prop('name', 'date')
      .change(function(){
        var option = $(this).find('option[value="'+$(this).val()+'"]');
        if ( option.length == 0 || !option.attr('data-url') )
          return;
        window.location = option.attr('data-url');
      })
      .appendTo(this)
    ;
    
    // adding content in the select based on the theorical content
    $.each(options, function(i, anchor){
      $('<option></option>')
        .val($(anchor).text())
        .text($(anchor).text())
        .attr('data-url', $(anchor).prop('href'))
        .appendTo(select)
      ;
    });
  });
});
