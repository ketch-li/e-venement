<?php foreach ( $MemberCards as $card ): ?>
  <?php 
    if ( $card->MemberCardType->custom_card_id )
    {
      echo '<style type="text/css">' . sfOutputEscaper::unescape($card->MemberCardType->CustomCard->css) . '</style>';  
    }
  ?>
<div class="page">
<div class="member_card">
  <div class="content card">
    <p class="picture"><?php echo $card->Contact->Picture->getRawValue()->getHtmlTagInline() ?> </p>
    <p class="cardid"><span class="title"><?php echo __('N° mumber card') ?></span><?php echo(' '.$card->id); ?></p>
    <p class="name"><span class="title"><?php echo __('Name') ?></span> <?php echo $card->Contact->name ?></p>
    <p class="firstname"><span class="title"><?php echo __('Firstname') ?></span> <?php echo $card->Contact->firstname ?></p>
    <p class="address"><span class="title"><?php echo __('Address') ?></span><?php echo nl2br(trim($card->Contact->address)) ?></p>
    <p class="city"><?php echo $card->Contact->postalcode.' '.$card->Contact->city ?></p>
    <p class="country"><?php echo $card->Contact->country ?></p>
    <p class="email"><span class="title"><?php echo __('Email') ?></span><?php echo $card->Contact->email ?></p>
    <p class="date"><span class="title"><?php echo __('Expiration') ?></span> <?php echo format_date($card->expire_at) ?></p>
    <p class="barcode">
        <?php
            switch(sfConfig::get('app_cards_id', 'id') )
            {
                case 'qrcode':
                    echo("<img src='data:image/png;base64,".$card->QRcodeBase64PNG."' alt='QRcode #".$card->id."' />");
                break;
                case 'id':
                    echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$card->Contact->getIdBarcoded());
                break;
            }
         ?>
    </p>
    <p class="extra-card"><?php echo nl2br(sfConfig::get('app_cards_extra')) ?></p>
    <p class="seats"><span class="title"><?php echo __('Privileged seat') ?></span> <?php echo nl2br(__($card->privileged_seat_name)) ?></p>
    <p class="status"><span class="title"><?php echo __('Status') ?></span> <?php echo nl2br(__($card->name)) ?></p>
  </div>
  <div class="content archive">
    <p class="cardid"><span class="title"><?php echo __('N° mumber card') ?></span><?php echo(' '.$card->id); ?></p>
    <p class="name"><span class="title"><?php echo __('Name') ?></span><?php echo $card->Contact->name ?></p>
    <p class="firstname"><span class="title"><?php echo __('Firstname') ?></span><?php echo $card->Contact->firstname ?></p>
    <p class="address"><span class="title"><?php echo __('Address') ?></span><?php echo nl2br(trim($card->Contact->address)) ?></p>
    <p class="city"><?php echo $card->Contact->postalcode.' '.$card->Contact->city ?></p>
    <p class="country"><?php echo $card->Contact->country ?></p>
    <p class="email"><span class="title"><?php echo __('Email') ?></span><?php echo $card->Contact->email ?></p>
    <p class="status"><span class="title"><?php echo __('Status') ?></span> <?php echo nl2br(__($card->name)) ?></p>
    <p class="date"><span class="title"><?php echo __('Expiration') ?></span> <?php echo format_date($card->expire_at) ?></p>
    <p class="barcode">
        <?php
            switch(sfConfig::get('app_cards_id', 'id') )
            {
                case 'qrcode':
                    echo("<img src='data:image/png;base64,".$card->QRcodeBase64PNG."' alt='QRcode #".$card->id."' />");
                break;
                case 'id':
                    echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$card->Contact->getIdBarcoded());
                break;
            }
         ?>
    </p>
  </div>
  <div class="content receipt">
    <p class="cardtype"><?php echo(' '.$card->MemberCardType->name); ?></p>
    <p class="librinfo">Imprimé et géré par e-venement www.libre-informatique.fr</p>
    <p class="cardid"><span class="title"><?php echo __('N° mumber card') ?></span><?php echo(' '.$card->id); ?></p>
    <h2><?php echo __('Card receipt') ?></h2>
    <p class="name"><span class="title"><?php echo __('Name') ?></span><?php echo $card->Contact->name ?></p>
    <p class="firstname"><span class="title"><?php echo __('Firstname') ?></span><?php echo $card->Contact->firstname ?></p>
    <p class="address"><span class="title"><?php echo __('Address') ?></span><?php echo nl2br(trim($card->Contact->address)) ?></p>
    <p class="city"><?php echo $card->Contact->postalcode.' '.$card->Contact->city ?></p>
    <p class="country"><?php echo $card->Contact->country ?></p>
    <p class="status"><span class="title"><?php echo __('Status') ?></span> <?php echo nl2br(__($card->name)) ?></p>
    <p class="date"><span class="title"><?php echo __('Expiration date') ?></span> <?php echo format_date($card->expire_at) ?></p>
    <p class="extra-date"><?php echo nl2br(sfConfig::get('app_cards_date_extra')) ?></p>
    <p class="extra-card"><?php echo nl2br(sfConfig::get('app_cards_extra')) ?></p>
    <p class="barcode">
        <?php        
            switch(sfConfig::get('app_cards_id', 'id') )
            {
                case 'qrcode':
                    echo("<img src='data:image/png;base64,".$card->QRcodeBase64PNG."' alt='QRcode #".$card->id."' />");
                break;
                case 'id':
                    echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$card->Contact->getIdBarcoded());
                break;
            }    
         ?>
    </p>    
    <p class="logo">
    <?php if ( sfConfig::get('app_cards_logo', null) ): ?>
      <?php
        $uri = sfConfig::get('sf_web_dir').'/private/'.sfConfig::get('app_cards_logo');
        $b64 = base64_encode(file_get_contents($uri));
      ?>
      <img src="data:image/jpeg;base64,<?php echo $b64 ?>" alt="logo" />      
    <?php endif ?>    
    </p>
  </div>
</div>
</div>
<div class="mc_separator"></div>
<?php endforeach ?>
