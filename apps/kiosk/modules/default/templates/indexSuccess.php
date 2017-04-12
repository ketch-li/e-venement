<?php use_helper('I18N', 'CrossAppLink') ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<?php use_stylesheet('material.min.css') ?>
<?php use_stylesheet('kiosk/waves.css') ?>
<?php use_stylesheet('kiosk/animate.css') ?>

<?php use_stylesheet('kiosk/kiosk.css') ?>
<?php use_stylesheet('kiosk/toastr.min.css') ?>

<?php use_javascript('jquery') ?>
<?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery-ui.custom.min.js') ?>
<?php use_javascript('/js/kiosk/toastr.min.js') ?>
<?php use_javascript('/js/kiosk/waves.js') ?>
<?php use_javascript('/js/handlebars/handlebars-v4.0.5.js') ?>
<?php use_javascript('/js/material/material.min.js') ?>
<?php use_javascript('/js/kiosk/kiosk.js') ?>
<?php use_stylesheet('/private/kiosk.css') ?>
<?php use_javascript('/private/kiosk.js') ?>

<div id="app" class="app-layout mdl-layout mdl-js-layout mdl-layout--fixed-header">
	<header class="app-header mdl-layout__header mdl-color--blue-grey-800">
		<div class="mdl-layout__header-row">
			<span class="mdl-layout-title"><img src="images/logo-evenement-small.png" alt="logo"/></span>
			<div class="mdl-layout-spacer"></div>
		</div>
	</header>
	<!-- <div class="app-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">
		<header class="app-drawer-header">
			<div class="app-logo-dropdown">
				<span>hello@example.com</span>
				<div class="mdl-layout-spacer"></div>
				<button id="accbtn" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
					<i class="material-icons" role="presentation">arrow_drop_down</i>
					<span class="visuallyhidden">Accounts</span>
				</button>
				<ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="accbtn">
					<li class="mdl-menu__item">hello@example.com</li>
					<li class="mdl-menu__item">info@example.com</li>
					<li class="mdl-menu__item"><i class="material-icons">add</i>Add another account...</li>
				</ul>
			</div>
		</header>
		<nav class="app-navigation mdl-navigation mdl-color--blue-grey-800">
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">home</i>Home</a>
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">inbox</i>Inbox</a>
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">delete</i>Trash</a>
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">report</i>Spam</a>
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">forum</i>Forums</a>
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">flag</i>Updates</a>
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">local_offer</i>Promos</a>
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">shopping_cart</i>Purchases</a>
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">people</i>Social</a>
			<div class="mdl-layout-spacer"></div>
			<a class="mdl-navigation__link" href=""><i class="mdl-color-text--blue-grey-400 material-icons" role="presentation">help_outline</i><span>Help</span></a>
		</nav>
	</div> -->
	<main id="content" class="mdl-layout__content mdl-color--blue-grey-800">
		<!-- loader -->
		<div class="mdl-spinner mdl-js-spinner is-active" id="spinner"></div>
		<!-- back fab -->
		<button id="back-fab" class="mdl-button mdl-js-button mdl-button--fab mdl-color--light-blue-300 waves-effect">
  			<i class="material-icons light">keyboard_backspace</i>
		</button>
		<!-- info fab -->
		<button id="access-fab" class="mdl-button mdl-js-button mdl-button--fab mdl-color--light-blue-300 waves-effect">
  			<i class="material-icons light">accessible</i>
		</button>
		<div id="info-panel" class="mdl-card__supporting-text mdl-shadow--6dp mdl-color--light-blue-300">
			<p>
				<a href="http://www.e-venement.org/">e-venement</a>
				<span>la billetterie informatique, libre et open source - © 2006-2016</span>
				<a href="http://www.libre-informatique.fr/">Libre Informatique</a>
				<br>
				<span>Publié sous licence</span>
				<a href="http://www.gnu.org/licenses/gpl.html">GNU/GPL</a>
				<span>- Renforcé par</span>
				<a href="http://www.symfony-project.org/">Symfony</a>
				,
				<a href="http://www.php.net/">PHP</a>
				,
				<a href="http://www.postgresql.org/">PostgreSQL</a>
			</p>
		</div>
		<!-- Snackbar -->
		<div id="snackbar" class="mdl-js-snackbar mdl-snackbar">
  			<div class="mdl-snackbar__text"></div>
  			<button class="mdl-snackbar__action" type="button"></button>
		</div>
		<!-- menu panel -->
		<div id="product-menu">
			<ul id="product-menu-items" class="flex-list"></ul>
		</div>
		<!-- product details panel -->
		<div id="product-details-card" class="mdl-card mdl-shadow--2dp"></div>
		<!-- products list -->
		<div id="products">
			<ul id="products-list" class="flex-list"></ul>
		</div>
		<!-- cart panel -->
		<div id="cart" class="mdl-color--blue-grey-600">
			<!-- lines -->
			<ul id="cart-lines"></ul>
			<!-- total -->
			<div id="cart-total" class="mdl-color--blue-grey-800">
				<span id="cart-total-label"><?php echo __('TOTAL') . ': ' ?></span>
				<span id="cart-total-value"></span>
			</div>
			<!-- confirm button -->
			<div id="cart-confirm" class="">
				<button id="confirm-btn" class="mdl-button mdl-js-button mdl-button--raised mdl-color--teal-600 waves-effect">
				<span id="confirm-btn-wrapper">
					<i class="material-icons light">check</i><?php echo __('Valider') ?>
					</button>
				</span>
			</div>
		</div>	
	</main>
