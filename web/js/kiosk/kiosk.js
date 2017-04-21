/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Romain SANCHEZ <romain.sanchez AT libre-informatique.fr>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
if ( LI === undefined )
  var LI = {};
if ( LI.kiosk === undefined )
  LI.kiosk = [];

LI.kiosk.utils = {};
LI.kiosk.transaction = {};
LI.kiosk.templates = {};
LI.kiosk.cart = {
	lines: {}
};
LI.kiosk.products = {};

$(document).ready(function() {
	$('a.culture-' + $('#user-culture').data('culture')).hide();
	LI.kiosk.init();
});

/********************* INIT ***********************/
LI.kiosk.init = function() {
	LI.kiosk.utils.showLoader();
	LI.kiosk.urls = $('#kiosk-urls').data();
	LI.kiosk.initPlugins();
	LI.kiosk.addListeners();
	// retrieve data then display menu
	$.when(
		LI.kiosk.getCSRF(),
		LI.kiosk.getTransaction(), 
		LI.kiosk.getManifestations(),
		LI.kiosk.getMuseum(),
		LI.kiosk.getStore()
	 )
     .then(function() {
     	//check if product type menu is needed
     	var lists = {};

		$.each(LI.kiosk.products, function(key, productList){
			var listLength = Object.keys(productList).length;

			if( listLength > 0)
				lists[key] = listLength;
		});

		if(Object.keys(lists).length > 1) {
			$(document).trigger('menu:mount');
		}else {
			$(document).trigger({
				type: 'product-list:mount',
				productType: Object.keys(lists)[0]
			});
		}

		LI.kiosk.utils.hideLoader();
	 })
	;
}

LI.kiosk.initPlugins = function() {
	Waves.attach('.waves-effect');
	Waves.init();
	LI.kiosk.cacheTemplates();
	toastr.options = {
		positionClass: 'toast-bottom-full-width',
		closeButton: true,
		timeOut: 0
	};
}

LI.kiosk.addListeners = function() {
	//accessibility mode
	$('#access-fab').click(function() {

		if($('#app').css('marginTop') == '0px'){
			$('#app').css({
				'height': '50vh',
				'margin-top': '50vh'
			});
		}else{
			$('#app').css({
				'height': '100vh',
				'margin-top': '0'
			});
		}
	});

	$(document)
		.on('menu:mount', function() {
			LI.kiosk.mountProductMenu();
		})
		.on('menu:unmount', function(e) {
			LI.kiosk.menuToList(e.productType);
		})
		.on('product-list:mount', function(e) {
			LI.kiosk.mountProductList(e.productType, e.mode);
		})
		.on('product-list:unmount', function(e) {
			if(e.mode == 'back') {
				LI.kiosk.listToMenu();
			}else{
				LI.kiosk.listToProduct(e.product);
			}
		})
		.on('product-details:mount', function(e) {
			LI.kiosk.mountProductDetails(e.product);
		})
		.on('product-details:unmount', function(e) {
			LI.kiosk.productToList(e.productType);
		})
	;

	//product clicks
	$('#product-list').on('click', '.product', function(event) {
		var productCard = $(event.currentTarget);
		var type = productCard.data('type');
		var id = productCard.data('id');

		$(document).trigger({
			type: 'product-list:unmount',
			mode: 'forth',
			product: LI.kiosk.products[type][id]
		});
	});

	//cart validation clicks
	$('#confirm-btn').click(function(){
		LI.kiosk.checkout();
	});
}

LI.kiosk.getTransaction = function(){
	return $.get(LI.kiosk.urls.getNewTransaction, function(data) {
		LI.kiosk.transaction.id = data;
	});
}

LI.kiosk.getCSRF = function() {
	return $.get(LI.kiosk.urls.getCsrf, function(token) {
		LI.kiosk.CSRF = token;
	});
}

LI.kiosk.getManifestations = function(){
	return $.ajax({
	    url: LI.kiosk.urls.getManifestations,
	    type: 'GET',
	    success: LI.kiosk.cacheManifestations,
	    error: LI.kiosk.utils.error
  	});
}

