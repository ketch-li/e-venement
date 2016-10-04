<?php $phones = array() ?>
<?php foreach ( $contact->Professionals as $pro ): ?>
<div class="pro pro-<?php echo $pro->id ?>">
  <?php foreach ( $pro->Organism->Phonenumbers as $phone ): ?>
  <?php if ( !in_array($phones[$phone->number]) ): ?>
    <?php $phones[] = $phones->number ?>
    <span title="<?php echo $phone->name ?>"><?php echo $phone->number ?></span>,
  <?php endif ?>
  <?php endforeach ?>
</div>
<?php endforeach ?>
