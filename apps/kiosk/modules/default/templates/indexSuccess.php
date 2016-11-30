<?php use_helper('I18N') ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<?php use_stylesheet('material.min.css') ?>
<?php use_stylesheet('kiosk/waves.css') ?>
<?php use_stylesheet('kiosk/kiosk.css') ?>
<?php use_stylesheet('kiosk/toastr.min.css') ?>
<?php use_javascript('jquery') ?>
<?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery-ui.custom.min.js') ?>
<?php use_javascript('/js/kiosk/toastr.min.js') ?>
<?php use_javascript('/js/kiosk/waves.js') ?>
<?php use_javascript('/js/mustache/mustache.min.js') ?>
<?php use_javascript('/js/material/material.min.js') ?>
<?php use_javascript('/js/kiosk/kiosk.js') ?>

<?php use_stylesheet('/private/kiosk.css') ?>
<?php use_javascript('/private/kiosk.js') ?>

<div class="app-layout mdl-layout mdl-js-layout mdl-layout--fixed-header">
	<header class="app-header mdl-layout__header mdl-color--blue-grey-800">
		<div class="mdl-layout__header-row">
			<span class="mdl-layout-title"><img src="images/logo-evenement-small.png" alt="logo"/></span>
			<div class="mdl-layout-spacer"></div>
			<!-- <div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable">
				<label class="mdl-button mdl-js-button mdl-button--icon" for="search" id="search-label">
					<i class="material-icons">search</i>
				</label>
				<div class="mdl-textfield__expandable-holder">
					<input class="mdl-textfield__input" type="text" id="search">
					<label class="mdl-textfield__label mdl" for="search" id="search-bottom-line">Enter your query...</label>
				</div>
			</div>
			<button class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" id="hdrbtn">
				<i class="material-icons">more_vert</i>
			</button>
			<ul class="mdl-menu mdl-js-menu mdl-js-ripple-effect mdl-menu--bottom-right" for="hdrbtn">
				<li class="mdl-menu__item">About</li>
				<li class="mdl-menu__item">Contact</li>
				<li class="mdl-menu__item">Legal information</li>
			</ul> -->
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
		<button id="info-fab" class="mdl-button mdl-js-button mdl-button--fab waves-effect">
  			<i class="material-icons light mdl-color-text--light-blue-300">info_outline</i>
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

<!-- MUSTACHE TEMPLATES -->

	<!-- menu item -->
<script id="menu-item-template" type="x-tmpl-mustache" data-template-type="menuItem">
	<li class="menu-item" data-type="{{ item.name }}">
		<div id="" class="menu-item-card mdl-card mdl-shadow--2dp waves-effect">
  			<div class="mdl-card__title mdl-card--expand" style="background-color: {{ item.color }};">
    			{{ item.name }}
    		</div>
  		</div>
  	</li>
</script>

	<!-- manif card -->
<script id="manif-card-template" type="x-tmpl-mustache"  data-template-type="productCard" data-product-type="manifestations">
<li class="product" data-type="{{ manif.type }}" data-id="{{ manif.id }}"> 
	<div class="manif-card mdl-card mdl-shadow--2dp waves-effect" id="{{ manif.id }}">
		<div class="mdl-card__title manif-title" style="{{ manif.background }};">
			<p class="mdl-card__title-text manif-name">{{ manif.name }}</p>
			<p class="mdl-card__title-text manif-happens_at"><i class="material-icons" role="presentation">access_time</i>{{ manif.start }}</p>
			<p class="mdl-card__title-text manif-location"><i class="material-icons" role="presentation">location_on</i>{{ manif.location }}</p>
		</div>
		<div class="mdl-card__supporting-text manif-description">
			{{{ manif.description }}}
		</div>
	</div>
</li>
</script>

	<!-- manif details -->
<script id="product-details-template" type="x-tmpl-mustache" data-template-type="productDetails">
	<div id="product-background" style="{{ product.background }};">
		<div class="mdl-card__title"></div>
		<div id="details-content" class="mdl-card__supporting-text">
			<div id="product-details">
	    		<div id="details-name">{{ product.name }}</div>
	    		<div id="details-description">{{{ product.description }}}</div>
	    		<div id="details-time">
		    		<span>
		    			<i class="material-icons" role="presentation">access_time</i>
		    			<span class="mdl-color-text--pink">{{ product.start }} - {{ product.end }}</span>
		    		</span>
		    		<span>
		    			<i class="material-icons" role="presentation">location_on</i>
		    			<span>{{ product.location }}</span>
		    		</span>
		    	</div>
	    	</div>
	    	
		</div>
		<ul id="prices" class="flex-list"></ul> 
	</div>
</script>

	<!-- price card -->
<script id="price-card-template" type="x-tmpl-mustache" data-template-type="priceCard">
	<li class="price">
		<div id="{{ price.id }}" class="price-card-square mdl-card mdl-shadow--2dp waves-effect">
  			<div class="mdl-card__title mdl-card--expand" style="background-color: {{ price.color }};">
    			{{ price.name }}
    		</div>
  		</div>
  	</li>
</script>

	<!-- cart line -->
<script id="cart-line-template" type="x-tmpl-mustache" data-template-type="cartLine">
	<li class="cart-line mdl-color--blue-grey-800" id="{{ line.id }}" style="border-right: 5px solid {{ line.price.color }};">
		<button class="remove-item mdl-button mdl-js-button mdl-button--fab mdl-button--colored">
  			<i class="material-icons light">remove</i>
		</button>
		<p class="line-main">
			<span class="line-qty">{{ line.qty }}</span>
			<span class="line-multiplier"> x </span>
			<span class="line-name">{{ line.name }}</span>
		<p>
		<p class="line-second">
			<span class="line-price">{{ line.price.name }} ({{line.price.value}})</span>
		</p>
		<p class="line-third">
			<span class="line-total">{{ line.total }}</span>
		</p>
  	</li>
</script>