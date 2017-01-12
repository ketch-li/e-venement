<?php if ( !$sf_user->hasCredential('pos-product-stats') ) return ?>

<?php use_javascript('pos-stocks') ?>

<?php include_partial('global/chart_jqplot', array(
  'id'    => 'declinations',
  'data'  => url_for('product/declinationsTrends?id='.$form->getObject()->id),
  'label' => __('Declinations trends'),
  'name' => $form->getObject()->name,
)) ?>

<?php use_javascript('helper') ?>
<?php use_javascript('/js/jqplot/plugins/jqplot.pieRenderer.js') ?>

<script>
  // Scroll to declination (when coming from the quick search)
  $(document).ready(function(){
    if ( location.hash == '#sf_fieldset_declinations' ) {
      var searchParams = new URLSearchParams(location.search);
      var code = searchParams.get('scrolltocode');
      if ( code !== null ) {
        $('.sf_admin_form_field_declinations input[id^="product_declinations"][id$=_code]').each(function(){
          if ( $(this).attr('value') == code && /^product_declinations_\d_code$/.test($(this).attr('id')) ) {
            var $that = $(this);
            // Hack : we wait 2 seconds before scrolling because TinyMCE is changing offsets after document ready event
            // Hack : we add 100px to the scroll because of the main menu navbar
            setTimeout(function(){ $("html, body").animate({ scrollTop: $that.offset().top - 100 }, 'fast') }, 2000);
            return false;
          }
        });
      }
    }
  });
</script>

