<ul id="social-networks">
    <li data-social="facebook"><a href="#" title="share this on Facebook"><span>Facebook</span></a></li>
  <li data-social="twitter"><a href="#" title="share this on Twitter"><span>Twitter/<span></a></li>
  <li data-social="googleplus"><a href="#" title="share this on Google+"><span>Google+</span></a></li>
</li>

<script>
$(document).ready(function(){
  var social_networks = <?php echo json_encode(sfConfig::get('app_social_media_networks', array()))  ?>;
  console.log(social_networks);


  var url = window.location.href;
  var twitter_text = social_networks.twitter && social_networks.twitter.text ? social_networks.twitter.text : '';
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
});
</script>