</div>

<!-- JS DATA -->
<div class="js-data" id="kiosk-urls"
  data-get-new-transaction="<?php echo cross_app_url_for('tck', 'transaction/newJson') ?>"
  data-get-csrf="<?php echo cross_app_url_for('tck', 'transaction/getCSRFToken') ?>"
  data-complete-transaction="<?php echo cross_app_url_for('tck', 'transaction/complete?id=-666') ?>"
  data-get-manifestations="<?php echo cross_app_url_for('tck', 'transaction/getManifestations?simplified=1') ?>"
  data-get-store="<?php echo cross_app_url_for('tck', 'transaction/getStore?simplified=1') ?>"
  data-get-museum="<?php echo cross_app_url_for('tck', 'transaction/getPeriods?simplified=1') ?>"
></div>

<!-- JS I18N -->
<div class="js-i18n" data-source="manifestations" data-target="<?php echo kioskConfiguration::getText('app_texts_menu_manifestation', 'Manifestations') ?>"></div>
<div class="js-i18n" data-source="museum" data-target="<?php echo kioskConfiguration::getText('app_texts_menu_museum', 'Museum') ?>"></div>
<div class="js-i18n" data-source="store" data-target="<?php echo kioskConfiguration::getText('app_texts_menu_store', 'Store') ?>"></div>

<!-- HANDLEBARS TEMPLATES -->

	<!-- menu item -->
<script id="menu-item-template" type="text/x-handlebars-template" data-template-type="menuItem">
	<li class="menu-item" data-type="{{ type }}">
		<div id="" class="menu-item-card mdl-card mdl-shadow--2dp waves-effect">
  			<div class="mdl-card__title mdl-card--expand" style="background-color: {{ color }};">
    			{{ name }}
    		</div>
  		</div>
  	</li>
</script>

	<!-- manif card -->
