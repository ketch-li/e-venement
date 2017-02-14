<?php include_partial('global/list_header') ?>
<?php include_partial('list_header_quick_search') ?>
<script type="text/javascript">
  // Placeholder for quick search input
  $(document).ready(function(){
    $('#list-integrated-search input[name=s]').attr('placeholder', '<?php echo __("Contact"); ?>')
  });
</script>
