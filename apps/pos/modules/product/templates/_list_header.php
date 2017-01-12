<?php include_partial('global/list_header') ?>

<script>
  // Placeholder for quick search input
  $(document).ready(function(){
    $('#list-integrated-search input[name=s]').attr('placeholder', '<?php echo __("Declination code"); ?>')
  });

  // Redirect to product/declination edit page if the quick search returns only one result
  if ( window.integrated_search_end === undefined )
    window.integrated_search_end = [];
  window.integrated_search_end.push(function(){
    var edit_links = $('.sf_admin_list table .sf_admin_action_edit a');
    if ( edit_links.length == 1 )
      window.location = edit_links.eq(0).attr('href')
        + '?scrolltocode='
        + $('#list-integrated-search input[name=s]').val().trim()
        + '#sf_fieldset_declinations';
  });
</script>