<script id="manif-card-template" type="text/x-handlebars-template"  data-template-type="productCard" data-product-type="manifestations">
<li class="product" data-type="{{ type }}" data-id="{{ id }}"> 
	<div class="manif-card mdl-card mdl-shadow--2dp waves-effect" id="{{ id }}">
		<div class="mdl-card__title manif-title" style="{{ background }};">
			<p class="mdl-card__title-text manif-name">{{ name }}</p>
		{{#unless museum}}
    		<p class="mdl-card__title-text manif-happens_at"><i class="material-icons" role="presentation">access_time</i>{{ start }}</p>
  		{{/unless}}
			<p class="mdl-card__title-text manif-location"><i class="material-icons" role="presentation">location_on</i>{{ location }}</p>
		</div>
		<div class="mdl-card__supporting-text manif-description">
			{{{ description }}}
		</div>
	</div>
</li>
</script>

<!-- store card -->
<script id="store-card-template" type="text/x-handlebars-template"  data-template-type="productCard" data-product-type="store">
<li class="product" data-type="{{ type }}" data-id="{{ id }}"> 
	<div class="manif-card mdl-card mdl-shadow--2dp waves-effect" id="{{ id }}">
		<div class="mdl-card__title manif-title" style="{{ background }};">
			<p class="mdl-card__title-text manif-name">{{ name }}</p>
			<!-- <p class="mdl-card__title-text manif-happens_at"><i class="material-icons" role="presentation">access_time</i>{{ start }}</p>
			<p class="mdl-card__title-text manif-location"><i class="material-icons" role="presentation">location_on</i>{{ location }}</p> -->
		</div>
		<div class="mdl-card__supporting-text manif-description">
			{{{ description }}}
		</div>
	</div>
</li>
</script>

	<!-- manif details -->
<script id="product-details-template" type="text/x-handlebars-template" data-template-type="productDetails">
	<div id="product-background" style="{{ background }};">
		<div class="mdl-card__title"></div>
		<div id="details-content" class="mdl-card__supporting-text">
			<div id="product-details">
	    		<div id="details-name">{{ name }}</div>
	    		<div id="details-description">{{{ description }}}</div>
	    		<div id="details-time">
		    		<span>
		    			<i class="material-icons" role="presentation">access_time</i>
		    			<span class="mdl-color-text--pink">{{ start }} - {{ end }}</span>
		    		</span>
		    		<span>
		    			<i class="material-icons" role="presentation">location_on</i>
		    			<span>{{ location }}</span>
		    		</span>
		    	</div>
	    	</div>
		</div>
		<ul id="declinations" class="flex-list"></ul>
		<p id="declination-name"></p>
		<ul id="prices" class="flex-list">
			<li>
				<button id="declination-back" class="mdl-button mdl-js-button">
					<i class="material-icons">keyboard_backspace</i>
				</button>
			</li>
		</ul> 
	</div>
</script>

	<!-- declination card -->
<script id="declination-card-template" type="text/x-handlebars-template" data-template-type="declinationCard">
	<li class="declination">
		<div id="{{ id }}" class="declination-card mdl-card mdl-shadow--2dp waves-effect">
			<div class="mdl-card__title mdl-card--expand" style="background-color: {{ color }};">
				{{ name }}
			</div>
		</div>
	</li>
</script>

	<!-- price card -->
<script id="price-card-template" type="text/x-handlebars-template" data-template-type="priceCard">
	<li class="price">
		<div id="{{ id }}" class="price-card-square mdl-card mdl-shadow--2dp waves-effect">
			<div class="mdl-card__title mdl-card--expand" style="background-color: {{ color }};">
				{{ name }}
			</div>
		</div>
	</li>
</script>

	<!-- cart line -->
<script id="cart-line-template" type="text/x-handlebars-template" data-template-type="cartLine">
	<li class="cart-line mdl-color--blue-grey-800" id="{{ id }}" style="border-right: 5px solid {{ color }};">
		<div class="line-controls">
			<button class="remove-item line-control mdl-button mdl-js-button mdl-button--fab mdl-button--colored">
	  			<i class="material-icons light">remove</i>
			</button>
			<button class="add-item line-control mdl-button mdl-js-button mdl-button--fab mdl-color--pink-300">
	  			<i class="material-icons light">add</i>
			</button>
	    </div>
	    <div class="line-details">
			<p class="line-main">
				<span class="line-qty">{{ qty }}</span>
				<span class="line-multiplier"> x </span>
				<span class="line-name">{{ name }}</span>
			<p>
			<p class="line-second">
				<span class="line-price">{{ price.name }} ({{ value }})</span>
			</p>
		</div>
		<div class="line-value">
			<span class="line-total">{{ total }}</span>
	    </div>
  	</li>
</script>