<?php use_helper('I18N') ?>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<?php use_stylesheet('material.min.css') ?>
<?php use_stylesheet('dialog-polyfill.css') ?>
<?php use_stylesheet('kiosk') ?>
<?php use_javascript('jquery') ?>
<?php use_javascript('/js/material/dialog-polyfill.js') ?>
<?php use_javascript('/js/mustache/mustache.min.js') ?>
<?php use_javascript('/js/material/material.min.js') ?>
<?php use_javascript('/js/kiosk/kiosk.js') ?>
<div class="app-layout mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">
	<header class="app-header mdl-layout__header">
		<div class="mdl-layout__header-row">
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
	<div class="app-drawer mdl-layout__drawer mdl-color--blue-grey-900 mdl-color-text--blue-grey-50">
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
	</div>
	<main class="mdl-layout__content mdl-color--blue-grey-800">
		<div class="mdl-spinner mdl-js-spinner is-active" id="spinner"></div>
		<ul id="manifs-list">
			
		</ul>
	</main>
</div>
<!-- ORDER DIALOG -->
<dialog class="mdl-dialog" id="manif-dialog">
</dialog>

<!-- MUSTACHE TEMPLATES -->
	<!-- manif card -->
<script id="manif-card-template" type="x-tmpl-mustache">
<li class="manif"> 
	<div class="manif-card-wide mdl-card mdl-shadow--2dp mdl-js-ripple-effect" id="{{ manif.id }}">
		<div class="mdl-card__title manif-title" style="background-color: {{ manif.color }};">
			<p class="mdl-card__title-text manif-name">{{ manif.name }}</p>
			<p class="mdl-card__title-text manif-happens_at"><i class="material-icons" role="presentation">access_time</i>{{ manif.happens_at }}</p>
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

	<!-- manif dialog -->
<script id="manif-dialog-template" type="x-tmpl-mustache">
	<h6 id="dialog-title" class="mdl-dialog__title"><?php echo __('Order') ?></h6>
    <div class="mdl-dialog__content" id="dialog-content">
    	<div> {{ manif.name }}</div>
    	<div>{{ manif.gauge }}</div>
    	<div>{{ manif.start }} - {{ manif.end }}</div>
    	<div>{{ manif.location }}</div>
    	<div>{{ manif.color }}</div>
    	<div id="prices"></div>
    </div>
    <div class="mdl-dialog__actions mdl-dialog__actions">
      <button type="button" class="mdl-button"><?php echo __('Order') ?></button>
      <button type="button" class="mdl-button close"><?php echo __('Cancel') ?></button>
    </div>
</script>

	<!-- price widget -->
<script id="price-widget-template" type="x-tmpl-mustache">
	<p>{{ price.name }}</p>

</script>