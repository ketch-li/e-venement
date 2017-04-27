
  LI.del_file = function(e, plnk) {
    var lnk = $(plnk);
    if ( confirm(confirm_msg + ' ' + lnk.attr('data-name')) ) {
      $.ajax({
        url: manifestation_del_url + '?id=' + lnk.attr('data-id') + '&mid=' + manifestation_id,
        type: 'GET',
        success: function(response) {
          if ( response['error'] == 'success' ) {
            lnk.closest('li').remove();
          }
        }
      });
    }
    e.preventDefault();
  }

  $(document).ready(function() {
    $('.manifestation_files .sf_admin_form_field_file_del').click(function(e) {
      LI.del_file(e, this);
    });
    
    $(document).on('change', '#manifestation_file', (function() {
      var data = new FormData();
      data.append('file', $('#manifestation_file').prop('files')[0]);
      data.append('id', manifestation_id);
      $.ajax({
        url: manifestation_file_url,
        type: 'POST',
        contentType: false,
        processData: false,
        data: data,
        success: function(response) {
          if ( response['error'] == 'success' ) {
            var li = $('<li></li>').appendTo('.manifestation_files');
            $('<a></a>')
              .attr('href', response['url'])
              .attr('target', '_blank')
              .text(response['name'])
              .appendTo(li);
            $('#template_lnk a')
              .clone()
              .attr('data-name', response['name'])
              .attr('data-id', response['id'])
              .attr('title', del_msg + response['name'])
              .appendTo(li).click(function(e) {
                LI.del_file(e, this);
              });
          } else {
            console.log(response['error']);
          }
          $('#manifestation_file').val('');
        }
      });
    }));
  });
