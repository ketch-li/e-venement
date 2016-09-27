<?php use_helper('I18N') ?>

    <div id="periodicity_repeat" class="ui-corner-all ui-widget-content">
      <h2><?php echo __('Repeat every') ?>:</h2>
      <ul id="time">
        <?php foreach ( array(
          'minutes' => __('minutes'),
          'hours'   => __('hours'),
          'days'    => __('days'),
          'weeks'   => __('weeks'),
          'month'   => __('month'),
          'years'   => __('years'),
        ) as $fieldName => $label ): ?>
        <li><input type="text" name="periodicity[repeat][<?php echo $fieldName ?>]" value="0" maxlength="2" size="2" class="number" id="periodicity_<?php echo $fieldName ?>" /> <label for="periodicity_<?php echo $fieldName ?>"><?php echo $label ?></label></li>
        <?php endforeach ?>
      </ul>
      <ul id="days">
        <?php foreach ( array(
          'mondays'    => __('Mondays'),
          'tuesdays'   => __('Tuesdays'),
          'wednesdays' => __('Wednesdays'),
          'thursdays'  => __('Thursdays'),
          'fridays'    => __('Fridays'),
          'saturdays'  => __('Saturdays'),
          'sundays'    => __('Sundays')
        ) as $fieldName => $label ): ?>
        <li class="weekdays"><input type="checkbox" name="periodicity[repeat][weekdays][]" value="<?php echo $fieldName ?>" id="periodicity_<?php echo $fieldName ?>" /> <label for="periodicity_<?php echo $fieldName ?>"><?php echo $label ?></label></li>
        <?php endforeach ?>
      </ul>
    </div>