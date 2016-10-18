<?php if ($displaySwitcher): ?>
  <div id="layout-switcher">
    <a href="#">[X]</a>
    <select>
      <option></option>
      <?php foreach($layouts as $l): ?>
        <option value="<?php echo $l ?>" <?php echo $l == $layout ? 'selected' : '' ?> >
          <?php echo $l ?>
        </option>
      <?php endforeach; ?>
    </select>
    <?php if ($layout != 'old'): ?>
      <select>
        <option></option>
        <?php foreach($themes as $t): ?>
          <option value="<?php echo $t ?>" <?php echo $t == $theme ? 'selected' : '' ?> >
            <?php echo $t ?>
          </option>
        <?php endforeach; ?>
      </select>
    <?php endif ?>
    <a>reset</a>
  </div>

  <script>
    $(function(){
      // clean url (remove any _theme or _layout query paremeters)
      var search = window.location.search;
      var parts = [];
      search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str,key,value) {
        if ( key != '_layout' && key != '_theme' )
          parts.push(key + '=' + value);
      });
      search = '?' + parts.join('&');
      var href = window.location.origin + window.location.pathname + search;

      $("#layout-switcher a").eq(0).click(function(){
        $("#layout-switcher").hide();
        return false;
      });
      $("#layout-switcher a").eq(1).attr('href', href + '&_layout=reset');
      $("#layout-switcher select").eq(0).change(function(){
        var layout = $(this).find('option:selected').val();
        if (layout)
          window.location = href + '&_layout=' + layout;
      });
      $("#layout-switcher select").eq(1).change(function(){
        var theme = $(this).find('option:selected').val();
        if (theme)
          window.location = href + '&_theme=' + theme;
      });
    });
  </script>
<?php endif ?>