LI.kiosk.getStore = function(){
	return $.ajax({
	    url: LI.kiosk.urls.getStore,
	    type: 'GET',
	    success: LI.kiosk.cacheStore,
	    error: LI.kiosk.utils.error
  	});
}

LI.kiosk.getMuseum = function(){
	return $.ajax({
	    url: LI.kiosk.urls.getMuseum,
	    type: 'GET',
	    success: LI.kiosk.cacheMuseum,
	    error: LI.kiosk.utils.error
  	});
}

/********************* UI CHANGES ***********************/
LI.kiosk.mountProductMenu = function() {
	LI.kiosk.utils.resetBackFab();

	if( !$('#product-menu-items').children().length > 0 ) {
		$.each(LI.kiosk.products, function(type, length){	
			var template = Handlebars.compile(LI.kiosk.templates.menuItem);
			var item = {
				name: $('[data-source="' + type + '"]').data('target'),
				type: type
			};

			$('#product-menu-items').append(template(item));
		});

		$('.menu-item').click(function(){
			$(document).trigger({
				type: 'menu:unmount',
				productType: $(this).data('type')
			});
		});
	}

	$('#product-menu').effect('slide', {
		direction: 'left',
		mode: 'show',
		duration: '300'
	});
}

LI.kiosk.menuToList = function(productType) {
	$('#product-menu').effect('slide', {
		direction: 'left',
		mode: 'hide',
		duration: '300',
		complete: function() {
			$(document).trigger({
				type: 'product-list:mount',
				productType: productType,
				mode: 'forth'
			});
		}
	});
}

LI.kiosk.mountProductList = function(type, mode) {
	var direction = mode == 'back' ? 'left': 'right';

	LI.kiosk.utils.resetBackFab();
	LI.kiosk.insertProducts(type);

	$('#products').effect('slide', {
		direction: direction, 
		mode: 'show',
		duration: '300',
		complete: function() {
			$('#back-fab')
				.click(function() {
					$(document).trigger({
						type: 'product-list:unmount',
						mode: 'back'
					});
				})
				.show()
			;
		}
	});
}

LI.kiosk.listToProduct = function(product) {
	LI.kiosk.utils.resetBackFab();

	$('#products').effect('slide', {
		direction: 'left',
		mode: 'hide',
		duration: '300',
		complete: function() {
			$(document).trigger({
				type: 'product-details:mount',
				product: product
			});
		}
	});
}

LI.kiosk.listToMenu = function() {
	LI.kiosk.utils.resetBackFab();

	$('#products').effect('slide', {
		direction: 'right',
		mode: 'hide',
		duration: '300',
		complete: function() {
			$(document).trigger('menu:mount');
		}
	});
}

LI.kiosk.mountProductDetails = function(product) {
	var detailsTemplate = Handlebars.compile(LI.kiosk.templates.productDetails);

	// insert manif info
	$('#product-details-card').html(detailsTemplate(product));

	if( Object.keys(product.declinations).length > 1 ){

		var declinationTemplate = Handlebars.compile(LI.kiosk.templates.declinationCard);
		
		$.each(product.declinations, function(id, declination){
			$('#declinations').append(declinationTemplate(declination));
		});

		$('#declinations').css('display', 'flex');

		$('.declination').click(function(event){
			var declination = product.declinations[$(event.currentTarget.children).attr('id')];

			$('#declinations').hide();
			$('#declination-name').text(declination.name);
			LI.kiosk.insertPrices(product, declination);
		});
	}else{
		LI.kiosk.insertPrices(product, Object.values(product.declinations)[0]);
	}

	$('#product-details').effect('slide', {
		direction: 'right',
		mode: 'show',
		duration: '300',
		complete: function() {
			$('#back-fab')
				.click(function() {
					$(document).trigger({
						type: 'product-details:unmount',
						productType: product.type
					});
				})
				.show()
			;
		}
	});
}

