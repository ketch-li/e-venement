<h1><?php echo __('Terms & Conditions') ?></h1>
<object class="pdf-cgv" style="width:100%;height:700px;margin:20px 0px;" data="<?php echo $form->url;?>" type="application/pdf">
  <param name="filename" value="<?php echo $form->url;?>" /> 
  <a href="<?php echo $form->url;?>" title="<?php echo __('Terms & Conditions') ?>"><?php echo __('Download PDF file') ?></a>
</object>