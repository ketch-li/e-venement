<?php use_helper('I18N') ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<?php use_stylesheet('material.min.css') ?>
<?php use_stylesheet('kiosk/waves.css') ?>
<?php use_stylesheet('kiosk/kiosk.css') ?>
<?php use_javascript('jquery') ?>
<?php use_javascript('/sfAdminThemejRollerPlugin/js/jquery-ui.custom.min.js') ?>
<?php use_javascript('/js/kiosk/waves.js') ?>
<?php use_javascript('/js/mustache/mustache.min.js') ?>
<?php use_javascript('/js/material/material.min.js') ?>
<?php use_javascript('/js/kiosk/kiosk.js') ?>
<div class="app-layout mdl-layout mdl-js-layout mdl-layout--fixed-header">
	<header class="app-header mdl-layout__header">
		<div class="mdl-layout__header-row mdl-color--light-blue-300">
			<span class="mdl-layout-title">e-kiosk</span>
			<div class="mdl-layout-spacer"></div>
			<div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable">
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
			</ul>
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
		<button id="back-fab"class="mdl-button mdl-js-button mdl-button--fab mdl-button--colored mdl-color--light-blue-300 waves-effect">
  			<i class="material-icons">keyboard_backspace</i>
		</button>
		<!-- Manif details panel -->
		<div id="manif-details-panel" class="mdl-card mdl-shadow--2dp"></div>
		<!-- manis list -->
		<div id="manifs">
			<ul id="manifs-list" class="flex-list"></ul>
		</div>
		<!-- cart panel -->
		<div id="cart" class="mdl-color--blue-grey-600">
			<ul id="cart-lines"></ul>
			<div id="cart-total"><?php echo __('Total') ?></div>
		</div>	
	</main>
</div>

<!-- MUSTACHE TEMPLATES -->
	<!-- manif card -->
<script id="manif-card-template" type="x-tmpl-mustache">
<li class="manif"> 
	<div class="manif-card-wide mdl-card mdl-shadow--2dp waves-effect" id="{{ manif.id }}">
		<div class="mdl-card__title manif-title" style="background-color: {{ manif.color }};">
			<p class="mdl-card__title-text manif-name">{{ manif.name }}</p>
			<p class="mdl-card__title-text manif-happens_at"><i class="material-icons" role="presentation">access_time</i>{{ manif.start }}</p>
			<p class="mdl-card__title-text manif-location"><i class="material-icons" role="presentation">location_on</i>{{ manif.location }}</p>
		</div>
		<div class="mdl-card__supporting-text manif-description">
			{{ manif.description }}
		</div>
		<!-- <div class="mdl-card__actions mdl-card--border manif-actions">
		 	<a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">
		 		<?php echo __('Book') ?>
		 	</a>
		</div> -->
	</div>
</li>
</script>

	<!-- manif details -->
<script id="manif-details-template" type="x-tmpl-mustache">
	<h6 id="details-title"><?php echo __('Order') ?></h6>
    <div id="details-content">
    	<div class="manif-details">
    		<div> {{ manif.name }}</div>
    		<div>{{ manif.gauge }}</div>
    		<div>
    			<i class="material-icons" role="presentation">access_time</i>
    			<span class="mdl-color-text--pink">{{ manif.start }} - {{ manif.end }}</span>
    		</div>
    		<div>
    			<i class="material-icons" role="presentation">location_on</i>
    			<span>{{ manif.location }}</span>
    		</div>
    		<div>{{ manif.color }}</div>
    	</div>
    	<ul id="prices" class="flex-list"></ul>
   	<div>
</script>

	<!-- price card -->
<script id="price-card-template" type="x-tmpl-mustache">
	<li class="price">
		<div id="{{ price.id }}" class="price-card-square mdl-card mdl-shadow--2dp waves-effect">
  			<div class="mdl-card__title mdl-card--expand" style="background-color: {{ price.color }};">
    			{{ price.name }}
    		</div>
  		</div>
  	</li>
</script>

	<!-- cart line -->
<script id="cart-line-template" type="x-tmpl-mustache">
	<li class="cart-line mdl-color--blue-grey-800" id="{{ line.id }}" style="border-right: 5px solid {{ line.price.color }};">
		<button class="remove-item mdl-button mdl-js-button mdl-button--fab mdl-button--colored">
  			<i class="material-icons">remove</i>
		</button>
		<p class="line-main">
			<span class="line-qty">{{ line.qty }}</span>
			<span class="line-multiplier"> x </span>
			<span class="line-name">{{ line.name }}</span>
		<p>
		
			<span class="line-price">{{ line.price.name }} ({{line.price.value}})</span>
		
		<span class="line-total">{{ line.total }}</span>
		<span line-currency> €</span>
  	</li>
</script>