LI.kiosk.productToList = function(productType) {
	LI.kiosk.utils.resetBackFab();
	$('#product-details').effect('slide', {
		direction: 'right',
		mode: 'hide',
		duration: '300',
		complete: function() {
			$(document).trigger({
				type: 'product-list:mount',
				mode: 'back',
				productType: productType
		});
		}
	});
}

LI.kiosk.insertProducts = function(type) {
	var cardTemplate = LI.kiosk.templates['productCard'][type];

	if(cardTemplate == null)
		cardTemplate = LI.kiosk.templates['productCard']['manifestations'];

	$('#product-list').empty();
	
	$.each(LI.kiosk.products[type], function(key, product){
		var template = Handlebars.compile(cardTemplate);
		$('#product-list').append(template(product));
	});
}

LI.kiosk.insertPrices = function(product, declination){
	var priceTemplate = $('#price-card-template').html();
	var prices = declination.available_prices;

	for(key in prices){
		var template = Handlebars.compile(priceTemplate);
		$('#prices').append(template(prices[key]));
	}

	LI.kiosk.addPriceListener(product, declination);

	$('#declination-back').click(function(){

		$('#prices').off().children('.price').remove();
		$('#prices').hide();
		$('#declinations').show();
	});

	$('#prices').css('display', 'flex');

}

LI.kiosk.addPriceListener = function(product, declination){
	$('#prices').on('click', '.price', function(event){
		var id = $(event.currentTarget).attr('id');

		LI.kiosk.cart.addItem(product, product.prices[$(event.currentTarget.children).attr('id')], declination);
	});
}

/********************* CACHE **************************/
LI.kiosk.rearrangeProperties = function(product){
	var productDate = new Date(product.happens_at.replace(' ', 'T'));
	var endDate = new Date(product.ends_at);

	product.declinations = {};
	product.prices = {};
	product.start = productDate.toLocaleString().replace(/:\d\d( \w+){0,1}$/,'');
	product.end = endDate.getHours() + ':' + endDate.getMinutes();
	product.name = product.name == null ? product.category : product.name;

	if(product.image_id != undefined)
		product.background = 'background-image: url("' + product.image_url + '"); background-size: cover;' ;
	else
		product.background = 'background-color: ' + product.color;

	$.each(product.gauges, function(i, gauge){

		var color = '#4FC3F7';

		if ( gauge.color == undefined )
			gauge.color = color;

		$.each(gauge.available_prices, function(key, price){
			if( price.color == undefined )
				price.color = color;

			product.prices[price.id] = price;
		});

		product.declinations[gauge.id] = gauge;
	});
}

LI.kiosk.cacheManifestations = function(data) {
	LI.kiosk.products.manifestations = {};
	var type = 'manifestations';

	$.each(data.success.success_fields[type].data.content, function(key, manif){
		
		if ( window.location.hash == '#debug' )
			console.log('Loading an item (#' + manif.id + ') from the ' + type);

		manif.type = type;
		LI.kiosk.rearrangeProperties(manif);
		LI.kiosk.products.manifestations[manif.id] = manif;
	});
}

LI.kiosk.cacheMuseum = function(data) {
	var type = 'museum';
	LI.kiosk.products.museum = {};

	$.each(data.success.success_fields[type].data.content, function(key, manif){
		
		if ( window.location.hash == '#debug' )
			console.log('Loading an item (#' + manif.id + ') from the ' + type);

		manif.type = type;
		manif.museum = true;
		LI.kiosk.rearrangeProperties(manif);
		LI.kiosk.products.museum[manif.id] = manif;
	});
}

