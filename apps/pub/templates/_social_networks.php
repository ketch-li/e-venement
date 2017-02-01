<ul id="social-networks">
  <li data-social="twitter">
    <a href="#" title="<?php echo __('Share this on %network%', array('%network%'=>'Twitter')) ?>"></a>
    <span class="twitter-count"></span>
  </li>
  <li data-social="facebook">
    <a href="#" title="<?php echo __('Share this on %network%', array('%network%'=>'Facebook')) ?>"></a>
    <span class="facebook-count"></span>
  </li>
  <li data-social="googleplus">
    <a href="#" title="<?php echo __('Share this on %network%', array('%network%'=>'Google+')) ?>"></a>
    <span class="googleplus-count"></span>
  </li>
</ul>

<script type="text/javascript">
$(document).ready(function(){
  var social_networks = <?php echo json_encode(sfConfig::get('app_social_media_networks', array()))  ?>;
  var url = window.location.href;
  var twitter_text = social_networks.twitter && social_networks.twitter.text ? social_networks.twitter.text : document.title;
  var default_urls = {
    'facebook': 'https://www.facebook.com/sharer/sharer.php?u=' + url,
    'twitter': 'https://twitter.com/share?text=' + encodeURIComponent(twitter_text),
    'googleplus': 'https://plus.google.com/share?url=' + url
  };

  $('#social-networks li').each(function(){
    var name = $(this).attr('data-social') || 'none';
    if ( !social_networks.hasOwnProperty(name) )
      return true;
    $(this).addClass('active').find('a').click(function(){
      var width      = 500;
      var height     = 400;
      var leftOffset = ($(window).width() - width) / 2;
      var topOffset  = ($(window).height() - height) / 2;
      var url        = default_urls[name];
      var opts       = 'width=' + width + ',height=' + height + ',top=' + topOffset + ',left=' + leftOffset;
      window.open(url, name, opts);
      return false;
    });
  });

  var updateCounters = function(data) {
    for(network in data)
    if ( data[network] >= 0 ) {
      $('#social-networks li[data-social='+network+'] a').addClass('count');
      $('#social-networks .'+network+'-count').show().html(data[network]);
    }

  };

  $.ajax({
    url: "<?php echo url_for('social_networks/count') ?>",
    data: {url: window.location.href},
    success: updateCounters,
    dataType: "json"
  });
});
</script>