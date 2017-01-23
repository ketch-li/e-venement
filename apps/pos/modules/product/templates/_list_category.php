<?php if ( $product->product_category_id ): ?>
<span style="background-color: <?php echo $product->Category->Color ?>;">
  <?php echo $sf_user->hasCredential('pos-product-category') ? link_to($product->Category, 'category/edit?id='.$product->product_category_id) : $product->Category ?>
</span>
<?php endif ?>