LI.kiosk.cacheStore = function(data) {
	var type = 'store';
	LI.kiosk.products.store = {};

	$.each(data.success.success_fields[type].data.content, function(key, product){
		
		if ( window.location.hash == '#debug' )
			console.log('Loading an item (#' + product.id + ') from the ' + type);

		product.prices = {};
		product.type = type;

		$.each(product.declinations, function(i, declination){

			var color = '#4FC3F7';

			if ( declination.color == undefined )
				declination.color = color;

			$.each(declination.available_prices, function(key, price){
				if( price.color == undefined )
					price.color = color;

				product.prices[price.id] = price;
			});

			product.declinations[declination.id] = declination;
		});

		LI.kiosk.products.store[product.id] = product;
	});
}

LI.kiosk.cacheTemplates = function() {
	//make handlebars cache the templates for quicker future uses
	$('script[type="text/x-handlebars-template"]').each(function(id, template){
		var templateType = $(template).data('template-type');
		var productType = $(template).data('product-type');
		var html = $(template).html();

		if( LI.kiosk.templates[templateType] === undefined )
			LI.kiosk.templates[templateType] = {};

		if(productType !== undefined)
			LI.kiosk.templates[templateType][productType] = html;
		else
			LI.kiosk.templates[templateType] = html;
	});
}

/******************** CART ****************************/
LI.kiosk.cart.insertLine = function(line, item, price, declination) {
	var lineTemplate = Handlebars.compile(LI.kiosk.templates.cartLine);

	$('#cart-lines').append(lineTemplate(line));
	
	$('#' + line.id + ' .remove-item').click(function(){
		LI.kiosk.cart.removeItem(line.id, item);
	});

	$('#' + line.id + ' .add-item').click(function(){
		LI.kiosk.cart.addItem(item, price, declination);
	});
}

LI.kiosk.cart.removeLine = function(htmlLine) {
	$(htmlLine).hide(500).remove();
}

LI.kiosk.cart.lineTotal = function(line) {
	line.total = LI.format_currency(line.price.raw_value * line.qty, false);
}

LI.kiosk.cart.cartTotal = function() {
	var total = 0;

	$.each(LI.kiosk.cart.lines, function(key, line) {
		total += parseFloat(line.total);
	});

	$('#cart-total-value').text(LI.format_currency(total, false));
}

LI.kiosk.cart.addItem = function(item, price, declination) {
	var newLine;
	var lineId;
	var exists = false;

	$.each(LI.kiosk.cart.lines, function(key, line) {
		
		if(line.product.id == item.id && line.price.id == price.id && line.declination.id == declination.id){
			var htmlLine = $('#' + line.id);
			line.qty++;
			LI.kiosk.cart.lineTotal(line);
			exists = true;
			htmlLine.find('.line-total').text(line.total);
			htmlLine.find('.line-qty').text(line.qty);
			LI.kiosk.utils.flash('#' + line.id);
			lineId = line.id;
		}
	});

	if(!exists) {
		var newLine = {
			id: LI.kiosk.utils.generateUUID(),
			name: item.name,
			product: item,
			value: price.value,
			price: price,
			declination: declination,
			qty: 1,
			total: price.value
		};

		LI.kiosk.cart.lines[newLine.id] = newLine;
		LI.kiosk.cart.insertLine(LI.kiosk.cart.lines[newLine.id], item, price, declination);
		LI.kiosk.utils.flash('#' + newLine.id);
		lineId = newLine.id;
	}

	LI.kiosk.cart.cartTotal();

	if(!$('#cart').is(':visible')) {
		$('#cart').show(500);
		$('#cart').css('display', 'flex');
    }

	if(item.gauge_url !== undefined )
		LI.kiosk.checkAvailability(item.gauge_url, lineId, item.id);
    
         $.ajax({
		    url: '/tck.php/transaction/' + LI.kiosk.transaction.id + '/complete',
		    type: 'get',
		    data: { 
		    	transaction: {
		    		price_new: {
		    			_csrf_token: LI.kiosk.CSRF,
		    			price_id: price.id,
		    			declination_id: declination.id,
		    			type: item.type == 'store' ? 'declination' : 'gauge',
		    			bunch: item.type,
		    			id: LI.kiosk.transaction.id,
		    			state: '',
		    			qty: '1'
		    		}
		        }
		    },
		    error: LI.kiosk.utils.error
		});
    ;
}

