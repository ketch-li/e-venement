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
      var code = location.search.match(/scrolltocode=([a-zA-Z0-9]+)/);
      if ( code !== null ) {
        code = code[1];
        var nbTextareas = $('textarea').filter(function(){
          return this.id.match(/^product_declinations_\d+_[a-z]+_description/);
        }).length;
        var $scrollElement = $('.sf_admin_form_field_declinations input[type=text]').filter(function(){
          return this.id.match(/^product_declinations_\d+_code/) && $(this).val() == code;
        }).first();
        if ( $scrollElement.length ) {

          // do scroll when all the tinyMCE widgets are ready
          var nbTinyMCE = 0;
          tinyMCE.on('addeditor', function( event ) {
            if ( event.target.activeEditor.id.match(/^product_declinations_\d+_[a-z]+_description/) ) {
              nbTinyMCE += 1;
              if ( nbTinyMCE == nbTextareas ) {
                setTimeout(function(){ $("html, body").animate({ scrollTop: $scrollElement.offset().top - 100 }, 'fast') }, 1000);
              }
            }
          }, true );

        }
      }
    }

    function getScrolltocode() {

    }
  });

</script>
