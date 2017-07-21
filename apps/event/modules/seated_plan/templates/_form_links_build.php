<?php include_partial(
  'form_links_field',
  array(
    'name'  => 'contiguous',
    'label' => __('Contiguous?'),
    'type'  => 'checkbox',
    'value' => 'yes',
    'size'  => false,
    'attributes' => array('checked' => 'checked'),
  )
) ?>
<?php include_partial(
  'form_links_field',
  array(
    'name'  => 'Additive',
    'label' => __('Additive?'),
    'type'  => 'checkbox',
    'value' => 'yes',
    'size'  => false,
    'attributes' => array(),
  )
) ?>
<?php include_partial(
  'form_links_field',
  array(
    'name'  => 'format',
    'label' => __('Format'),
    'value' => "%row%%num%",
    'size'  => '20',
    'with_submit' => true,
    'submit_label' => __('Build'),
    'helper' => __('%row% for alphabetic row (safe), %rown% for numeric row, %rowm% for mixte row and %num% for numeric seats'),
  )
) ?>