LI.kiosk.cart.removeItem = function(lineId, item) {
	var line = LI.kiosk.cart.lines[lineId];
	var htmlLine = $('#' + lineId);

	line.qty--;
	LI.kiosk.cart.lineTotal(line);

	if(line.qty == 0){
		delete LI.kiosk.cart.lines[lineId]
		LI.kiosk.cart.removeLine(htmlLine);
	}else{
		LI.kiosk.cart.lines[lineId] = line;
		htmlLine.find('.line-qty').text(line.qty);
		htmlLine.find('.line-total').text(line.total);
	}

	$.ajax({
	    url: '/tck.php/transaction/' + LI.kiosk.transaction.id + '/complete',
	    type: 'get',
	    data: { 
	    	transaction: {
	    		price_new: {
	    			_csrf_token: LI.kiosk.CSRF,
	    			price_id: line.price.id,
	    			declination_id: line.declination.id,
	    			type: item.type == 'store' ? 'declination' : 'gauge',
	    			bunch: item.type,
	    			id: LI.kiosk.transaction.id,
	    			state: '',
	    			qty: '-1'
	    		}
	        }
	    },
	    error: LI.kiosk.utils.error
	});	

	LI.kiosk.cart.cartTotal();

	if(Object.keys(LI.kiosk.cart.lines) < 1)
		$('#cart').hide(200);
}

LI.kiosk.checkAvailability = function(gaugeUrl, lineId, productId){
	var qty = 0;
	var available = true;

	$.each(LI.kiosk.cart.lines, function(key, line){
		if(line.product.id == productId)
			qty += line.qty;
	});

	$.get(gaugeUrl, function(data){

		if(data.free < qty){
			available = false;
			$('#' + lineId + ' .remove-item').click();
			toastr.info('The last item added to the cart was removed as it wasn\'t available anymore');
		}
	});

	return available;
}

/************** CHECKOUT *******************************/
LI.kiosk.checkout = function() {
	alert('');
}

/********************* UTILS *************************/
LI.kiosk.utils.generateUUID = function(){
    var d = new Date().getTime();
    //Force letter as first character to avoid selector issues
    var uuid = 'Axxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c == 'x' ? r : (r & 0x7 | 0x8)).toString(16);
    });

    return uuid.toUpperCase();
}

LI.kiosk.utils.switchPanels = function() {
	$('#products').effect('slide', {
		direction: 'left', 
		mode: 'hide', 
		duration: '300',
		complete: function() {
			$(document).trigger({
				type: 'product-list:unmounted'
			});
		}
	});
	
	$('#back-fab').unbind('click').click(function() {
		$(this).hide();

		$('#product-details-card').effect('slide', {
			direction: 'right', 
			mode: 'hide',
			duration: '300',
			complete: function(){
				$(document).trigger({
					type: 'product-details:unmounted'
				});
			}
		});

		if($('#product-menu-items .menu-item').length > 0)
			$(this).unbind('click').click(function() {
				$('#products').effect('slide', {
					direction: 'right',
					mode: 'hide',
					duration: '300',
					complete: function(){
						$('#back-fab').hide();

						$('#product-menu').effect('slide', {
							direction: 'left', 
							mode: 'show', 
							duration: '300'
						});
					}
				});
			}).show();
		
	}).show();
}

LI.kiosk.utils.error = function(error){
	console.error(error);
}

LI.kiosk.utils.showLoader = function(){
	$('#spinner')
	    .addClass('is-active')
	    .css('display', 'block')
	;
}

LI.kiosk.utils.hideLoader = function(){
	$('#spinner')
	    .removeClass('is-active')
	    .css('display', 'none')
	;
}

LI.kiosk.utils.flash = function(selector){
	Waves.attach(selector);
	Waves.init();
	Waves.ripple(selector);
}

LI.kiosk.utils.resetBackFab = function() {
	$('#back-fab').unbind('click').hide();
}