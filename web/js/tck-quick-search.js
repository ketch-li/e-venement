  $(document).ready(function(){
    $('#li-direct-access a.fg-button').click(function(){
      // current day by default
      var start = new Date();
      start = new Date(start.getFullYear(), start.getMonth(), start.getDate());
      var stop = new Date(start.valueOf()+(24*60*60*1000));
      switch ( $(this).attr('href') ) {
      case '#month':
        stop = new Date(start.valueOf()+(31*24*60*60*1000));
        break;
      case '#week':
        stop = new Date(start.valueOf()+(7*24*60*60*1000));
        break;
      }
      $('#sf_admin_filter [name="order_filters[manifestation_happens_at][from][day]"],   #sf_admin_filter [name="event_filters[dates_range][from][day]"]').val(start.getDate());
      $('#sf_admin_filter [name="order_filters[manifestation_happens_at][from][month]"], #sf_admin_filter [name="event_filters[dates_range][from][month]"]').val(start.getMonth()+1);
      $('#sf_admin_filter [name="order_filters[manifestation_happens_at][from][year]"],  #sf_admin_filter [name="event_filters[dates_range][from][year]"]').val(start.getFullYear());
      $('#sf_admin_filter [name="order_filters[manifestation_happens_at][to][day]"],     #sf_admin_filter [name="event_filters[dates_range][to][day]"]').val(stop.getDate());
      $('#sf_admin_filter [name="order_filters[manifestation_happens_at][to][month]"],   #sf_admin_filter [name="event_filters[dates_range][to][month]"]').val(stop.getMonth()+1);
      $('#sf_admin_filter [name="order_filters[manifestation_happens_at][to][year]"],    #sf_admin_filter [name="event_filters[dates_range][to][year]"]').val(stop.getFullYear());
      $('#sf_admin_filter form').submit();
      return false;
    });
  });
