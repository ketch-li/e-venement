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
*    Copyright (c) 2017 Romain SANCHEZ <romain.sanchez AT libre-informatique.fr>
*    Copyright (c) 2017 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
$(document).ready(function() {
	LI.kiosk.init();
});

if ( LI === undefined )
  var LI = {};

LI.kiosk = {
	connector: new EveConnector('https://localhost:8164'),
	devices: {
		ept: {
			type: 'serial',
			params: {
				baudrate: 1200,
				comName: '/dev/ttyACM0',
				databits: 7,
				parity: 'even',
				pnpId: 'usb-079b_0028-if00'
			}
		},
		printer: {
			type: 'usb',
			params: {
				pid: 1,
				vid: '1305'
			}
		}
	},
	templates: {},
	dialogs: {},
	transaction: {},
	products: {},
	urls: {},
	currentPanel: {},
	config: {},
	countries: {},
	init: function() {
		LI.kiosk.utils.showLoader();
		LI.kiosk.config = $('#config').data();
		LI.kiosk.urls = $('#kiosk-urls').data();
		LI.kiosk.initPlugins();
		LI.kiosk.addListeners();

		//hide current culture from menu
		$('.culture[data-culture="' + LI.kiosk.config.culture + '"]').hide();

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

			$.each(LI.kiosk.products, function(key, productList) {
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

			if(LI.kiosk.config.showLocationPrompt) {
				LI.kiosk.getCountries();
			}
		 })
		;
	},
	initPlugins: function() {
		Waves.attach('.waves-effect');
		Waves.init();
		LI.kiosk.cacheTemplates();
		toastr.options = {
			positionClass: 'toast-bottom-full-width',
			closeButton: true,
			timeOut: 0
		};

    	$('.mdl-dialog').each(function(key, elem) {
    		var dialog = $(elem).get(0);

    		dialogPolyfill.registerDialog(dialog);

    		LI.kiosk.dialogs[$(elem).prop('id')] = dialog;
    	});
	},
	addListeners: function() {
		//UI transitions
		$(document)
			.on('menu:mount', function() {
				LI.kiosk.mountProductMenu();
			})
			.on('menu:unmount', function(e) {
				LI.kiosk.menuToList(e.productType);
				$('.culture').hide();
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
			.on('declinations:mount', function(e) {
				LI.kiosk.mountDeclinations(e.product);
			})
			.on('declinations:unmount', function(e) {
				LI.kiosk.declinationsToPrices(e.product, e.declination);
			})
			.on('prices:mount', function(e) {
				LI.kiosk.mountPrices(e.product, e.declination, e.mode);
			})
			.on('prices:unmount', function(e) {
				if(e.mode == 'direct') {
					LI.kiosk.pricesToProducts(e.product.type, 'back');
				} else {
					LI.kiosk.pricesToDeclinations(e.product);
				}
			})
		;

		//accessibility mode
		$('#access-fab').click(function() {
			$('#access-fab, #app, #back-fab, .panel, #product-details-card').toggleClass('a11y');
		});

		//info button
		$('#info-btn').click(function() {
			$('#info-panel').toggle(500);

			setTimeout(function() {
				$('#info-panel').hide(500);
			}, 10000);
		});

		//breadcrumbs clicks
		$('.breadcrumb').not(':last-child').click(function() {
			var id = $(this).prop('id');
			var target = $(this).data('target');

			$('.breadcrumb')
				.not($(this))
				.not('#home-breadcrumb')
				.hide()
			;

			LI.kiosk.utils.switchPanels('right', function() {
				$('#' + target).effect('slide', {
					direction: 'left',
					mode: 'show',
					duration: 500
				});
			});
		});

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
		$('#confirm-btn').click(function() {
			if(LI.kiosk.config.showLocationPrompt) {
				LI.kiosk.utils.showLocationPrompt();
			} else {
				LI.kiosk.checkout();
			}
		});
	},
	getTransaction: function() {
		return $.get(LI.kiosk.urls.getNewTransaction, function(data) {
			LI.kiosk.transaction.id = data;
		});
	},
	getCSRF: function() {
		return $.get(LI.kiosk.urls.getCsrf, function(token) {
			LI.kiosk.CSRF = token;
		});
	},
	getCountries: function() {
		return $.get(LI.kiosk.urls.getCountries + '?culture=' + LI.kiosk.config.culture, function(data) {
			LI.kiosk.countries = JSON.parse(data);
		});
	},
	getManifestations: function() {
		return $.ajax({
		    url: LI.kiosk.urls.getManifestations,
		    type: 'GET',
		    success: LI.kiosk.cacheManifestations,
		    error: LI.kiosk.utils.error
	  	});
	},
	getStore: function() {
		return $.ajax({
		    url: LI.kiosk.urls.getStore,
		    type: 'GET',
		    success: LI.kiosk.cacheStore,
		    error: LI.kiosk.utils.error
	  	});
	},
	getMuseum: function() {
		return $.ajax({
		    url: LI.kiosk.urls.getMuseum,
		    type: 'GET',
		    success: LI.kiosk.cacheMuseum,
		    error: LI.kiosk.utils.error
	  	});
	},
	mountProductMenu: function() {
		LI.kiosk.utils.resetBackFab();

		if( !$('#product-menu-items').children().length > 0 ) {
			var template = Handlebars.compile(LI.kiosk.templates.menuItem);

			$.each(LI.kiosk.products, function(type, length){	
				var item = {
					name: $('[data-source="' + type + '"]').data('target'),
					type: type
				};

				$('#product-menu-items').append(template(item));
			});

			$('.menu-item').click(function() {
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
	},
	menuToList: function(productType) {
		LI.kiosk.utils.switchPanels('left', function() {
			$(document).trigger({
				type: 'product-list:mount',
				productType: productType,
				mode: 'forth'
			});
		});
	},
	mountProductList: function(type, mode) {
		var direction = mode == 'back' ? 'left': 'right';

		LI.kiosk.utils.resetBackFab();
		
		$('#back-fab')
			.click(function() {
				$(document).trigger({
					type: 'product-list:unmount',
					mode: 'back'
				});
			})
			.show()
		;

		$('#products-breadcrumb a')
			.html($('[data-source="' + type + '"]').data('target'))
			.parent()
			.css('display', 'inline-block')
		;

		LI.kiosk.insertProducts(type); 

		$('#products').effect('slide', {
			direction: direction, 
			mode: 'show',
			duration: '300'
		});
	},
	listToProduct: function(product) {
		LI.kiosk.utils.switchPanels('left', function() {
			$(document).trigger({
				type: 'product-details:mount',
				product: product
			});
		});
	},
	listToMenu: function() {
		$('#products-breadcrumb').hide();

		LI.kiosk.utils.switchPanels('right', function() {
			$(document).trigger('menu:mount');
		});
	},
	mountProductDetails: function(product) {
		if(product.type == 'store') {
			LI.kiosk.insertStoreProductDetails(product);
		} else {
			LI.kiosk.insertProductDetails(product);
		}

		$('#details').effect('slide', {
			direction: 'right',
			mode: 'show',
			duration: '300',
			complete: function() {
				$('#details-breadcrumb a')
					.html(product.name)
					.parent()
					.css('display', 'inline-block')
				;
			}
		});
	},
	productToList: function(productType) {
		$('#details-breadcrumb').hide();

		LI.kiosk.utils.switchPanels('right', function() {
			$(document).trigger({
				type: 'product-list:mount',
				mode: 'back',
				productType: productType
			});
		});
	},
	mountDeclinations: function(product) {
		var declinationList = $('#declinations');
		var declinationTemplate = Handlebars.compile(LI.kiosk.templates.declinationCard);
		
		declinationList.empty();
		$('#prices')
			.empty()
			.hide()
		;

		$.each(product.declinations, function(id, declination) {
			if(product.type == 'store') {
				declination.store = true;
				declination.value = declination.available_prices[Object.keys(declination.available_prices)[0]].value;
			}

			declinationList.append(declinationTemplate(declination));
		});

		$('#back-fab')
			.click(function() {
				$(document).trigger({
					type: 'product-details:unmount',
					productType: product.type
				})
			})
			.show()
		;

		if(product.type !== 'store') {
			$('.declination').off('click').click(function(event) {
				var declination = product.declinations[$(event.currentTarget.children).attr('id')];

				$(document).trigger({
					type: 'declinations:unmount',
					product: product,
					declination: declination
				});

				declinationList.hide();
			});
		}else {
			$('.declination').off('click').click(function(event) {
				var declination = product.declinations[$(event.currentTarget.children).attr('id')];
				var price = declination.available_prices[Object.keys(declination.available_prices)[0]];
			
				LI.kiosk.cart.addItem(product, price, declination);
			});
		}

		$('#declinations').css('display', 'flex');
	},
	declinationsToPrices: function(product, declination) {
		LI.kiosk.utils.resetBackFab();

		$('#declinations').effect('slide', {
			direction: 'left',
			mode: 'hide',
			duration: '150',
			complete: function() {
				$(document).trigger({
					type: 'prices:mount',
					product: product,
					declination: declination
				});
			}
		});
	},
	mountPrices: function(product, declination, mode) {
		LI.kiosk.insertPrices(product, declination);

		$('#back-fab')
			.click(function() {
				$(document).trigger({
					type: 'prices:unmount',
					mode: mode,
					product: product,
					declination: declination
				})
			})
			.show();
		;
		
		$('#prices').css('display', 'flex');
	},
	pricesToProducts: function(productType, mode) {
		$('#details-breadcrumb').hide();

		$('#prices').effect('slide', {
			direction: 'right',
			mode: 'hide',
			duration: '300',
			complete: function() {
				$('#details').hide();
				
				$(document).trigger({
					type: 'product-list:mount',
					productType: productType,
					mode: mode
				});
			}
		});
	},
	pricesToDeclinations: function(product) {
		LI.kiosk.utils.resetBackFab();

		$('#prices').effect('slide', {
			direction: 'right',
			mode: 'hide',
			duration: '300',
			complete: function() {
				$(document).trigger({
					type: 'declinations:mount',
					product: product
				});
			}
		});
	},
	insertProducts: function(type) {
		var cardTemplate = LI.kiosk.templates['productCard'][type];

		if(cardTemplate == null)
			cardTemplate = LI.kiosk.templates['productCard']['manifestations'];

		$('#product-list').empty();
		
		var template = Handlebars.compile(cardTemplate);

		$.each(LI.kiosk.products[type], function(key, product){
			$('#product-list').append(template(product));
		});
	},
	insertProductDetails: function(product) {
		var detailsTemplate = Handlebars.compile(LI.kiosk.templates.productDetails);

		$('#declinations').empty();
		// insert manif info
		$('#product-details-card').html(detailsTemplate(product));

		if( Object.keys(product.declinations).length > 1 ) {
			$(document).trigger({
				type: 'declinations:mount',
				product: product
			});
		}else {
			$(document).trigger({
				type: 'prices:mount',
				product: product,
				mode: 'direct',
				declination: Object.values(product.declinations)[0]
			});
		}
	},
	insertStoreProductDetails:  function(product) {
	 	var detailsTemplate = Handlebars.compile(LI.kiosk.templates.productDetails);

	 	$('#product-details-card').html(detailsTemplate(product));

 		$(document).trigger({
 			type: 'declinations:mount',
 			product: product
 		});
	},
	insertPrices: function(product, declination) {
		var priceTemplate = $('#price-card-template').html();
		var prices = declination.available_prices;

		$('#prices #declinations')
			.empty()
			.hide()
		;

		var template = Handlebars.compile(priceTemplate);

		for(key in prices){
			$('#prices').append(template(prices[key]));
		}

		LI.kiosk.addPriceListener(product, declination);
	},
	addPriceListener: function(product, declination) {
		$('#prices').off('click').on('click', '.price', function(event) {
			LI.kiosk.cart.addItem(product, product.prices[$(event.currentTarget.children).attr('id')], declination);
		});
	},
	/********************* CACHE **************************/
	rearrangeProperties: function(product) {
		var productDate = new Date(product.happens_at.replace(' ', 'T'));
		var endDate = new Date(product.ends_at);

		product.declinations = {};
		product.prices = {};
		product.start = productDate.toLocaleString().replace(/:\d\d( \w+){0,1}$/,'');
		product.end = endDate.getHours() + ':' + endDate.getMinutes();
		product.name = product.name == null ? product.category : product.name;

		if(product.image_id != undefined)
			product.background = 'background-image: url("' + product.image_url + '"); background-size: cover;';
		else
			product.background = 'background-color: ' + product.color;

		$.each(product.gauges, function(i, gauge){

			var color = '#4FC3F7';

			if ( gauge.color == undefined )
				gauge.color = color;

			$.each(gauge.available_prices, function(key, price){
				if( price.color == undefined || price.color == '0') {
					price.color = color;
				}

				if(LI.kiosk.config.uiLabels.price !== undefined) {
					price.name = price[LI.kiosk.config.uiLabels.price]
				}

				product.prices[price.id] = price;
			});

			product.declinations[gauge.id] = gauge;
		});
	},
	cacheManifestations: function(data) {
		LI.kiosk.products.manifestations = {};
		var type = 'manifestations';

		$.each(data.success.success_fields[type].data.content, function(key, manif) {
			
			if ( window.location.hash == '#debug' )
				console.log('Loading an item (#' + manif.id + ') from the ' + type);

			manif.type = type;
			LI.kiosk.rearrangeProperties(manif);
			LI.kiosk.products.manifestations[manif.id] = manif;
		});
	},
	cacheMuseum: function(data) {
		var type = 'museum';
		LI.kiosk.products.museum = {};

		$.each(data.success.success_fields[type].data.content, function(key, manif) {
			
			if ( window.location.hash == '#debug' )
				console.log('Loading an item (#' + manif.id + ') from the ' + type);

			manif.type = type;
			manif.museum = true;
			LI.kiosk.rearrangeProperties(manif);
			LI.kiosk.products.museum[manif.id] = manif;
		});
	},
	cacheStore: function(data) {
		var type = 'store';
		LI.kiosk.products.store = {};

		$.each(data.success.success_fields[type].data.content, function(key, product) {
			
			if ( window.location.hash == '#debug' )
				console.log('Loading an item (#' + product.id + ') from the ' + type);

			product.prices = {};
			product.type = type;
			product.store = true;

			$.each(product.declinations, function(i, declination) {

				var color = '#4FC3F7';

				if ( declination.color == undefined || declination.color == '0')
					declination.color = color;

				$.each(declination.available_prices, function(key, price) {
					if( price.color == undefined || price.color == '0') {
						price.color = color;
					}

					if(LI.kiosk.config.uiLabels.price !== undefined) {
						price.name = price[LI.kiosk.config.uiLabels.price]
					}

					product.prices[price.id] = price;
				});

				product.declinations[declination.id] = declination;
			});

			LI.kiosk.products.store[product.id] = product;
		});
	},
	cacheTemplates: function() {
		//make handlebars cache the templates for quicker future uses
		$('script[type="text/x-handlebars-template"]').each(function(id, template) {
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
	},
	/******************** CART ****************************/
	cart: {
		total: 0,
		lines: {},
		insertLine: function(line, item, price, declination) {
			var lineTemplate = Handlebars.compile(LI.kiosk.templates.cartLine);

			$('#cart-lines').append(lineTemplate(line));
			
			$('#' + line.id + ' .remove-item').click(function(){
				LI.kiosk.cart.removeItem(line.id, item);
			});

			$('#' + line.id + ' .add-item').click(function(){
				LI.kiosk.cart.addItem(item, price, declination);
			});
		},
		removeLine: function(htmlLine) {
			$(htmlLine).hide(500).remove();
		},
		lineTotal: function(line) {
			line.total = LI.format_currency(line.price.raw_value * line.qty, false);
		},
		cartTotal: function() {
			LI.kiosk.cart.total = 0;

			$.each(LI.kiosk.cart.lines, function(key, line) {
				LI.kiosk.cart.total += line.price.raw_value * line.qty;
			});

			$('#cart-total-value').text(LI.format_currency(LI.kiosk.cart.total, false));
		},
		addItem: function(item, price, declination) {
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

		    var available = false;
			
		    if(item.type == 'store') {
		    	available = true;
		    }

			if(item.gauge_url !== undefined ) {
				available = LI.kiosk.cart.checkAvailability(item.gauge_url, lineId, item.id);
			}
		    	
		    
			if(available) {
		    	LI.kiosk.cart.updateTransaction({ 
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
			    });
			}
		},
		removeItem: function(lineId, item) {
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

			LI.kiosk.cart.updateTransaction({ 
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
		    });

			LI.kiosk.cart.cartTotal();

			if(Object.keys(LI.kiosk.cart.lines) < 1)
				$('#cart').hide(200);
		},
		checkAvailability: function(gaugeUrl, lineId, productId) {
			var qty = 0;
			var available = true;

			$.each(LI.kiosk.cart.lines, function(key, line) {
				if(line.product.id == productId)
					qty += line.qty;
			});

			$.get(gaugeUrl, function(data) {

				if(data.free < qty){
					available = false;
					$('#' + lineId + ' .remove-item').click();
					toastr.info('The last item added to the cart was removed as it wasn\'t available anymore');
				}
			});

			return available;
		},
		updateTransaction: function(data, callback) {
			return $.ajax({
			    url: LI.kiosk.urls.completeTransaction.replace('-666', LI.kiosk.transaction.id),
			    type: 'get',
			    data: data,
			    success: callback,
			    error: LI.kiosk.utils.error
			});
		}
	},
	/************** CHECKOUT *******************************/
	checkout: function() {
		// LI.kiosk.utils.showPaymentPrompt();

		// var etpOptions = {
		//     amount: LI.kiosk.cart.total * 100,
		//     delay: 'A010',
		//     version: 'E+'
		// };

		// var message = new ConcertProtocolMessage(etpOptions);

		// var device = new ConcertProtocolDevice(LI.kiosk.devices.ept, LI.kiosk.connector);

		// device
		// 	.doTransaction(message)
		// 	.then(function(res) {
	 //        	if(res.stat === '0') {
	        		LI.kiosk.finalize();
	    //     	} else {
	    //     		console.error(res.stat + ' ' + res.getStatusText());
	    //     		LI.kiosk.utils.showFailurePrompt();
	    //     	}
	    // 	})
	    // 	.catch(function(err) {
	    //     	console.error(err);
	    // 	})
	    // ;
	},
	finalize: function() {
		LI.kiosk.utils.showSuccessPrompt();
		LI.kiosk.printTickets();
		LI.kiosk.printReceipt();

	},
	/******************  TICKETS **************************/
	integrateTickets: function() {
		return LI.kiosk.cart.updateTransaction({ 
    		transaction: {
    			store_integrate: {
    				_csrf_token: LI.kiosk.CSRF,
    				id: LI.kiosk.transaction.id,
    				force: ''
    			}
        	}
	    });
	},
	printTickets: function() {
		var data = 'G0AbHQMDAAAbHkEBGwcUFBsqclIbKnJBGypyUTEAGypyRDMAGypyVDEAGypyRjkAGypyRTkAABsq\r\nclAxMTYwAGIFAAAH\/\/\/gYgUAAAf\/\/+BiBQAAA\/\/\/4GIFAAAB\/\/\/gYgMAAADwYgMAAABwYgMAAABw\r\nYgMAAABwYgMAAABwGypyWTIzAGIcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH5iHQAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf+HGIdAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/4e\r\nYh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/h9iHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAH\/+H4BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/+H8BiHgAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAP\/+H8BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/+H+BiHgAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf4+D+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/g+\r\nB\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/g+A\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAA\/A+A\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/A+A\/BiHgAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAA\/A+AfBiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/A+A\/BiHgAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/A+A\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nA\/A+A\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/g+B\/BiHgAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAf4+D+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/+BiHgAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/8Bi\r\nHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/4BiHQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAD\/\/\/2IdAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/+Yh0AAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAD\/\/hiHQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/gBsqclk0AGIeAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\r\n\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAD\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/4GIeAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/4GIe\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAfgGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwGIeAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAH4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD4GIeAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\r\n8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAD8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH8GIeAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAP8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/8GIeAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\r\n\/\/\/\/8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAD\/\/\/\/wGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/wGIdAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/Yh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/wbKnJZ\r\nNwBiHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/\/\/wYh8AAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAP\/\/\/\/\/8GIfAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/\/\/BiHwAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/\/\/wYh8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/\/\r\n8GIfAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/\/\/BiHwAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAA\/\/\/\/\/\/wYh8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/\/8GIeAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAfwA\/gGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/AAPwGIe\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB+AAH4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAB+AAD4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8AAD8GIeAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAD8AAD8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8AAD8GIeAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAD8AAD8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAH\r\n8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/AAH8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAD\/gAf8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/4B\/8GIeAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAB\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/4GIeAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/wGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nf\/\/\/gGIdAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/Yh0AAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAP\/\/xiHQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/8Bsqclk2AGIeAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAA\/j\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/j\/\/\/\/\r\n4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/j\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAA\/j\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/j\/\/\/\/4GIeAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAA\/j\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/j\/\/\/\/4Bsqclk0\r\nAGIdAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABwYh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAHA\/5iHQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB8H\/2IeAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAfw\/\/gGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfw\/\/wGIeAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/x\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/x\/\/\r\n4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/x\/\/8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAD\/h\/H8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+D\/D8GIeAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAD8D+D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8D+D8GIeAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8H+D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\r\n8H8D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8H8D8GIeAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAD8H8D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8P4D8GIeAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAD+P4D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/4H8GIe\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/wf4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAB\/\/wf4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/wf4GIeAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAf\/gfwGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/AfgGIdAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAH+AfYh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB4b\r\nKnJZNgBiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/+BiHgAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAA\/\/\/\/+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/+BiHgAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/+Bi\r\nHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAA\/\/\/\/+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/+BiHgAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAH4BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8BiHgAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nA+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAA\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/BiHgAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAA\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/BiHgAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nA\/\/\/\/\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/\/BiHgAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAA\/\/\/\/\/BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/+BiHgAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/8BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/8Bi\r\nHQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/2IdAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAD\/\/\/8GypyWTYAYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/gYh4AAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/gYh4A\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAH\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAP\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gYhsAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAP4YhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4YhsAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwYhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwYhsA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwYhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHw\r\nYhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH4YhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAD8YhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/Yh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAA\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/gYh4AAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAD\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/gYh4AAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\r\n\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gYhsAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAP8YhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP4YhsAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAPwYhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwYhsAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAPwYhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHwYhsAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAH4YhsAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD4YhsAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAB8Yh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gYh4A\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAP\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAP\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gYh4AAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/g\r\nGypyWTIwAGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB8AAD8GIeAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAB8AAB8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB8AAB8GIeAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAB8AAD8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/\r\n8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAH\/\/\/\/\/8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/8GIeAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAH\/\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/4GIeAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/\/wGIfAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\r\n\/\/\/+AAFiHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfABBBjnYh8AAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAHwAAYccWIfAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgx\/zzzzzxiHwAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAIIMc88888Yh8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAADDjjjjj\r\n\/jjjjmIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGOeeeef\/+eeeeAYh8AAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAZ5555555\/\/55552IgAAAAAAAAAAAAAAAAAAAAAAAAAAACHHHHHHHnP\/\/fHHmAYiAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAPPPPPPPPP\/\/v\/PPMBiHwAAAAAAAAAAAAAAAAAAAAAAAAAAAM888888\r\n8\/\/+\/888Yh8AAAAAAAAAAAAAAAAAAAAAAAAAAADjjjjjjjj\/\/j\/jjmIgAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAACeeeeeeef\/\/+f+eeAYiAAAAAAAAAAAAAAAAAAAAAAAAAAAAB5555555\/\/\/5\/554BiIAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAhx5xxxxx\/x\/x\/5xwGIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAzzzz\r\nzzzz\/z7z\/zzAYh8AAAAAAAAAAAAAAAAAAAAAAAAAAADPPPPPPPP\/PvP\/PGIfAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAA444844478\/47849iIAAAAAAAAAAAAAAAAAAAAAAAAAAAAnnnnnnnn\/n\/n\/nngGIf\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAeeeeeeOH8D+f+eViHwAAAAAAAAAAAAAAAAAAAAAAAAAAAJxx\r\nxxxxx\/A\/x\/5xYh8AAAAAAAAAAAAAAAAAAAAAAAAAAADPPPDCAAPwPvP\/PGIfAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAzzwggAAD+D73\/zhiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAf8+P+NiHwAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAAAf\/\/\/\/nBYh8AAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAA\r\nAAD\/\/\/\/4QGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/2GIeAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAf\/\/\/ymIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/wGIeAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAT\/\/+wGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEe\/\/+\r\nEGIdAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEOf+eYh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAecccJiHQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAE8888GIdAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAzzzwgYh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADzjziBiHQAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAABnnnngGIcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGeeeFiHAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAhxxxgYhwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPPPP\r\nOGIbAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAjzzz2IbAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nA44442IbAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGeeeeGIbAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAEeeeeGIbAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHnHHCGIbAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAzzzzwmIbAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAzzzwgGIbAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAA4444gGIcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOeeeeAH5iHQAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAABnnnhAf+HGIdAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACHHHmAH\/4eYh0AAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAHPPPOA\/\/h9iHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAI8888AH\/+\r\nH4BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGOPOOAP\/+H8BiIAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAnnnngAP\/+H8AABGIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOeeeAB\/\/4f4AAEYiAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAccccAAH+Pg\/8ccRiIAAAAAAAAAAAAAAAAAAAAAAAAAAAIIM8888AA\/g+\r\nN\/888GIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAzzzwAAj+z7z\/zzwYiAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAADjjjgABPzvjvzjjBiIAAAAAAAAAAAAAAAAAAAAAAAAAAAADnnnnnnn\/n\/n\/nnnGIgAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAGeeeeOef+f+f+eeUYiAAAAAAAAAAAAAAAAAAAAAAAAAAAAAccccc\r\nccf8f8f8ccRiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAA8888888\/8+8\/888AJiIAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAA8888888\/8+8\/888GIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4444447+74\/8444\r\nYiEAAAAAAAAAAAAAAAAAAAAAAAAAAAAZ555555\/\/\/5\/555wQYiAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAZ555555\/\/\/\/\/555xiIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAxxxxxxx\/\/\/\/\/xxxGIgAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAADzzzzzzz\/\/\/\/zzzwYiAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPPPPPPPP\/\r\n\/\/\/PPPBiIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOPP\/\/\/+OOOGIgAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAGeeeeeeef\/\/+eeecYh8AAAAAAAAAAAAAAAAAAAAAAAAAAAAJ55555557\/\/4QQGIeAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAADHHHHHnHHH\/GGGIhAAAAAAAAAAAAAAAAAAAAAAAAAAAADzzzzzzz\r\nwgAAAAAAgGIbAAAAAAAAAAAAAAAAAAAAAAAAAAAADzzzzzxwgmIdAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAA44wwAAAAABwYh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAJ554YQAAHA\/5iHQAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAB8H\/2IeAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAAAAAAfw\/\/gGIeAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAfw\/\/wGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\n\/x\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/x\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAB\/x\/\/8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/h\/H8GIeAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAD+D\/D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8D+D8GIe\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8D+D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAD8H+D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8H8D8GIeAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAD8H8D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8H8D8GIeAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAD8P4D8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+P4D\r\n8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/4H8GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAB\/\/wf4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/wf4GIeAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAA\/\/wf4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/gfwGIeAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/AfgGIdAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nH+AfYh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB4bKnJZNgBiHgAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAA\/\/\/+BiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/+BiHwAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/+AIYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\r\n\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAP\/\/\/\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/\/\/gYhwAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAP\/gGIhAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/AAAAAAAgGIh\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD+AAAAAgggGIhAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAD8AAAAAAQwGIhAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD8AAAACeeUGIhAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAD8AAAACeeQGIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\r\n+GAAADnHYiEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH3GAAAPPPAYiEAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAACCAAAAD\/OAAADPPAYiEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAAAAD\/iAAADzjA\r\nYiEAAAAAAAAAAAAAAAAAAAAAAAAAAAAA554AAAB\/4AAAJ55QYiEAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAA554AAAP\/\/\/\/gJ55QYiAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcccAAAP\/\/\/\/gIcdiIQAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAA884AAA\/\/\/\/+A888BiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAA884AA\r\nA\/\/\/\/+AM88BiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAACOOAAAA\/\/\/\/+AOOMBiIQAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAADnngAAA\/\/\/\/+AnnnBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAABnngAAA\/\/\/\/+AH\r\nnlBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAABxxwAAA\/\/\/\/+AhxxBiIQAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAA888AAAc88AAAM88BiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAA884AAAM88AAAM88BiIQAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAOOMAAAOOMAAAOOOBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAABn\r\nnkAAAHnmACAnnnBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAABnngAAAHngAOAHnnBiIQAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAxxwAAAB\/\/\/+ABxxBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAA888AAAP\/\/\r\n\/+AM88BiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAA888AAAP\/\/\/+AM88BiIQAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAOPMAAAP\/\/\/+AOOOBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAABnnkAAAf\/\/\/+AHnnBi\r\nIQAAAAAAAAAAAAAAAAAAAAAAAAAAAABnngAAAf\/\/\/+ADnnBiIQAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAxxwAAA\/\/\/\/+ABxxBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAA888AAA\/\/8P4AM88BiIQAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAA888AAA\/98D4AM88BiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOMAA\r\nA\/P+B8AOOOBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAnnkAAA\/n\/B\/nnnnhiIQAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAnngAAA\/n\/B\/BnnnBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAxxwAAA\/x\/x\/xx\r\nxxhiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAA888AAA\/8+8\/8888BiIQAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAM88Mc8\/8+8\/8888BiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOMOOO\/v\/O\/OOOOBiIQAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAnnnnnn\/\/\/n\/nnnnhiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAn\r\nnnnnn\/\/\/\/\/nnnnBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAhxxxxx\/9\/\/\/x5xxhiIQAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAA888888\/8\/\/+8888xiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAM88888\/8\/\r\n\/+8888BiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOPOOOOP+P\/+OOOOBiIQAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAnnnnnnn\/n\/\/nnnnhiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHnnnnnn\/n\/3nnnnhi\r\nIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAhxxxxxxxx5xx55xxiIQAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAM88888888888888RiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAM8888888888888cBiHgAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAOOOOOOOOOOMNiHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAnnnnnnnnn\r\nnnhhYhwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB555544QQGIeAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAnHHH\/\/\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAzzww\/\/\/\/\/\/4GIeAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAzwgg\/\/\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/\/\/4GIeAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\r\n\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/\/\/\/4GIdAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAP\/\/\/Yh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/AD+AYh4AAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAD8AA\/AYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH4AAfgYh4A\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH4AAfwYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAPwAAPwYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwAAPwYh4AAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAPwAAPwYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPwAAPwYh4AAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAP4AAfwYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP8AA\/w\r\nYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP+AB\/wYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAH\/gH\/gYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/\/\/\/gYh4AAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAD\/\/\/\/AYh4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/\/\/AYh4AAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/\/+AYh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\r\n\/\/5iHQAAAAAAAAAAAAAAAH8AAAAAAAAAAAAAAAAAAA\/\/\/GIdAAAAAAAAAAAAAAAD\/\/AAAAAAAAAA\r\nAAAAAAAAAf\/gYg0AAAAAAAAAAAAAAA\/\/+GINAAAAAAAAAAAAAAAP\/\/xiDQAAAAAAAAAAAAAAHgA8\r\nYg0AAAAAAAAAAAAAABwAHmINAAAAAAAAAAAAAAAcAB5iDQAAAAAAAAAAAAAAHAAeYg0AAAAAAAAA\r\nAAAAAB4AHGINAAAAAAAAAAAAAAAf\/\/xiDQAAAAAAAAAAAAAAD\/\/4Yg0AAAAAAAAAAAAAAAf\/8GIN\r\nAAAAAAAAAAAAAAAB\/8AbKnJZMgBiDQAAAAAAAAAAAAAAAAHAYg0AAAAAAAAAAAAAAAfH8GINAAAA\r\nAAAAAAAAAAAP7\/hiDQAAAAAAAAAAAAAAD\/\/8YiEAAAAAAAAAAAAAAB\/+PAAAAAAAAAAAAAAAAAAA\r\nfgAAAACCYiEAAAAAAAAAAAAAABw8HgAAAAAAAAAAAAAAAAAH\/hwAAAADYiIAAAAAAAAAAAAAABwc\r\nHgAAAAAAAAAAAAAAAAAf\/h4Z5555wGIiAAAAAAAAAAAAAAAcHB4AAAAAAAAAAAAAAAAAP\/4fEGOe\r\necBiIgAAAAAAAAAAAAAAHAAcAAAAAAAAAAAAAAAAAH\/\/3555x5xAYiEAAAAAAAAAAAAAAB8AfAAA\r\nAAAAAAAAAAAAAAD\/\/v\/PPPPPYiEAAAAAAAAAAAAAAA+A\/AAAAAAAAAAAAACCDPP\/\/v\/PPPPPYiIA\r\nAAAAAAAAAAAAAA+A+AAAAAAAAAAAAABDDjn\/\/j\/jjjjjgGIiAAAAAAAAAAAAAAADgPAAAAAAAAAA\r\nAAGeeeef\/\/+f+eeeecBiIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAnnnnn\/n\/n\/nnnnnAYiIAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAMccccf8f8f8ccceQGIhAAAAAAAAAAAAAAAA8DwAAAAAAAAAAADz\r\nzzzz\/z7z\/zzzz2IhAAAAAAAAAAAAAAAA8DwAAAAAAAAAAABzzzzz\/z7z\/zzzz2IiAAAAAAAAAAAA\r\nAAAA8DwAAAAAAAAAAAA444478745844884BiIgAAAAAAAAAAAAAAAPA8AAAAAAAAAAAAnnnnn\/n\/\r\nn\/nnnnngYiIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJ5555\/5\/5\/55555wGIiAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAADHHHHH\/H\/n\/HHHGEBiIQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA88888\/8+9\/88\r\n889iHwAAAAAAAAAAAAAAAH8AAAAAAAAAAAAAM8888\/8+\/+IIYh8AAAAAAAAAAAAAAAP\/8AAAAAAA\r\nAAAAADjjjzn\/\/\/\/jBGIeAAAAAAAAAAAAAAAP\/\/gAAAAAAAAAAACeeeee\/\/\/\/4GIeAAAAAAAAAAAA\r\nAAAP\/\/wAAAAAAAAAAAAeeeee\/\/\/\/wGIeAAAAAAAAAAAAAAAeADwAAAAAAAAAAACnHHHAf\/\/\/gGId\r\nAAAAAAAAAAAAAAAcAB4AAAAAAAAAAAAzzzzwP\/\/\/Yh0AAAAAAAAAAAAAABwAHgAAAAAAAAAAADPP\r\nPPKP\/\/5iHQAAAAAAAAAAAAAAHAAeAAAAAAAAAAAAOOOOOMP\/+GIdAAAAAAAAAAAAAAAeABwAAAAA\r\nAAAAAAGeeeeeef+AYhwAAAAAAAAAAAAAAB\/\/\/AAAAAAAAAAAAB555554YGIdAAAAAAAAAAAAAAAP\r\n\/\/gAAAAAAAAAAAAHHHHHHnGAYh4AAAAAAAAAAAAAAAf\/8AAAAAAAAAAAADPPPPPPvPCAYh0AAAAA\r\nAAAAAAAAAAH\/wAAAAAAAAAAAADPPPPPPvHxiHgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOPOOP+O\r\nfsBiHwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABnnnnn\/n\/\/hAYh8AAAAAAAAAAAAAAADAHAAAAAAA\r\nAAAAAAAQZ57\/5\/\/4YWIfAAAAAAAAAAAAAAAH8BwAAAAAAAAAAAAAGHHH\/\/H\/3GBiIAAAAAAAAAAA\r\nAAAAD\/gcAAAAAAAAAAAAAAIM8\/+8\/+888GIgAAAAAAAAAAAAAAAf\/BwAAAAAAAAAAAAAAAhz\/zz\/\r\n7zwwYiEAAAAAAAAAAAAAAB4eHAAAAAAAAAAAAAAAAAP\/jj\/zjzjBYiIAAAAAAAAAAAAAABwPHAAA\r\nAAAAAAAAAAAAAAf555\/55554QGIiAAAAAAAAAAAAAAAcB5wAAAAAAAAAAAAAAAAD8Gef+eeeeEBi\r\nIgAAAAAAAAAAAAAAHAOcAAAAAAAAAAAAAAAAA\/hxx\/xxx5xgYiIAAAAAAAAAAAAAABwD\/AAAAAAA\r\nAAAAAAAAAAPwDPP\/PPPPPGIiAAAAAAAAAAAAAAAfwfwAAAAAAAAAAAAAAAAD8Ahz\/zzzzzhiIgAA\r\nAAAAAAAAAAAAD8D8AAAAAAAAAAAAAAAAA\/AAA\/OPPOOMYiMAAAAAAAAAAAAAAAfAfAAAAAAAAAAA\r\nAAAAAAP4AQf555555ARiIgAAAAAAAAAAAAAAA8AcAAAAAAAAAAAAAAAAA\/gAB\/BnnnngYiIAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH+AB\/4cccccGIiAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAB\/+H\/4AzzzzxiIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/\/\/+AI8888YiIAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/\/ADjjzjGIiAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nf\/\/\/gGeeeeRiIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/\/wBnnnngYiIAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAf\/\/4AcccccGIiAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/8\r\nzzzzzzxiIgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAH\/4Ic8888IYiEAAAAAAAAAAAAAAAAD\r\nwAAAAAAAAAAAAAAAAAAAAADjjzjjYiIAAAAAAAAAAAAAAAYP8AAAAAAAAAAAAAAAAAAAAAZ55555\r\nQGIhAAAAAAAAAAAAAAAOH\/gAAAAAAAAAAAAAAAAAAAEeeeeeEGIhAAAAAAAAAAAAAAAeP\/wAAAAA\r\nAAAAAAAAAAAAAACHHHHHEGIhAAAAAAAAAAAAAAAeODwAAAAAAAAAAAAAAAAAABzzzzzwgGIgAAAA\r\nAAAAAAAAAAAcOB4AAAAAAAAAAAAAAAAAAAzzzzzwYh8AAAAAAAAAAAAAABw4HgAAAAAAAAAAAAAA\r\nAAAP\/\/\/jjmIgAAAAAAAAAAAAAAAcOB4AAAAAAAAAAAAAAAAAP\/\/\/+eeEYh8AAAAAAAAAAAAAAB44\r\nHAAAAAAAAAAAAAAAAAD\/\/\/\/54WIfAAAAAAAAAAAAAAAP\/\/wAAAAAAAAAAAAAAAAA\/\/\/\/\/HBiHgAA\r\nAAAAAAAAAAAAD\/\/4AAAAAAAAAAAAAAAAAf\/\/\/+9iHgAAAAAAAAAAAAAAA\/\/wAAAAAAAAAAAAAAAA\r\nA\/\/\/\/+JiHgAAAAAAAAAAAAAAAP\/AAAAAAAAAAAAAAAAAO\/\/\/\/+BiHgAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAABH\/\/nnnhiHQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABn\/3njmIdAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAHH\/HHGYh0AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADPPP\/PKBiHQAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIM8\/88IGIdAAAAAAAAAAAAAAAf\/\/wAAAAAAAAAAAAAAA4\/\r\n844gYhwAAAAAAAAAAAAAAB\/\/\/AAAAAAAAAAAAAAZ55\/54WIcAAAAAAAAAAAAAAAf\/\/wAAAAAAAAA\r\nAAAAEOef+eBiIgAAAAAAAAAAAAAAB\/\/8AAAAAAAAAAAAAhxxx\/wgAAAAAABhYiIAAAAAAAAAAAAA\r\nAAOAAAAAAAAAAAAAAADPPPP\/CAAAAAADHGIiAAAAAAAAAAAAAAADgAAAAAAAAAAAAAABzzzz\/gAA\r\nAggzzzxiIgAAAAAAAAAAAAAAA4AAAAAAAAAAAAAAAOOOP\/\/\/\/+EMOOOOYiIAAAAAAAAAAAAAAAOA\r\nAAAAAAAAAAAAAQZ555\/\/\/\/\/5555552IiAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGeeef\/\/\/\/+eee\r\neediIgAAAAAAAAAAAAAAAAAAAAHAAAAAAAAAAxxxx\/\/\/\/\/xxxxxxYiIAAAAAAAAAAAAAAAAAAAAD\r\nwAeAAAAAAAPPPPP\/\/\/\/vPPPPPGIiAAAAAAAAAAAAAAAAfwAAA8YDgAAAAAADzzzz\/\/\/\/7zzzzzxi\r\nIgAAAAAAAAAAAAAAA\/\/wAAOGQ8AAAAAAAOOOO\/\/\/\/+OOOOOOYiMAAAAAAAAAAAAAAA\/\/+AADhkPA\r\nAAAAAAJ555\/\/\/\/\/5555554BiIgAAAAAAAAAAAAAAD\/\/8AAOGQ8AAAAAAAnnnnnnnnnnnnnnnYiMA\r\nAAAAAAAAAAAAAB4APAADxkeAAAAAAAMccccceccecccccYBiIgAAAAAAAAAAAAAAHAAeAAPmT4AA\r\nAAAAA888888888888888YiIAAAAAAAAAAAAAABwAHgAB\/\/8AAAAAAADPPPPPPPPvPPPPPGIiAAAA\r\nAAAAAAAAAAAcAB4AAP\/\/AAAAAAAA444444844444445iIgAAAAAAAAAAAAAAHgAcAAB\/\/AAAAAAA\r\nBnnnnn\/\/\/\/nnnnnhYiIAAAAAAAAAAAAAAB\/\/\/AAAH\/AAAAAAAAJ5555\/\/\/\/55554QWIfAAAAAAAA\r\nAAAAAAAP\/\/gAAAZAAAAAAAACHHHH\/\/\/\/\/GBiIAAAAAAAAAAAAAAAB\/\/wAAACQAAAAAAAA8888\/\/\/\r\n\/+88MGIeAAAAAAAAAAAAAAAB\/8AAAAAAAAAAAAAAzzzz\/\/\/\/4GIeAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAA4445\/\/\/\/4GIeAAAAAAAAAAAAAAAAwBwAAAAAAAAAAAACeeGD\/\/\/\/4GIeAAAAAAAAAAAA\r\nAAAH8BwAAAAAAAAAAAAAWEED+Pw\/gGIeAAAAAAAAAAAAAAAP+BwAAAAAAAAAAAAAAAAD8HwPgGIe\r\nAAAAAAAAAAAAAAAf\/BwAAAAAAAAAAAAAAAAD8HwHwGIeAAAAAAAAAAAAAAAeHhwAAAAAAAAAAAAA\r\nAAAD8DwD4GIeAAAAAAAAAAAAAAAcDxwAAAAAAAAAAAAAAAAD8D4D4GIeAAAAAAAAAAAAAAAcB5wA\r\nAAfAAAAAAAAAAAAD8D4D8GIeAAAAAAAAAAAAAAAcA5wAAH\/8AAAAAAAAAAAD8D4D8GIeAAAAAAAA\r\nAAAAAAAcA\/wAAP\/\/AAAAAAAAAAAD8D4D8GIeAAAAAAAAAAAAAAAfwfwAAf\/\/gAAAAAAAAAAD+D8D\r\n8GIeAAAAAAAAAAAAAAAPwPwAA8PPgAAAAAAAAAAD\/h+H8GIeAAAAAAAAAAAAAAAHwHwAA4HDgAAA\r\nAAAAAAAB\/x\/\/8GIeAAAAAAAAAAAAAAADwBwAA4DjwAAAAAAAAAAB\/x\/\/8GIeAAAAAAAAAAAAAAAA\r\nAAAAA4DjwAAAAAAAAAAA\/w\/\/4GIeAAAAAAAAAAAAAAAAAAAAA4HjwAAAAAAAAAAA\/w\/\/4GIeAAAA\r\nAAAAAAAAAAAAAAAAA+PngAAAAAAAAAAAfwf\/wGIeAAAAAAAAAAAAAAAAAAAAAf\/HgAAAAAAAAAAA\r\nHwP\/gGIdAAAAAAAAAAAAAAAAAAAAAP+HAAAAAAAAAAAABwH\/YhEAAAAAAAAAAAAAAAAAAAAAfwYb\r\nKnJZMgBiNAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAADAYjQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nwGI0AAAAAAAAAAAAAAAAAOAAAAAAAAAAAAAAAAAAD\/\/\/4AAAAAAAAAAAABwAAAAAAAAAAAAAAGBi\r\nNAAAAAAAAAAAAAAAADH4AAAAAAAAAAAAAAAAAD\/\/\/+AAAAAAAAAAAAB8wAAAAAAAAAAAAAA4YjUA\r\nAAAAAAAAAAAAAABz\/AAD\/\/+AAAAAAAAAAAD\/\/\/\/gAAAAAAAAAAAA\/OAAAAAAAAAAAAAA\/\/BiKQAA\r\nAAAAAAAAAAAAAPP8AAP\/\/4AAAAAAAAAAAf\/\/\/+AAAAAAAAAAAAH84GIpAAAAAAAAAAAAAAAA45wA\r\nA\/\/\/gAAAAAAAAAAB\/\/\/\/4AAAAAAAAAAAAcxgYikAAAAAAAAAAAAAAAHnjgAA\/\/+AAAAAAAAAAAP\/\r\n\/\/\/gAAAAAAAAAAABjHBiNAAAAAAAAAAAAAAAAceOAABwAAAAAAAAAAAAA\/\/\/\/+AAAAAAAAAAAAGM\r\nYAAAAAAAAAAAAAACYjUAAAAAAAAAAAAAAAHHDgAAcAAAAAAAAAAAAAP\/\/\/\/gAAAAAAAAAAABzGAA\r\nAAAAAAAAAAAAPsBiNQAAAAAAAAAAAAAAAO8cAABwAAAAAAAAAAAAA\/gAAAAAAAAAAAAAAAH\/4AAA\r\nAAAAAAAAAAB+8GI1AAAAAAAAAAAAAAAA\/zwAAHAAAAAAAAAAAAAD+AAAAAAAAAAAAAAAAP\/AAAAA\r\nAAAAAAAAAOYwYjUAAAAAAAAAAAAAAAD+PAAAAAAAAAAAAAAAAAPwAAAAAAAAAAAAAAAAf4AAAAAA\r\nAAH\/4AAAxhBiNQAAAAAAAAAAAAAAAH44AAAAAAAAAAAAAAAAA\/AAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAP\/gAADGGGI1AAAAAAAAAAAAAAAAHDAAAAAAAAAAAAAAAAAD8AAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nYAAAAMYQYjUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHwAAAAAAAAAAAAAAAAAAAAAAAAAABg\r\nAAAAxjBiNQAAAAAAAAAAAAAAAeAAAAAAAAAAAAAAAAAAAfgAAAAAAAAAAAAAAAH\/4AAAAAAAAGAA\r\nAABmcGI1AAAAAAAAAAAAAAAB4AAAAAAAAAAAAAAAAAAA\/AAAAAAAAAAAAAAAAf\/gAAAAAAAAAAAA\r\nAH\/gYjUAAAAAAAAAAAAAAADgAAAAAAAAAAAAAAAAAAB\/AAAAAAAAAAAAAAAB\/+AAAAAAAAAAAAAA\r\nH4BiMAAAAAAAAAAAAAAAAHAAAAAAAAAAAAAAAAAAAD\/\/\/+AAAAAAAAAAAAAAwAAAAAAAAAdiMAAA\r\nAAAAAAAAAAAAADwAAAAAAAAA4AAAAAAAAH\/\/\/+AAAAAAAAAAAAAAYAAAAAAAAAdiNAAAAAAAAAAA\r\nAAAAAP\/8AAAAAAAB4APAAAAAAP\/\/\/+AAAAAAAAAAAAAAYAAAAAAAAAcAAACAYjQAAAAAAAAAAAAA\r\nAAD\/\/AAAAAAAAeMBwAAAAAH\/\/\/\/gAAAAQYQAAAAAAHAAAAAAAAAHAAAA8GI0AAAAAAAAAAAAAAAA\r\n\/\/wAA4AAAAHDIeAAAAAB\/\/\/\/4AAAACGEAAAAAAHgAAAAAAAABwAAAH5iNQAAAAAAAAAAAAAAAAAA\r\nAAPwAAABwyHgAAAAA\/\/\/\/+MM88888AAAAAH\/4AAAAAAAAAAAAAAPgGI1AAAAAAAAAAAAAAAAAAAA\r\nAD8AAAHDIeAAAAAD\/\/\/\/4ggxzzzwAAAAAf\/gAAAAAAAAAAAAAAHwYjUAAAAAAAAAAAAAAAAABAAA\r\nB+AAAeMjwAAAAAP9DDjjjjjjjjAAAAAB\/4AAAAAAAAH\/4AAAADBiNQAAAAAAAAAAAAAAAD\/8AAAA\r\nfgAB8yfAAAAAA\/hnnnnnnnnnnAAAAAAAAAAAAAAAAP\/gAAAB8GI1AAAAAAAAAAAAAAAA\/\/wAAAAP\r\ngAD\/\/4AAAEGP+eeeeeeeeeeUAAAAAAAAAAAAAAAAYAAAAA\/AYjQAAAAAAAAAAAAAAAD\/\/AAAAACA\r\nAH\/\/gAAAYcf8ceccccccccQAAAAAAAAAAAAAAABgAAAAfmI0AAAAAAAAAAAAAAAA\/\/gAAAAAAAA\/\r\n\/gAADzzz\/zzzzzzzzzzwAAAAAf\/\/AAAAAAAAYAAAAPBiNAAAAAAAAAAAAAAAAecYAAAAAAAAD\/gA\r\nAA888\/88888888888AAAAAH\/\/wAAAAAAAYAAAACAYjAAAAAAAAAAAAAAAAHHHAAAAAAAAAMgAAAD\r\njjn7jjjjjjjjjjgAAAAB\/\/8AAAAAAAHgYjQAAAAAAAAAAAAAAAHHDAAAAAAAAAEgAAAZ55755555\r\n555555wAAAAA4MAAAAAAAAHwAAAAwGI0AAAAAAAAAAAAAAAB4x4AAAAAAAAAAAAACeeefeeeeeee\r\neeecAAAAAcBgAAAAAAABvAAAAMBiNAAAAAAAAAAAAAAAAPO+AAAAAAAAAAAAAAx55\/\/\/\/\/5xxxxx\r\nxAAAAAGAYAAAAAAAAY\/gAABgYjQAAAAAAAAAAAAAAADz\/AAAAAAAAAAAAAAPPPP\/\/\/\/vPPPPPPAA\r\nAAABgHAAAAAAAAGD4AAAOGI1AAAAAAAAAAAAAAAAcfwAAAAAAAAAAAAADzzz\/\/\/\/7zzzzzzwAAAA\r\nAcDgAAAAAAABgGAAAP\/wYjAAAAAAAAAAAAAAAAAw+AAAAeQAAAAAAAADjj\/\/\/\/\/jjzjjjBAAAAAB\r\n\/+AAAAAAAAGAYjAAAAAAAAAAAAAAAAAAAAAAB+YAAAAAAAAJ55\/\/\/\/\/5555554QAAAAA\/8AAAAAA\r\nAAGAYjQAAAAAAAAAAAAAAAAAAAAAD+cAAAAAAAAJ55\/\/\/\/\/55554YQQAAAAAf4AAAAAAAAAAAAAA\r\nAmI1AAAAAAAAAAAAAAAAAAAAAB\/ngAAAAAAADHnH\/\/\/\/\/GGAAAAAAAAAAAAAAAAAAAAAAAAAAD7A\r\nYjUAAAAAAAAAAAAAAAAAAAAAHGOAAAAAAAAPPPP\/\/\/\/vPDCAAAAAAAAAAAAAAAAAAAH\/4AAAfvBi\r\nNQAAAAAAAAAAAAAAAD\/8AAAcYYAAA+AAAAc8888IIAAAAAAAAAAAABn\/4AAAAAAAAP\/gAADmMGI1\r\nAAAAAAAAAAAAAAAA\/\/wAADhhwAA\/\/gAAA8444wwAAAAAAAAAAAAAGf\/gAAAAAAAAYAAAAMYQYjUA\r\nAAAAAAAAAAAAAAD\/\/AAAOGHAAH\/\/gAAJ4YQAAAAAAAAAAAAAAAAZ\/+AAAAAAAABgAAAAxhhiNQAA\r\nAAAAAAAAAAAAAP\/8AAAcY4AA\/\/\/AAAhBAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGAAAADGEGI1AAAA\r\nAAAAAAAAAAAB4AAAAB9ngAHh58AAAAAD+AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMYwYjUAAAAA\r\nAAAAAAAAAAHgAAAAH\/+AAcDhwAAACAP4AAAAAAAAAAAAAAAAAAAAAAAAAAGAAAAAZnBiNQAAAAAA\r\nAAAAAAAAAOAAAAAP\/wABwHHgAAAAA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAeAAAAB\/4GI1AAAAAAAA\r\nAAAAAAAA4AAAAAP8AAHAceAAAAAD+AAAAAAAAAAAAAAAAYBgAAAAAAAB8AAAAB+AYjAAAAAAAAAA\r\nAAAAAAB\/\/AAAAAAAAcDx4AAAAAH4AAAAAAAAAAAAAAABgHAAAAAAAAG8YjEAAAAAAAAAAAAAAAB\/\r\n\/AAAAAAAAfHzwAAAAAH8AAAAAAAAAAAAAAAP\/+AAAAAAAAGP4GIxAAAAAAAAAAAAAAAA\/\/wAAAAA\r\nAAD\/48AAAAAA\/AAAAAAAAAAAAAAAD\/\/gAAAAAAABg+BiMQAAAAAAAAAAAAAAAP\/8AAAAAAAAf8OA\r\nAAAAAH4AAAAAAAAAAAAAAA\/\/4AAAAAAAAYBgYjUAAAAAAAAAAAAAAAHgAAAD\/\/+AAD+DAAAAAAA\/\r\nAAAAAAAAAAAAAAABgAAAAAAAAAGAAAAAIeBiNQAAAAAAAAAAAAAAAeAAAAP\/\/4AAAAAAAAAAA\/\/\/\r\n\/+AAAAAAAAAAAAGAAAAAAAAAAYAAAABj8GI1AAAAAAAAAAAAAAAA4AAAA\/\/\/gAAAAAAAAAAD\/\/\/\/\r\n4AAAAAAAAAAAAAAgAAAAAAAAAAAAAMMwYjUAAAAAAAAAAAAAAADgAAAD\/\/+AAAAAAAAAAAP\/\/\/\/g\r\nAAAAAAAAAAAA\/+AAAAAAAAAAAAAAxhBiNQAAAAAAAAAAAAAAAHAAAAAAAAAAAAAAAAAAA\/\/\/\/+AA\r\nAAAAAAAAAAH\/4AAAAAAAAf\/gAADGGGI1AAAAAAAAAAAAAAAA\/\/wAAAAAAAAAAAAAAAAD\/\/\/\/4AAA\r\nAAAAAAAAAf\/gAAAAAAAA\/+AAAMYQYjUAAAAAAAAAAAAAAAD\/\/AAAAAAAAf\/\/wAAAAAP\/\/\/\/gAAAA\r\nAAAAAAABmMAAAAAAAABgAAAAzDBiNQAAAAAAAAAAAAAAAP\/8AAAAAAAB\/\/\/AAAAAA\/\/\/\/+AAAAAA\r\nAAAAAAGMYAAAAAAAAGAAAAB88GI1AAAAAAAAAAAAAAAAAAAAA\/\/\/gAH\/\/8AAAAAD\/\/\/\/4AAAAAAA\r\nAAAAAYxgAAAAAAAAYAAAADjgYjAAAAAAAAAAAAAAAAAAAAAD\/\/+AAH\/\/wAAAAAAAAAAAAAAAAAAA\r\nAAABzHAAAAAAAAAwYjAAAAAAAAAAAAAAAAAAAAAD\/\/+AADgAAAAAAAAAAAAAAAAAAAAAAAAB7+AA\r\nAAAAAADxYjUAAAAAAAAAAAAAAAAAAAAD\/\/+AADgAAAAAAAAAAAAAAAAAAAAAAAAA5+AAAAAAAAD\/\r\nAAAAf\/BiNQAAAAAAAAAACAAAAAAAAAAAAAAAOAAAAAAAAAAAAAAAAAAAAAAAAABnwAAAAAAAAD\/w\r\nAAD\/8GI0AAAAAAAAAD\/4AAAAAAAAAAAAAAA4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAsXAA\r\nAMBiNAAAAAAAAAB\/+AAAAAAAAAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP0AAADA\r\nYjQAAAAAAAAAf\/gAAAAAAAAAB\/+AAAAAAAAAAAAA\/+AAAAAAAAAAAAAAP+AAAAAAAAA\/4AAAwGI0\r\nAAAAAAAAAGYwAAAAAAAAAB\/\/gAAAAAAAAAAAB\/\/4AAAAAAAAAAAAAP\/gAAAAAAAAMfAAAMBiNAAA\r\nAAAAAABjGAAAAAAAAAAf\/4AAAAAAAAAAAB\/\/\/gAAAAAAAAAAAAH\/4AAAAAAAADEAAABAYjQAAAAA\r\nAAAAYxgAAAADwAAAH\/8AAAAAAAAAAAA\/\/\/8AAAAAAAAAAAAB\/+AAAAAAAAAAAAAAOGI1AAAAAAAA\r\nAHMcAAAcD\/AAADzjAAAAAAAAAAAAf\/\/\/gAAAAAAAAAAAAYAAAAAAAAAAAAAAAP\/wYigAAAAAAAAA\r\ne\/gAABwf+AAAOOOAAAAAAAAAAAD\/\/\/\/AAAAAAAAAAAABgGIoAAAAAAAAADn4AAAcP\/wAADjhgAAA\r\nAAAAAAAA\/\/\/\/wAAAAAAAAAAAAcBiMAAAAAAAAAAZ8AAAHHw8AAA8Y8AAAAAAAAAAAf\/A\/+AAAAAA\r\nAAAAAAD\/4AAAAAAAAeBiNQAAAAAAAAAAAAAAHHgcAAAed8AAAAAAAAAAAf4AH+AAAAAAAAAAAAD\/\r\n4AAAAAAAABwAAAA\/wGI1AAAAAAAAAAAAAAAccB4AAB5\/gAGMAAAAAAAD\/AAP8AAAAAAAAAAAAf\/g\r\nAAAAAAAAA8AAAH\/gYjUAAAAAAAAH\/\/gAABxwHgAADj+AAcwAAAAAAAP4AAfwAAAAAAAAAAAB\/+AA\r\nAAAAAAAAIAAA4DBiNQAAAAAAAAf\/+AAAHDgcAAAGHwAB\/\/\/AAAAAA\/AAA\/AAAAAAAAAAAAGAAAAA\r\nAAAAAAAAAADAMGI1AAAAAAAAB\/\/4AAAf\/DwAAAAAAAH\/\/8AAAAAD8AAD8AAAAAAAAAAAAYAAAAAA\r\nAAAAAAAAAMAYYjUAAAAAAAAAAAAAAB\/8fAAAAAAAAf\/\/wAAAAAPwAAPwAAAIAAAAAAABwAAAAAAA\r\nAAAAAAAAwBBiNQAAAAAAAAAAAAAAA\/x4AAAAAAAAf\/\/AAAAAA\/AAA\/AAAIIAAAAAAAD\/4AAAAAAA\r\nAYAAAADAMGI1AA\/\/\/\/\/+AAAIAAAADHAAAAB4AAAMAAAAAAAD8AAD8AAAAgAAAAAAAf\/gAAAAAAAB\r\n4AAAAOBwYjUAD\/\/\/\/\/4AP\/gAAAAAAAAAcP4AAAwAAAAAAAPwAAPwAAADAAAAAAAB\/+AAAAAAAAHw\r\nAAAAf+BiNQAP\/\/\/\/\/gB\/+AAAAAAAAAHx\/wAAAAAAAAAAA\/hAB\/AAABlAAAAAAAH\/4AAAAAAAAbwA\r\nAAAfwGIxAA4AAAAOAH\/4AAAAfwAAA\/P\/gAAAAAAAAAAD\/EAP8AAAGeAAAAAAAAAAAAAAAAABj+Bi\r\nMQAOAAAADgBmMAAAA\/\/wAAPzx4ABz\/\/AAAAAAf5gH+AAABxgAAAAAAHAAAAAAAAAAYPgYjEADgAA\r\nAA4AYxgAAA\/\/+AADg8OAAc\/\/wAAAAAH\/4f\/gAAAPPAAAAAABwAAAAAAAAAGAYGI1AA4AAAAOAGMY\r\nAAAP\/\/wAB4ODwAHP\/8AAAAAA\/\/\/\/wAAABxwAAAAAAMAAAAAAAAABgAAAAACAYjUADgAAAA4AcxwA\r\nAB4APAAHg4PAAc\/\/wAAAAAD\/\/\/\/AAAADjgAAAAAB\/+AAAAAAAAGAAAAAcOBiNQAOAAAADgB7+AAA\r\nHAAeAAcHg8AAAAAAAAAABH\/\/\/4AAABnngAAAAAH\/4AAAAAAAAAEAAABgcGI1AA4AAAAOADn4AAAc\r\nAB4AB4eDwAAAAAAAAAAAP\/\/\/AAAAGeeAAAAAAf\/gAAAAAAAAAQAAAMAwYjUADgAAAA4AGfAAABwA\r\nHgAHhwOAAAAAAAAAAAAf\/\/4AAAAMccAAAAAB\/+AAAAAAAAH\/4AAAwBBiNQAOAAAADgAAAAAAHgAc\r\nAAOPB4AAHgAAAAAAAc\/\/+AAAAA888AAAAAAAAAAAAAAAAf\/gAADAEGI1AA4AAAAOAAAAAAAf\/\/wA\r\nA\/8fgAAeAAAAAAAAz\/\/AAAAAAzzwAAAAAAAAAAAAAAAAwQAAAMAwYjUADgAACA4AAAAAAA\/\/+AAB\r\n\/h8AAA4AAAAAAADjgAAAAAADjjgAAAAAAAAAAAAAAABxAAAA4DBiNQAOAB\/4DgAAAAAAB\/\/wAAH+\r\nHgAABwAAAAAABnngAAAAABnnngAAAAAeAAAAAAAAAB0AAAB\/4GI1AA4AP\/gOAM\/4AAAB\/8AAAHgc\r\nAAADwAAAAAAGeeAAAAAACeeeAAAAAH+AAAAAAAAABwAAAB\/AYjAADgBiMA4Dz\/gAAAAAAAAAAAAA\r\nAA\/\/wAAAAAf8YAAAAAAIcccAAAAA\/8AAAAAAAAADYikADgBjGA4Dz+AAAAAAAAAAAAAAAA\/\/wAAA\r\nD\/P\/OAAAAAAPPPPAAAAB8+BiKQAOAGMIDgeOcAAAAAAAAAAAAAAAD\/\/AAAAP8\/8oAAAAAAM884AA\r\nAAHA4GIxAA4AYwgOBw44AAAAAAAAAAAAAAAAAAAAAA\/584gAAAAAA844wAAAAYBgAAAAAAAAOGBi\r\nMQAOAGMMDg4OOAAAAAAAAAAAAAAAAAAAAAAP\/\/ngAAAAAAHnnlAAAAGAcAAAAAAAADhgYikADgBx\r\nmA4ODhgAAAAAAAAAAAAAAAAAQAAAD\/\/\/\/\/\/gAAAAZ55QAAABwGBiNQAOADn4Dg4AHAAAAAAAAAAA\r\nAAAAA\/\/AAAAP\/\/\/\/\/+AAAABxxxAAAAHg4AAAAAAAAAAAAAAAEGI1AA4ACPAODgA4AAAAAAAAAAAA\r\nAAAP\/8AAAA\/\/\/\/\/\/4AAAADzzwgAAAf\/gAAAAAAAAz8AAAABwYjUADgAAAA4HADgAAAAAAAAAAAAA\r\nAA\/\/wAAAB\/\/\/\/\/\/gAAAADPPAAAAA\/8AAAAAAAAHP4AAAA\/BiNQAOAGAIDgeAeAAAAAAAAAAB5AAA\r\nD\/+AAAAH\/\/\/\/\/+AAAAAPPOAAAAA\/AAAAAAAAAZhgAAAfgGI0AA4AYAwOA+PwAAAAAAAAAAfmAAAe\r\ncYAAAAP\/\/\/\/\/4AAAAAeeeAAAAAAAAAAAAAABmGAAAP5iNAAOA\/\/4DgP\/4AAAHP\/8AAAP5wAAHHHA\r\nAAAB\/\/\/\/\/+AAAAADnnAAAAAAAAAAAAAAAcxgAAf2YjQADgP\/+A4B\/8AAABz\/\/AAAH+eAABxwwAAA\r\nAcf4AAAAAAAAAceYAAAZgAAAAAAAAAD\/4ABPhmI0AA4AYAAOAH8AAAAc\/\/wAABxjgAAeMeAAAAzz\r\n+gAAAAAAAADzzgAAGYAAAAAAAAAAf8AAzAZiNAAOAGAADgAAAAAAHP\/8AAAcYYAADzvgAAAM8\/AA\r\nAAAAAAAA88IAAB\/\/4AAAAAAAAAAAAY+GYjQADgAACA4AAAAAAAAAAAAAOGHAAA8\/wAAADjnwAAAA\r\nAAAAADjiAAAf\/+AAAAAAAAAAAAED9mI0AA4AH\/gOAAAAAAAAAAAAADhhwAAHH8AAAAeeeAAAAAAA\r\nAAGeeAAAD\/\/gAAAAAAAAAAAAAP5iNQAOAD\/4DgAAAAAAAAAAAAAcY4AAAw+AAAADn\/gAAAAAAAAA\r\nnngAAAGAAAAAAAAAAf\/gAAAfgGI1AA4AYjAOAAAAAAAf\/\/wAAB9ngAAAAAAAAAHH+AAAAAAAAADH\r\nHAAAAYAAAAAAAAAA\/+AAAAPwYjUADgBjGA4AAAAAAB\/\/\/AAAH\/+AAAwAwAAADPP4AAAAAAAAAPPP\r\nAAAAAAAAAAAAAABgAAAAAHBiMAAOAGMIDgAAAAAAH\/\/8AAAP\/wAADADgAAAM8\/gAAAAAAAAAM84A\r\nAAAAAAAAAAAAAGBiMAAOAGMIDgAAwAAAH\/\/8AAAD\/AAA\/\/\/gAAAPOfgAAAAAAAAAOOMAAAD\/4AAA\r\nAAAAAGBiKQAOAGMMDgA58AAAAHh4AAAAAAAA\/\/\/AAAAnn\/wAAAAAAAAAnnmAAAH\/4GIpAA4AcZgO\r\nAHv4AAAA4BwAAAAAAAD\/\/8AAAAee\/AAAAAAAAAAeeAAAAf\/gYigADgA5+A4Ae\/gAAADgHAAD\/\/+A\r\nAP\/\/gAAAIcd+AAAAAAAAAIccAAABwGIoAA4ACPAOAGOYAAAB4B4AA\/\/\/gAAMAAAAAAzz\/wAAAAAA\r\nAAAzzwAAAYBiKAAOAAAADgBjnAAAAeAeAAP\/\/4AADAAAAAAM8\/\/\/\/+AAAAAAM84AAAGAYjEADgAA\r\nAA4AZxgAAADwPAAD\/\/+AAAAAAAAADjv\/\/\/\/gAAAAADjjAAAAwAAAAAAAAADPwGI1AA4AAEAOAHcY\r\nAAAA\/\/wAAA8PAAAAAAAAACef\/\/\/\/4AAAAACeeYAAAf\/gAAAAAAABz+AADB\/AYjUADgA4cA4Af3gA\r\nAAB\/\/AAAHAOAAAAAAAAAB5\/\/\/\/\/gAAAAAB54AAAB\/+AAAAAAAAGYYAAMP+BiNQAOADA4DgA+eAAA\r\nAD\/4AAAcA4AAAAAAAAABx\/\/\/\/+AAAAAAhxwAAAH\/4AAAAAAAAZhgAAwwMGI1AA4AYBgOABxwAAAA\r\nH+AAADwDwAAAAAAAAAzz\/\/\/\/4AAAAADzzwAAAf\/gAAAAAAABzGAADDAwYjUADgBgCA4AAAAAAAAA\r\nAAAAPAPAAAAAAAAADPP\/\/\/\/gAAAAAHPOAAAAAAAAAAAAAAD\/4AAMYBhiNQAOAGAIDgAAAAAAAAAA\r\nAAAeB4AAAAAAAAAOO\/\/\/\/+AAAAAAOOMAAAAAAAAAAAAAAH\/AAAwgEGI1AA4AYBgOAAcAAAAADyAA\r\nAB\/\/gAAAAAAAAAeeeAAAAAAAAAGeeAAAH\/\/gAAAAAAAAAAAADDAwYjUADgBwGA4AHzAAAAA\/MAAA\r\nD\/+AAAAAAAAAB55wAAAAAAAAAZ54AAAf\/+AAAAAAAAAAAAAP8DBiNQAOAD\/wDgA\/OAAAAH84AAAH\r\n\/wAAAAAAAAAB5xgAAAAAAAABxxwAAB\/\/4AAAAAAAAAAAAA\/44GI1AA4AD+AOAH84AAAA\/zwAAAP8\r\nAAAA\/8AAAAzzyAAAAAAAAADzzgAAH\/\/gAAAAAAAAAAAAABjAYjEADgAAAA4AcxgAAADjHAAAAAAA\r\nAAf\/wAAACPPAfgAAAAAACPPCAAAAAAAAAAAAAAH\/4GIxAA4AAAAOAGMcAAAA4wwAAAAAAAAP\/8AA\r\nAAw45\/4cAAAAAAI48gAAAAAAAAAAAAAA\/+BiMAAOAAAADgBjGAAAAcMOAAAAAAAAD\/\/AAAABnn\/+\r\nHgAAAABHnngAAAAAAAAAAAAAAGBiNQAOBn\/4DgBzGAAAAcMOAAAB\/4AAHwAAAAABnn\/+HwAAAAAD\r\nnnAAAAAAAAAAAAAAAGAAAAMPwGI1AA4Gf\/gOAH\/4AAAA4xwAAA\/\/gAAeAAAAAAHHf\/4fgAAAACHH\r\nEAAAAAAAAAAAAAAAYAAABx\/wYjUADgAAAA4AP\/AAAAD7PAAAH\/+AAB4AAAAAAPP\/\/h\/AAAAADPPC\r\nAAAAAAAAAAAAAAAAAAAOODBiNQAOAAAADgAf4AAAAP\/8AAAf\/4AADgAAAAAAc\/\/+H8AAAAAc88AA\r\nAAAAAAAAAAAAAH\/AAAwwEGI1AA4H\/\/gOAAAAAAAAf\/gAAD4AAAAOAAAAAAA5\/\/4f4AAAAA44wAAA\r\nAAAAAAAAAAAA\/+AADDAYYjUADgAAAA4ABwAAAAAf4AAAPAAAAAOAAAAAAR\/\/\/g\/gAAAA555QAAAA\r\nHAAAAAAAAAGAYAAMMBBiNQAOAAAADgAfMAAAAAAAAAA8AAAAD\/\/AAAABH\/n+B\/AAAADnnhAAAAB8\r\nwAAAAAAAAYBgAAYQMGI1AA4AB4AOAD84AAAAAAAAABwAAAAP\/8AAAAAH\/H4D8AAACHHHAAAAAPzg\r\nAAAAAAABgGAABxxwYjUADgAf8A4IfzgAAAAAAAAAHAAAAA\/\/wAAAADP\/PgPwAAADPPPAAAAB\/OAA\r\nAAAAAAD\/4AAD\/+BiNQAOADh4DgxzGAAAAD\/8AAAHAAAAAAAAAAAAI\/8+I\/AAAI888IAAAAHMYAAA\r\nAAAAAH\/AAAD\/gGIpAA4AYBgODmMcAAAA\/\/wAAB\/\/gAAAAAAAAAAX8\/4B8AAAA444AAAAAYxwYjEA\r\nDgBgCA4CYxgAAAD\/\/AAAH\/+AAAAAAAAAAAf5\/4\/wAAZ5554AAAABjGAAAAAAAAD4YGIxAA4AYAwO\r\nAHMYAAAA\/\/wAAB\/\/gAAAAAAAAAAH+f+H8AAGOeecAAAAAcxgAAAAAAAA\/GBiMQAOAGAIDgB\/+AAA\r\nAeAAAAAAAAABz\/\/AAAAAB\/x\/x\/gBhxxxxAAAAAH\/4AAAAAAAAYZgYjEADgBgGA4AP\/AAAAHgAAAA\r\nAAAAAc\/\/wAAAAAP\/PvfzDPPPPPAAAAAA\/8AAAAAAAAGHYGI1AA4AODAOAB\/gAAAA4AAAAAAAgAHP\r\n\/8AAAAAB\/z7\/7zzzzzzgAAAAAH+AAAAAAAABg+AAD\/\/wYjUADgB\/\/84AAAAAAADgAAAAB\/+AAc\/\/\r\nwAAAAAH\/\/\/\/jjjjjjzAAAAAAAAAAAAAAAADh4AAP\/\/BiNAAOAH\/\/zgBwAAAAAH\/8AAAf\/4AAAAAA\r\nAAAAAP\/\/\/\/nnnnnngAAAAAHAAAAAAAAAAODgAAOAYjQADgAAAA4AcAAAAAB\/\/AAAH\/+AAAAAAAAA\r\nAAD\/\/\/\/555555wAAAAABwAAAAAAAAAAAAAABgGI0AA4AAAAOADAAAAAA\/\/wAAB\/\/AAAA8gAAAAAA\r\nf\/\/\/nHHHHHEAAAAAAMAAAAAAAAAB4AAAAYBiNAAOAH\/4DgB\/+AAAAP\/8AAA84wAAA\/MAAAAAAD\/\/\r\n\/8888888AAAAAAH\/4AAAAAAAABwAAAGAYjEADgB\/+A4Af\/gAAAHgAAAAOOOAAAfzgAAAAAAP\/\/\/P\r\nPPPPGAAAAAAB\/+AAAAAAAAADwGIxAA4AADAOAH\/4AAAB4AAAADjhgAAP88AAAAAAA\/\/4444444wA\r\nAAAAAf\/gAAAAAAAAACBiNAAOAAAYDgB\/+AAAAOAAAAA8Y8AADjHAAAAAAAB\/nnnnnnnAAAAAAAH\/\r\n4AAAAAAAAPhgAAABYjUADgAACA4AAAAAAADgAAAAHnfAAA4wwAAAAAAAZ5555555QAAAAAAAAAAA\r\nAAAAAAD8YAADx+BiNQAOAAAMDgAAAAAAAHAAAAAef4AAHDDgAAAAAAABxxxxxxgAAAAAAAAAAAAA\r\nAAAAAYZgAAfu8GI1AA4AAAgOBn\/4AAAA\/\/wAAA4\/gAAcMOAAAAADwAzzzzzzzwAAAAAAAD8AAAAA\r\nAAABh2AABjgwYjUADgAAGA4Gf\/gAAAD\/\/AAABh8AAA4xwAAAAD\/8ADHPPPCAAAAAAAAA\/8AAAAAA\r\nAAGD4AAMGBBiNQAOAH\/4DgZ\/+AAAAP\/8AAAAAAAAD7PAAAAAf\/8AMOOPOMAAAAAAAAD\/4AAAAAAA\r\nAOHgAAwQGGI1AA4Af+AOAAAAAAAAAAAAADwAAAAP\/8AAAAH\/\/8GeeeeeEAAAAAAAAeHgAAAAAAAA\r\n4OAADBgQYjUADgAAAA4AAAAAAAAAAAAAPAAAAAf\/gAAAAf\/\/4AQYYQQAAAAAAAABwOAAAAAAAAAA\r\nAAAMODBiNQAOAAAADgAAAAAAAAAAAAAcAAAAAf4AAAAD\/\/\/gABhxgAAAAAAAAAGAYAAAAAAAAH\/A\r\nAAd8MGI1AA4AAAAOAAAAAAAAAAQAAA4AAAAAAAAAAAf\/\/\/AAAAAAAAAAAAAAAYBgAAAAAAAA\/+AA\r\nB+\/gYjUADgB\/AA4AB4AAAAA\/\/AAAB4AAAAAAAAAAB\/\/\/8AAAAAAAAAAAAAABwOAAAAAAAAGAYAAA\r\nh8BiMQAOAf\/ADgAf4AAAAP\/8AAAf\/4AAAAAAAAAH\/z\/4AAAAAAAAAAAAAAD\/wAAAAAAAAYBgYjEA\r\nDgPA8A4AP\/AAAAD\/\/AAAH\/+AAf\/\/wAAAB\/gH+AAAAAAAAAAAAAAf\/+AAAAAAAAGAYGIxAA4HADAO\r\nAHz4AAAA\/\/gAAB\/\/gAH\/\/8AAAA\/wA\/gAAAAAAAAAAAAAH\/\/gAAAAAAAA\/+BiNQAOBgAYDgBwOAAA\r\nAecYAAAAAAAB\/\/\/AAAAP4AP4AAAAAAAAAAAAAB\/\/4AAAAAAAAH\/AAAPwMGI1AA4GABgOAGAYAAAB\r\nxxwAAAAAAAH\/\/8AAAA\/gA\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/gwYjUADgQAGA4AYBwAAAHH\r\nDAAAAAAAAAAAAAAAD+AD+AAAAAAAAAAAAAAAAAAAAAAAAAHgAAAOGDBiNQAOBAAYDgBwGAAAAeMe\r\nAAAAAAAAAAAAAAAP4AH4AAAAAAAAAAAAAAAAAAAAAAAAABwAAAwMMGI1AA4EABgOAHg4AAAA874A\r\nADH\/gAAAAAAAAA\/gAfgAAAAAAAAAAAAAGf\/gAAAAAAAAA8AADAwwYjUADgQAGA4Af\/gAAADz\/AAA\r\n8f+AAAAAAAAAD+AB+AAAAAAAAAAAAAAZ\/+AAAAAAAAAAIAAMBjBiNQAOBAAYDgA\/8AAAAHH8AAHx\r\n\/4AAHgAAAAAP4AH4AAAAAAAAAAAAABn\/4AAAAAAAAAAAAAwHMGI1AA4H\/\/gOAA\/AAAAAMPgAAfH+\r\nAAD\/gAAAAA\/gAfgAAAAAAAAAAAAAAAAAAAAAAAAA98AABwOwYjUADgf\/+A4AAAAAAAAAAAADwc8A\r\nAP\/AAAAAD+AB+AAAAAAAAAAAAAAAAAAAAAAAAAD\/4AAHwfBiNQAOAAAADgDD4AAAAADgAAPBx4AB\r\n\/+AAAAAP4AH4AAAAAAAAAAAAAAAAAAAAAAAAAdxgAAHAcGIxAA4AAAAOA8fwAAAAMfgAB4HDgAHj\r\n4AAAAA\/gAfgAAAAAAAAAAAAAAADgAAAAAAABjGBiNAAOAAAADgfP+AAAAHP8AAeBw8ABweAAAAAP\r\n\/\/\/\/\/+AAAAAAAAAAAAAA4AAAAAAAAYBgAABAYjQADgAAAA4HzjgAAADz\/AAHgAPAAcDgAAAAD\/\/\/\r\n\/\/\/gAAAAAAAAAAAAAOAAAAAAAADg4AAOQ2I0AA4AAAAOBw44AAAA45wAB4ADwAHA4AAAAA\/\/\/\/\/\/\r\n4AAAAAAAAAAAAADgAAAAAAAA4MAAD\/NiNQAOAAAADg4cGAAAAeeOAAeAA4ABwOAAAAAP\/\/\/\/\/+AA\r\nAAAAAAAAAAAA4AAAAAAAAAAAAAD\/wGI1AA4AAAAODhwcAAABx44AA8AHgAHA4AAAAA\/\/\/\/\/\/4AAA\r\nAAAAAAAAAADgAAAAAAAA+GAAAEPwYjUADgAAAA4OHBgAAAHHDgAD4A+AAcDgAAAAD\/\/\/\/\/\/gAAAA\r\nAAAAAAAAAOAAAAAAAAD8YAAAQxBiNAAOAAAADg4cOAAAAO8cAAH4PwAB\/\/\/AAAAP\/\/\/\/\/+AAAAAA\r\nAAAAAAAA4AAAAAAAAYZgAA\/DYjQADgAAAA4HPDgAAAD\/PAAB\/\/8AAf\/\/wAAAD\/\/\/\/\/\/gAAAAAAAA\r\nAAAf\/+AAAAAAAAGHYAAH\/2I1AA4AAAAOB\/j4AAAA\/jwAAP\/+AAH\/\/8AAAAAAAAAAAAAAAAAAAAAA\r\nH\/\/gAAAAAAABg+AAAF\/wYjUAD\/\/\/\/\/4D+PAAAAB+OAAAP\/gAAf\/\/wAAAAAAAAAAAAAAAAAAAAAAf\r\n\/+AAAAAAAADh4AAAQ\/BiNAAP\/\/\/\/\/gHw4AAAABwwAAAPwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAODgAABDYjQAD\/\/\/\/\/4AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAxsqclk4AGIlAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgwhi\r\nJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIIIYiUAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAADjiGIlAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAOeeBiJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABhngYiUAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIAAAcccGIlAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAgwgAADzzxiJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIIAAAAMM4AAA884YiUAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAADjhAAADjjGIlAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAOeeAAACeeAAAOeeJiJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABjngAAAnngAABnng\r\nYiUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAccYAAAIccAAAcccGIlAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAATzygAADzzwAADzzxiJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA88oAAAM\r\n84AAA888YiUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABDjiAAADjjAAADjjGIlAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAOeeAAACeeQAAGeeZiJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nBnngAAAHngAABnngYiUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAecYAAAIccAAAMccGIlAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAATzzgAAAzzwAADzzxiJQAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAA88oAAAM88AAAc88YiUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADjiAAADjjAAADj\r\njmIlAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGeeAAAAeeQAACeeZiJQAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAABnngAAAHngAAAnnmYiUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAcccAAA\r\nAccAAAMccGIlAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADzzgAAAzzwAADzzxiJQAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAA884AAAM88AAAM88YiUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAADjiAAADjjAAADjjmIlAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGeeAAAAeeUAACeediJQAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABnngAAAHnkAAAnnnYiUAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAcccAAAAccAAAIceGIlAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADzzgAAAzzwAAD\r\nzzxiJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAc84AAAM88AAAM88YiUAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAADjiAAADjjAAADjjmImAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGeeAA\r\nAAeeUAGGeeeAYiUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAZ54AAAA55QAQZ552ImAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAHHHAAAAHHnHHHnHGAYiYAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAPPPAAADPPPPPPPPIBiJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAc84IMc888888888\r\nYiUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADjjBDjzjzjzjjjjmImAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAGeeeeeeeeeeeeeeeAYiYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAR55555\r\n5555555554BiJgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABxxxxxxx5xxxxxxxgGImAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAABzzzzzzzzzzzzzzyAYiYAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAHPPPPPPPPPPPPPPIBiJQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAPOOOPOOOOOOOOOO\r\nYiYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAZ555555555555554BiJgAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAABHnnnnnnnnnnnnnngGImAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABHHHH\r\nHHHHHHHHHHmAYiYAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAHPPPPPPPPPPPPPPKBiJQAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAc88888888888888YiQAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAADjjjjjjjjjjjDAYiQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB555555555554YYiEA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB5555554QQYiAAD\/\/\/\/\/4AAIAAAAAAAAAAAAAAAAAA\r\nAAAAAAEcccccccZiJAAP\/\/\/\/\/gD\/gAAAAAPAAAADgAAAAAAAAABwAc888MIAAAAAAAdiMQAP\/\/\/\/\r\n\/gH\/gAAAAD\/8AAAHgDgAAAAAAABwAc88IIIAAAAAAB8wAAAAAAAA\/\/AABg\/gYjEADgAAAA4B9wAA\r\nAAB\/\/gAABxA4AAAAAAAAcACAAAAAAAAAAAA\/OAAAAAAAAH\/wAAYf8GIxAA4AAAAOAZmAAAAA\/\/8A\r\nAAcSGAAAAAAAAHAAGAAAAAAAAAAAfzgAAAAAAAAwAAAGGBhiMQAOAAAADgGZgAAAAOAHAAAHEhgA\r\nAAAAAAAAAAAAAAAAAAAAAHMYAAAAAAAAEAAABhgYYjEADgAAAA4B2YAAAADAA4AABxI4AAAAAAAA\r\nAAAAAAAAAAAAAABjHAAAAAAAAAAAAAYwDGIxAA4AAAAOAc+AAAAAwAMAAAeSeAAAAAAAAAAAAAAA\r\nAAAAAAAAYxgAAAAAAAAAAAAGEAhiMQAOAAAADgDPgAAAAOAHAAAD\/\/AAAAAAAABwAAAAAAAAAAAA\r\nAHMYAAAAAAAAAAAABhgYYjEADgAAAA4AAAAAAAD\/\/wAAAf\/gAAAAAAAAcAAAAAAAAAAAAAB\/+AAA\r\nAAAAAAAAAAf4GGIxAA4AAAAOAAAAAAAAf\/4AAAD\/wAAAAAAAAHAAAAAAAAAAAAAAP\/AAAAAAAAAB\r\nAAAH\/HBiMQAOAAAADgAAAAAAAB\/8AAAAEgAAAAAAAABwAAAAAAAAAAAAAB\/gAAAAAAAAAQAAAAxg\r\nYiwADgAAAA4AAAAAAAAAAAAAABIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFiLAAOAAAIDg\/\/\r\ngAAAAAAwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAWIxAA4AH\/gOD\/+AAAAAPP4AAAAA\r\nAAAAAAAAAHAAAAAAAAAAAAAAAAAAAAAAAAAAAAABh+BiMQAOAD\/4Dg\/\/gAAAAH7\/AAAAAAAAAAAA\r\nAABwAAAAAAAAAAAAAH\/4AAAAAAAAAAAAA4\/4YjEADgBiMA4AAAAAAAD\/\/wAAAAAAAAAAAAAAcAAA\r\nAAAAAAAAAAB\/+AAAAAAAAAAAAAccGGIxAA4AYxgOAACAAAAA44cAAAAAAAAAAAAAAHAAAAAAAAAA\r\nAAAAf\/gAAAAAAAD\/8AAGGAhiMQAOAGMIDgD\/gAAAAMGDgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAw\r\nAAAAAAAAf\/AABhgMYjEADgBjCA4B\/4AAAADBgwAAAB4AAAAAAAAAAAAAAAAAAAAAAAAAGAAAAAAA\r\nADAAAAYYCGIxAA4AYwwOAfcAAAAA4AcAAAH\/4AAAAAAAAAAAAAAAAAAAAAAAABgAAAAAAAAQAAAD\r\nCBhiMQAOAHGYDgGZgAAAAPgfAAAD\/\/AAAAAAAAAAAAAAAAAAAAAAAAAcAAAAAAAAAAAAA444YjEA\r\nDgA5+A4BmYAAAAB4HgAAB\/\/4AAAAAAAAAAAAAAAAAAAAAAAAeAAAAAAAAMAAAAH\/8GIxAA4ACPAO\r\nAdmAAAAAOBwAAAcGOAAAAAAAAAAAAAAAAAAAAAAAf\/gAAAAAAADgAAAAf8BiLAAOAAAADgHPgAAA\r\nAAAAAAAGBxgAAAAAAAAAAAAAAAAAAAAAAH\/4AAAAAAAA2GIsAA4AYAgOAM+AAAAAAAAAAAYHHAAA\r\nAAAADgAAAAAAAAAAAAAAf+AAAAAAAADMYi0ADgBgDA4AAAAAAAAAAAAABwc4AAAAAAA+YAAAAAAA\r\nAAAAAAAAAAAAAAAAAMOAYi0ADgP\/+A4AAAAAAAAPBwAAB\/54AAAAAAB+cAAAAAAAAAAAAAAAAAAA\r\nAAAAAMDwYi0ADgP\/+A4DP4AAAAAPBwAAA\/54AAAAAAD+cAAAAAAAAAAAAAAAAAAAAAAAAMAQYjEA\r\nDgBgAA4HP4AAAAAPBwAAAfxgAAAAAADmMAAAAAAAAAAAAAB\/\/4AAAAAAAMAAAAf\/+GIxAA4AYAAO\r\nDzcAAAAAAAAAAAAAAAAAAAAAxjgAAAAAAAAAAAAAf\/+AAAAAAAAAAAAH\/\/hiMAAOAAAIDgwzgAAA\r\nAAAAAAAAAAAAAAAAAMYwAAAAAAAAAAAAAH\/\/gAAAAAAAAAAAAcBiMAAOAB\/4DhwxgAAAAAAAAAAA\r\nAAAAAAAAAOYwAAAAAAAAAAAAADgwAAAAAAAAAAAAAMBiMAAOAD\/4DhwBgAAAAAPAAAAAAAAAAAAA\r\nAP\/wAAAAAAAAAAAAAHAYAAAAAAAA\/\/AAAMBiMAAOAGIwDhwBgAAAAD\/8AAAH\/\/gAAAAAAH\/gAAAA\r\nAAAAAAAAAGAYAAAAAAAAf\/AAAMBiLAAOAGMYDgwBgAAAAH\/+AAAH\/\/gAAAAAAD\/AAAAAAAAAAAAA\r\nAGAcAAAAAAAAMGIsAA4AYwgODgOAAAAA\/\/8AAAf\/+AAAAAAAAAAAAAAAAAAAAAAAcDgAAAAAAAAQ\r\nYjEADgBjCA4P\/wAAAADgBwAAAf\/4AAAAAAABgAAAAAAAAAAAAAB\/+AAAAAAAAAAAAAAAgGIxAA4A\r\nYwwOB\/4AAAAAwAOAAAHAAAAAAAAAc+AAAAAAAAAAAAAAP\/AAAAAAAADAAAAB4\/BiMQAOAHGYDgH8\r\nAAAAAMADAAAAwAAAAAAAAPfwAAAAAAAAAAAAAB\/gAAAAAAAA4AAAA\/d4YjEADgA5+A4AAAAAAADg\r\nBwAAAMAAAAAAAAD38AAAAAAAAAAAAAAAAAAAAAAAANgAAAMcGGIxAA4ACPAOAAAAAAAA\/\/8AAAAA\r\nAAAAAAAAxzAAAAAAAAAAAAAAAAAAAAAAAADMAAAGDAhiNQAOAAAADgAAAAAAAH\/+AAAAAAAAAAAA\r\nAMc4AAAAAAAAAAAABn\/4AAAAAAAAw4AABggMAAAAIGI1AA4AAAAOAAAAAAAAH\/wAAAAAAAAAAAAA\r\nzjAAAAAAAAAAAAAGf\/gAAAAAAADA8AAGDAgAAAPsYjUADgAAAA4AAAAAAAAAAAAAAAAAAAAAAADu\r\nMAAAAACDDCAAAAZ\/+AAAAAAAAMAQAAYcGAAAB+9iNQAOAABADgAAAAAAAAAAAAAAAAAAAAAAAP7w\r\nAAAAAEOMAAAAAAAAAAAAAAAAwAAAA74YAAAOY2I1AA4AOHAOAAAAAAAADAcAAAAAAAAAAAAAfPAA\r\nAAEGeeeeAAAAAAAAAAAAAAAAAAAD9\/AAAAxhYjYADgAwOA4AAAAAAAA\/BwAAAAAAAAAAAAA44AAA\r\nAAY554QAAAAAAAAAAAAAAAAAAABD4AAADGGAYjUADgBgGA4ABAAAAAB\/hwAAAAAAAAAAAAAAAAAA\r\nAMcececAAAAAAAAAAAAAAAAAAAAAAAAADGFiNQAOAGAIDgDPAAAAAP\/HAAAAAAAAAAAAAAAAAAAA\r\nc88888AAAGAYAAAAAAAAAAAAAAAAAAAMY2I1AA4AYAgOAc+AAAAA4ecAAAYAAAAAAAAAAAAAAADz\r\nzzzzwAAAYBwAAAAAAAD\/8AAAAAAAAAZnYjUADgBgGA4B3YAAAADA5wAAA+AAAAAAAAAAAAAABDzj\r\njjzAAAP\/+AAAAAAAAH\/wAAH4GAAAB\/5iNQAOAHAYDgGZgAAAAMB3AAAAfAAAAAAAAH\/wAAAHnnnn\r\nnngAA\/\/4AAAAAAAAMAAAA\/wYAAAB+GIxAA4AP\/AOAZmAAAAA4D8AAAAHwAAAAAAA\/\/AAAAeeeeee\r\neAAD\/\/gAAAAAAAAQAAAHDBhiMQAOAA\/gDgH5gAAAAPw\/AAAAAPgAAAAAAP\/wAABxx555xxwAAGAA\r\nAAAAAAAAAAAABgYYYjEADgAAAA4B84AAAAB8HwAAAAAIAAAAAADgAAAAPPPPPPPPAABgAAAAAAAA\r\nAAgAAAYGGGI0AA4AAAAOAPMAAAAAPAcAAAAAAAAAAAAAwAAAABzzzzzzzwAAAAgAAAAAAABogAAG\r\nAxgAAAxiNAAOBn\/4DgAAAAAAAAAAAAAAAAAAAAAAAMAAAAEPPOOPOOMAAD\/4AAAAAAAAP4AABgOY\r\nAAAMYjQADgZ\/+A4AGAAAAAAAAAAAAAAAAAAAAABgAAAB55555555wAB\/+AAAAAAAAAnwAAOB2AAA\r\nBmI1AA4AAAAOAPsAAAAAAAAAAAAAAAAAAAAA\/\/AAAGeeeeeeeUAAf\/gAAAAAAAAIgAAD4PgAAAOA\r\nYjUADgAAAA4A+4AAAAAAAAAAAAAAAAAAAAD\/8AAAcccYAcceQABmMAAAAAAAAH6AAADgOAAAD\/9i\r\nLQAOB\/\/4DgHZgAAAAAAAAAAAAAAAAAAAAP\/wAAM888oM888AAGMYAAAAAAAAC\/BiMAAOAAAADgGZ\r\ngAAAAAAAAAAAAYAAAAAAAP\/wAAM88oAAM88AAGMYAAAAAAAACJAAACBiNQAOAAAADgGZgAAAAAAA\r\nAAAAD7AAAAAAAAAAAAGOOMAAOOOAAHMcAAAAAAAAAIAAByGAAAAA8GI1AA4AB4AOAdmAAAAAAHAA\r\nAAAPuAAAAAAAABAAAeeeAAAOeeAAe\/gAAAAAAAAAAAAH+YAAAAP+YjUADgAf8A4A\/4AAAABx\/gAA\r\nAB2YAAAAAAB\/8AAB554AAA55wAA5+AAAAAAAAAAAAAB\/4AAABw9iNQAOADh4DgD\/AAAAAPP\/AAAA\r\nGZgAAAAAAP\/wAABxxgAABxxwABnwAAAAAAAAAAAAACH4AAAMA2I1AA4AYBgOAAgAAAAA888AAAAZ\r\nmAAAAAAA\/\/AABzzyAAAjzzAAAAAAAAAAAACAAAAAIYgAAAwBYjUADgBgCA4AGAAAAADHBwAAAB2Y\r\nAAAAAADMYAADPPAAAAPPEAAAAAAAAAAAAHAAAAfhgAAADAFiNQAOAGAMDgD7AAAAAMcDgAAAD\/gA\r\nAAAAAMYwAAOOOAAAAOPAAA\/4AAAAAAAADgAAA\/+AAAAMAWI1AA4AYAgOAPuAAAAAwwMAAAAP8AAA\r\nAAAAxjAAAeecAAAGeeAAP\/gAAAAAAAABwAAAL\/gAAAwDYjUADgBgGA4Z2YAAAADjhwAAAACAAAAA\r\nAADmOAAB55gAAAZ54AB\/+AAAAAAAAAAwAAAh+AAABwZiNQAOADgwDh2ZgAAAAH\/\/AAAAAAAAAAAA\r\nAPfwAARxwAAAAxxwAH\/4AAAAAAAAAAAAACGAAAD\/\/2I1AA4Af\/\/OBZmAAAAAP\/4AAAAAAAAAAAAA\r\nc\/AABzzwAAADzzgAYAAAAAAAAAAAAAAAAYAAAP\/\/YiQADgB\/\/84B2YAAAAAf+AAAAP\/4AAAAAAAz\r\n4AAHPPAAAAHPOABgYiwADgAAAA4A\/4AAAAAAAAAAAP\/4AAAAAAAAAAADjjgAAADjgABwAAAAAAAA\r\nAMBiNQAOAAAADgD\/AAAAAAAAAAAA\/\/gAAAAAAAAAAAHnnAAAAnngAD\/4ABAAAAAA4AAAAAAAAADP\r\n\/2I1AA4Af\/gOAAgAAAAAAAAAAAAAAAAAAAAAAAAAAeecAAACeeAAP\/gAAAAAAADYAAAAAAAAAM\/\/\r\nYiwADgB\/+A4AAAAAAAAAAAAAAAAAAAAAAA\/\/8AAEecAAAAMccAB\/+AAAAAAAAMxiLQAOAAAwDgGA\r\nAAAAAP\/\/AAAAAAAAAAAAD\/\/wAAc88AAAA888AH\/4IIAAAAAAw4BiNQAOAAAYDgGAAAAAAP\/\/AAAA\r\n\/\/gAAAAAD\/\/wAAM88AAAAM84IOMc8oAAAAAAwPAAAAEAAAD\/\/2IxAA4AAAgOAMAAAAAA\/\/8AAAD\/\r\n+AAAAAAAcGAAA444AAAA84wA4w44gAAAAADAEAAAH2BiMQAOAAAMDgH\/gAAAAD\/\/AAAA\/\/gAAAAA\r\nAOAwAAHnngAAAnnhBnnnngAAAAAAwAAAAD94YjEADgAACA4B\/4AAAAA4AAAAAAAAAAAAAADAMAAB\r\n55wAAAY5555\/\/54AAAAAAACAAABzGGIxAA4AABgOAf+AAAAAGAAAAAAACAAAAAAAwDgAAHHGAAAC\r\nHHHnf\/nGAAAAAAAAgAAAYwhiMQAOAH\/4DgAAAAAAABgAAAAAD\/gAAAAAAOBwAAc88s8888888\/\/8\r\n84AAAAAA\/\/AAAGMMYjEADgB\/4A4AAAAAAAAAAAAAAB\/4AAAAAAD\/8AADPPCDHPPPPPP\/\/PKAAAAA\r\nAH\/wAABjCGIxAA4AAAAOAAAAAAAAAAAAAAAfcAAAAAAAf+AAA444444444444484gAAAAAAwgAAA\r\nYxhiMQAOAAAADg3\/gAAAAAAAAAAAGZgAAAAAAD\/AAAHnnnnnnnnnnnnnngAAAAAAGIAAADM4YjUA\r\nDgAAAA4N\/4AAAAADwAAAABmYAAAAAAAAAAAB555555555555554AAAAAAAaAAAA\/8AAADAFiNgAO\r\nAH8ADg3\/gAAAAD\/8AAAAHZgAAAAAAAAAAABxxxxxxxxxxz5xxwAAAAAAA4AAAA\/AAAAMAYBiNQAO\r\nAf\/ADgAAAAAAAH\/+AAAAHPgAAAAAAAAAAAM8888888888\/\/884IAAAAAAIAAAAAAAAB\/\/2I1AA4D\r\nwPAOAAAAAAAA\/\/8AAAAM+AAAAAAAAAAAAzzzzzzzzzzz\/\/zzgAAAAAAYMAAAAAAAAH\/\/YjQADgcA\r\nMA4AAAAAAADgBwAAAAAAAAAAAAAAAAABjjjjjjjjjjj\/\/jiAAAAAABgwAAf\/+AAADGI0AA4GABgO\r\nAAAAAAAAwAOAAAAAAAAAAAAAAAAAAeeeeeeeeeeef\/+eAAAAAAAAAAAAAAAAAAxiJgAOBgAYDgB\/\r\nAAAAAMADAAAAAMAAAAAAAAAAAAHnnnnnnnnnnnnnjmImAA4EABgOAP+AAAAA4AcAAABz8AAAAAAA\r\nAAAAAHHHHnHHHHHHHHHGYjUADgQAGA4Bw4AAAAD\/\/wAAAPP4AAAAAAAOAAABPPPPPPPPPPPDCAAA\r\nAAAAACPgAAAAAAAAB\/9iNQAOBAAYDgGBgAAAAH\/+AAAA5xgAAAAAAD5gAAE8888888888IeIAAAA\r\nAAAAZjAAAB\/gAAAP\/2I0AA4EABgOAYGAAAAAH\/wAAAHHGAAAAAAAfnAAAY444444wQAAH+AAAAAA\r\nAADEEAAAP\/AAAAxiNAAOBAAYDgGBgAAAAAAAAAABxhgAAAAAAP5wAAHnnnnnnnhAAD\/wAAAAAAAA\r\nxBAAA3AYAAAMYjQADgf\/+A4B44AAAAAMBwAAAcYYAAAAAADmMAAB54YQQAAAAAB8+AAAAAAAAEQQ\r\nAAZgGAAADGI0AA4H\/\/gOAP+AAAAAPwcAAAHGGAAAAAAAxjgAAHHGGAAAAAAAcDgAAAAAAAByMAAM\r\nYAwAAAxiNAAOAAAADgB+AAAAAH+HAAAA7jgAAAAAAMYwAAAAAAAAAAAAAGAYAAAAAAAAH+AADGAI\r\nAAAEYjUADgAAAA4AAAAAAAD\/xwAAAPx4AAAAAADmMAAAAAAAAAAAAABgHAAAAAAAAAAAAAdgGAAA\r\nA4BiNQAOAAAADgAMAAAAAOHnAAAAfHAAAAAAAP\/wAAAAAAAAAAAAAHAYAAAAAAAAAAAAAXA4AAAP\r\n\/2IxAA4AAAAOBz8AAAAAwOcAAAAAAAAAAAAAf+AAAAAAAAAAAAAAeDgAAAAAAAAAAAAAP\/BiMQAO\r\nAAAADg8\/gAAAAMB3AAAAAAAAAAAAAD\/AAAAAAAAAAAAAAH\/4AAAAAAAA\/\/AAAA\/gYjUADgAAAA4O\r\ncYAAAADgPwAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\/8AAAAAAAAH\/wAAAAAAAAACBiNQAOAAAADhxx\r\ngAAAAPw\/AAAAAAAAAAAAABCAAAAAAAAAAAAAAA\/AAAAAAAAAMAAAAAAAAAAD7GI1AA4AAAAOHGGA\r\nAAAAfB8AAAAAAAAAAAAAcOAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAYAAAAAfvYjUADgAAAA4cYYAA\r\nAAA8BwAAAAAAAAAAAADw8AAAAAAAAAAAAAAAAAAAAAAAAAAAAABgAAAADmNiNQAOAAAADhxhgAAA\r\nAAAAAAAAAAAAAAAAAPDwAAAAAAAAAAAABmAAAAAAAAAAAAAAADAAAAAMYWI2AA4AAAAODuOAAAAA\r\nwAAAAAABgAAAAAAAwDAAAAAAAAAAAAAGYAAAAAAAAAAAAAAAHAAAAAxhgGI1AA\/\/\/\/\/+D8eAAAAA\r\nfAAAAAAPsAAAAAAAwDgAAAAAAAAAAAAH\/\/gAAAAAAAAAAAAAf\/gAAAxhYjUAD\/\/\/\/\/4HxwAAAAAP\r\ngAAAAA+4AAAAAADAMAAAAAAAAAAAAAf\/+AAAAAAAAAAAAAAAAAAADGNiNQAP\/\/\/\/\/gAAAAAAAAD4\r\nAAAAHZgAAAAAAOBwAAAAAAAAAAAAA\/\/4AAAAAAAAAAAAAAAAAAAGZ2I1AAAAAAAAAAAAAAAAAB8A\r\nAAAZmAAAAAAA\/\/AAAAAAAAAAAAAAYAAAAAAAAAAj4AAAYAgAAAf+YjUAAAAAAAAAAAAAAAAAAQAA\r\nABmYAAAAAAB\/4AAAAAAAAAAAAABgAAAAAAAAAGYwAABgDAAAAfhiMQAAAAAAAAAAAAAAAAAwAAAA\r\nHZgAAAAAAD\/AAAAAAAAAAAAAAAAAAAAAAAAAxBAAA\/\/4YjEAAAAAAAAAAAAAAAA8\/gAAAA\/4AAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMQQAAP\/+GI1AAAAAAAAAAAAAAAAfv8AAAAP8AAAAAAAAAAA\r\nAAAAAAAAAAAAP\/gAAAAAAABEEAAAYAAAAAf\/YjUAAAAAAAAAAAAAAAD\/\/wAAAACAAAAAAAAAAAAA\r\nAAAAAAAAAAB\/+AAAAAAAAHIwAABgAAAAD\/9iNAAAAAAAAAAAAAAAAOOHAAAAAAAAAAAAAH\/wAAAA\r\nAAAAAAAAAH\/4AAAAAAAAH+AAAAAAAAAMYjQAAAAAAAAAAAAAAADBg4AAAAAAAAAAAAD\/8AAAAAAA\r\nAAAAAABwAAAAAAAAAAAAAAA\/+AAADGI0AAAAAAAAAAAAAAAAwYMAAAD\/+AAAAAAA\/\/AAAAAAAAAA\r\nAAAAYAAAAAAAAAAAAAAAf\/gAAAxiNAAAAAAAAAAAAAAAAOAHAAAA\/\/gAAAAAAOAAAAAAAAAAAAAA\r\nAGAAAAAAAAAAAAAAAGAAAAAMYjQAAAAAAAAAAAAAAAD4HwAAAP\/4AAAAAADAAAAAAAAAAAAAAAAw\r\nAAAAAAAAAP\/wAABgAAAAB2I1AAAAAAAAAAAAAAAAeB4AAAAcGAAAAAAAwAAAAAAAAAAAAAAAf\/gA\r\nAAAAAAB\/8AAAYAAAAAf\/YjUAAAAAAAAAAAAAAAA4HAAAABgYAAAAAABgAAAAAAAAAAAAAAB\/+AAA\r\nAAAAADAAAABgAAAAD\/9iNAAAAAAAAAAAAAAAAAAAAAAAGBgAAAAAAP\/wAAAAAAAAAAAAAH\/4AAAA\r\nAAAAEAAAACAAAAAMYjQAAAAAAAAAAAAAAAAAAAAAABw4AAAAAAD\/8AAAAAAAAAAAAAB\/+AAAAAAA\r\nAAAAAAAcAAAADGI0AAAAAAAAAAAAAAAAA8AAAAAP+AAAAAAA\/\/AAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAf\/gAAAxiNAAAAAAAAAAAAAAAAD\/8AAAAB\/AAAAAAAP\/wAAAAAAAAAAAAAAAAAAAAAAAAP8AA\r\nAAAAAAAEYjUAAAAAAAAAAAAAAAB\/\/gAAAAAAAAAAAAAAAAAAAAAAAAAAAAf\/+AAAAAAAAHBwAAAA\r\nAAAAA4BiNQAAAAAAAAAAAAAAAP\/\/AAAAAAAAAAAAAAAAAAAAAAAAAAAAB\/\/4AAAAAAAAQBAAAAAA\r\nAAAP\/2ItAAAAAAAAAAAAAAAA4AcAAAAAAAAAAAAAABAAAAAAAAAAAAAH\/\/gAAAAAAADAEGIxAAAA\r\nAAAAAAAAAAAAwAOAAAAAAAAAAAAAf\/AAAAAAAAAAAAAH\/\/gAAAAAAABAEAAAH+BiMQAAAAAAAAAA\r\nAAAAAMADAAAAD\/gAAAAAAP\/wAAAAAAAAAAAAAAAAAAAAAAAAcHAAAD\/wYjUAAAAAAAAAAAAAAADg\r\nBwAAAB\/4AAAAAAD\/8AAAAAAAAAAAAAAAAAAAAAAAAD\/AAABwGAAAACBiNQAAAAAAAAAAAAAAAP\/\/\r\nAAAAH\/gAAAAAAMxgAAAAAAAAAAAAAIIIAAAAAAAAAAAAAGAYAAAD7GI1AAAAAAAAAAAAAAAAf\/4A\r\nAAAYAAAAAAAAxjAAAAAAAAAAAAAAQwAAAAAAAAAAAAAAYAwAAAfvYjUAAAAAAAAAAAAAAAAf\/AAA\r\nABgAAAAAAADGMAAAAAAAAAAAQA5554AAAAAAADwQAABgCAAADmNiNQAAAAAAAAAAAAAAAAAAAAAA\r\nCAAAAAAAAOY4AAAAAAAAAAAABnnjAAAAAAAAZhAAAGAYAAAMYWI2AAAAAAAAAAAAAAAAwAAAAAAf\r\n+AAAAAAA9\/AAAAAAAAAAAAHHHHnAAAAAAADDEAAAcDgAAAxhgGI1AAAAAAAAAAAAAAAAfAAAAAAf\r\n+AAAAAAAc\/AAAAAAAAAAAADzzzzwAAAAAADBEAAAP\/AAAAxhYjUAAAAAAAAAAAAAAAAPgAAAAB\/4\r\nAAAAAAAz4AAAAAACAAAADPPPPPCAAAAAAEGQAAAP4AAADGNiNQAAAAAAAAAAAAAAAAD4AAAAAAAA\r\nAAAAAAAAAAAAAAMAAAAOOP\/+OAAAAAAAYNAAAAAAAAAGZ2I1AAAAAAAAAAAAAAAAAB8AAAAACAAA\r\nAAAAAAAAAAAAGeAAAGeef\/+eAAAAAAAwcAAAAAAAAAf+YjUAAAAAAAAAAAAAAAAAAQAAAA\/4AAAA\r\nAAAf8AAAAAAZ4AAAZ55\/\/54AAAAAAIAAAAAAQAAAAfhiMQAAAAAAAAAAAAAAAAAwAAAAH\/gAAAAA\r\nAH\/wAAAAABxwAABxx395xwAAAAAAcAAAADhwYjEAAAAAAAAAAAAAAADh\/AAAAB9wAAAAAAD\/8AAA\r\nAAHPOAACPPPvPPPAAAAAAA4AAAAwOGI1AAAAAAAAAAAAAAAA4\/4AAAAZmAAAAAAA\/\/AAAAABzzgA\r\nAzzz7zzzwAAAAAABwAAAYBgAAAAIYjUAAAAAAAAAAAAAAADj\/wAAABmYAAAAAADAAAAAAADjgAAD\r\njjjznzjgAAAAAAAwAABgCAAABw5iNQAAAAAAAAAAAAAAAOcHAAAAHZgAAAAAAMAAAAAAHnngAAnn\r\nnn\/\/nngAAAAAPBAAAGAIAAAGB2I1AAAAAAAAAAAAAAAA5wMAAAAc+AAAAAAA4AAAAAAGeeAAAOeO\r\nf\/eeeAAAAABmEAAAYBgAAAwDYjUAAAAAAAAAAAAAAADnAwAAAAz4AAAAAAB\/8AAAAAcecAAIcccf\r\n8ccYAAAAAMMQAABwGAAADAFiNQAAAAAAAAAAAAAAAOMHAAAAAAAAAAAAAH\/wAAAAc888AA888888\r\n884AAAAAwRAAAD\/wAAAMAWI1AAAAAAAAAAAAAAAA\/48AAAAAAAAAAAAA\/\/AAAABzzzgABzzw8Ahz\r\nygAAAABBkAAAD+AAAAwDYjUAAAAAAAAAAAAAAAD\/jwAAABgAAAAAAAD\/8AAAADjjjAADjzhwBDji\r\nAAAAAGDQAAAAAAAADgNiNQAAAAAAAAAAAAAAAAOOAAAAGAAAAAAAAMAAAAABnnngABnnnDABnniA\r\nAAAAMHAAAAAAAAAH\/mI1AAAAAAAAAAAAAAAAAAAAAAAMAAAAAAAAwAAAAAGeeeAAGeeUf\/keeAAA\r\nAAAAAAAAAAAAAAH8Yi0AAAAAAAAAAAAAAAADwAAAAB\/4AAAAAADgAAAAAccecAAcccR\/+AccAAAA\r\nAD\/AYjUAAAAAAAAAAAAAAAA\/\/AAAAB\/4AAAAAAB\/8AAABPPPPACPPPD\/+HPPCAAAAHBwAAAAAAAA\r\nAAFiNQAAAAAAAAAAAAAAAH\/+AAAAH\/gAAAAAAP\/wAAAA88oAAI888H\/4M88AAAAAQBAAAAAAAAAD\r\n\/2I1AAAAAAAAAAAAAAAA\/\/8AAAAAAAAAAAAA\/\/AAAAU44wAAI84wAAA484AAAADAEAAAAAAAAAf\/\r\nYjUAAAAAAAAAAAAAAADgBwAAAAAAAAAAAAD\/8AAAB554AAA554AAAB55gAAAAEAQAAAAAAAADEZi\r\nNQAAAAAAAAAAAAAAAMADgAAAM\/gAAAAAAAAAAAABnngAADnngA\/AHnmAAAAAcHAAAAAAAAAMY2I1\r\nAAAAAAAAAAAAAAAAwAMAAABz+AAAAAAA4AAAAAHHGAAAHHHAP\/AHHAAAAAA\/wAAAAAAAAAxhYjUA\r\nAAAAAAAAAAAAAADgBwAAAPNwAAAAAADgAAAADPPIAADPPMA\/+DPPAAAAAIAAAAAAAAAADGFiNgAA\r\nAAAAAAAAAAAAAP\/\/AAAAwzgAAAAAAGAAAAAE88AAAM88wHh4E88AAAAAcAAAAAAAAAAMYYBiNQAA\r\nAAAAAAAAAAAAAH\/+AAABwxgAAAAAAP\/wAAAHOOAAAPOOAHA4GOOAAAAADgAAAAAAAAAOM2I1AAAA\r\nAAAAAAAAAAAAH\/wAAAHAGAAAAAAA\/\/AAAAeeeAAAeeeAYBgeecAAAAABwAAAAAAAAAc\/YjUAAAAA\r\nAAAAAAAAAAAAAAAAAcAYAAAAAAD\/8AAAA55gAAB55wBgGA54QAAAAAAwAAAAAAAAAR5iLQAAAAAA\r\nAAAAAAAAAAAAAAAAwBgAAAAAAP\/wAAABxwAAAhxxgHA4BxwAAAAAOeBiLQAAAAAAAAAAAAAAAAAA\r\nAAAA4DgAAAAAAAAAAAAE88AAA888ID\/wc88AAAAAfzBiNQAAAABAAAAAAAAAAAAAAAAA\/\/AAAAAA\r\nAAAAAAAE88AAAM88B\/\/4M88AAAAARhAAAAAAAAD\/\/2ItAAAAAEAAAAAAAAAAAAAAAAB\/4AAAAAAA\r\nAAAAAAY44AAA44wH\/\/g44wAAAADGEGItAAAAAEAAAAAAAAAAAAAAAAAfwAAAAAAADwAAAAeeeAAG\r\neecH\/\/meeYAAAADAEGItAAAAAH\/gAAAAAAAAAAAAAAAAAAAAAAAAP8AAAAGeeAAGeeUAAAGeeAAA\r\nAABgMGI0AAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAf+AAAAHnGAAHHHkAAAHnHAAAAAAwYAAAAAAA\r\nADxiNAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAPnwAAAE888AM888AAA8888AAAAAAAAAAAAAAAB\/\r\nYjUAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAADgcAAAAHPOADPPPAZ\/\/PPOAAAAAAAAAAAAAAAA54Bi\r\nNQAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAMAwAAAAOOOOOOOMBn\/+OOIAAAAAPBAAAAAAAADBgGI1\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAwDgAAAGeeeeeeeQGf\/+eeAAAAABmEAAAAAAAAMGAYjUA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADgMAAAAR5555554AAAZ554AAAAAMMQAAAAAAAAgYBiNQAA\r\nAAA4AAAAAAAAAAAAAAAAAAAAAAAAAPBwAAABx5xxx55wAABxxxgAAAAAwRAAAAAAAACBgGI1AAAA\r\nAHwAAAAAAAAAAAAAAAAAAAAAAAAA\/\/AAAABzzzzzzzgAADzzyAAAAABBkAAAAAAAAIGAYjUAAAAA\r\nRgAAAAAAAAAAAAAAAAAAAAAAAAB\/4AAAADPPPPPPAAAAPPPAAAAAAGDQAAAAAAAAgYBiNQAAAABG\r\nAAAAAAAAAAAAAAAAAAAAAAAAAB+AAAAAGPOOOOMAAAA\/PMAAAAAAMHAAAAAAAACBgGI1AAAAAEYA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAeeeeeecAAAH+ecAAAAAAAAAAAAAAAAP\/\/YjUAAAAARgAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAZ55554QAAAf54AAAAAAAAAAAAAAAAA\/\/9iJgAAAAB\/4AAA\r\nAAAAAAAAAAAAAAAAAAAADMAAAAAAB5xxxxwAAAB5x2InAAAAAH\/gAAAAAAAAAAAAAAAAAAAAAAAM\r\nwAAAAAABzzzzzwAAADzygGImAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP\/\/AAAAABzzzzwgAAADzw\r\nYiYAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAA\/\/8AAAAABDjjjAAAAAPjBiJwAAAAABAAAAAAAAAAAA\r\nAAAAAAAAAAAAB\/\/wAAAAAHnnnnAAB\/\/\/hBBiJQAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAMAAAAAA\r\nABBnhAAAB\/\/5YiUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAAAAAAAAIccYAAAf\/+GIlAAAHgAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAA4AAAAAAAAAgAAAAAAAhiHwAAB+AAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAOAAAAAAAAAIYhgAAAfwAAAAAAAAAAAAAAAAAAAAAAAAAABgYhkAAAf8AAAAAAAAAAAAAAAAAAAA\r\nAAAAAAD\/8GIZAAAH\/gAAAAAAAAAAAAAAAAAAAAAAAAAA\/\/BiGQAAB5\/AAAAAAAAAAAAAAAAAAAAA\r\nAAAAAP\/wYhkAAAeP8AAAAAAAAAAAAAAAAAAAAAAAAAD\/8GIFAAAHh\/\/AYhgAAAeB\/+AAAAAAAAAA\r\nAAAAAAAAAAAAAAAOYhkAAAeAf+AAAAAAAAAAAAAAAAAAAAAAAAA+YGIZAAAHgA\/gAAAAAAAAAAAA\r\nAAAAAAAAAAAAfnBiGQAAB4AA4AAAAAAAAAAAAAAAAAAAAAAAAP5wYhkAAAeAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAADmMGIZAAAHgAAAAAAAAAAAAAAAAAAAAAAAAAAAxjhiGQAAB4AAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAMYwYhkAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADmMGIZAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAA\/\/BiGQAAB4AAAAAAAAAAAAAAAAAAAAAAAAAAAH\/gYhkAAAfgAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAA\/wGIDAAAH8GIDAAAH\/GIYAAAH\/gAAAAAAAAAAAAAAAAAAAAAAAAAD8GIYAAAHn8AAAAAA\r\nAAAAAAAAAAAAAAAAAAAH+GIYAAAHj\/AAAAAAAAAAAAAAAAAAAAAAAAAP\/GIYAAAHh\/\/AAAAAAAAA\r\nAAAAAAAAAAAAAAAOPGIYAAAHgf\/gAAAAAAAAAAAAAAAAAAAAAAAOHGIYAAAHgH\/gAAAAAAAAAAAA\r\nAAAAAAAAAAAMHGIYAAAHgA\/gAAAAAAAAAAAAAAAAAAAAAAAMHGIYAAAHgADgAAAAAAAAAAAAAAAA\r\nAAAAAAAMHGIYAAAHgAAAAAAAAAAAAAAAAAAAAAAAAAAMHGIZAAAHgAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAP\/\/BiGQAAB4AAAAAAAAAAAAAAAAAAAAAAAAAAD\/\/wYhkAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAA\/\/8Bsqclk0AGIFAAAH\/\/\/gYgUAAAf\/\/+BiBQAAB\/\/\/4GIFAAAD\/\/\/gYgUAAAH\/\/+BiAwAAAPBi\r\nAwAAAHBiAwAAAHBiAwAAAHBiAwAAAHAbKnJZMQBiAwAAABxiBAAAABwcYgQAAAOcHGIEAAAD\/hxi\r\nBAAAA\/\/8YgUAAAD\/\/4BiBQAAAB\/\/8GIFAAAAHD\/wYgUAAAIcHPBiBAAAA\/wcYgQAAAP\/3GIEAAAD\r\n\/\/5iBQAAAB\/\/8GIFAAAAHP\/wYgUAAAAcH\/BiBQAAABwcEGIEAAAAABwbKnJZMQAbDAQbKnJCGx0D\r\nBAAA';

		$.when(LI.kiosk.integrateTickets())
		 .then(function() {
		 	$.get(
				LI.kiosk.urls.printTickets.replace('-666', LI.kiosk.transaction.id) +
					'?direct={"vid": ' + LI.kiosk.devices.printer.params.vid + 
					', "pid": ' + 
					LI.kiosk.devices.printer.params.pid + 
					'}'
				, 
				function(response) {
		            console.log(response);
		         //    LI.kiosk.connector.sendData(LI.kiosk.devices.printer, data).then(
			        //     function(response) {
			        //     	LI.kiosk.utils.showFinalPrompt();
			        //     	console.info("sendData() result:", response);
			        //     },
			        //     LI.kiosk.utils.error();
			        // );
	        	}
	        );
		 })
		;
	},
	printReceipt: function() {

	},
	/********************* UTILS *************************/
	utils: {
		generateUUID: function() {
		    var d = new Date().getTime();
		    //Force letter as first character to avoid selector issues
		    var uuid = 'Axxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
		        var r = (d + Math.random() * 16) % 16 | 0;
		        d = Math.floor(d / 16);
		        return (c == 'x' ? r : (r & 0x7 | 0x8)).toString(16);
		    });

		    return uuid.toUpperCase();
		},
		error: function(error) {
			console.error(error);
		},
		showLoader: function() {
			$('#spinner')
			    .addClass('is-active')
			    .css('display', 'block')
			;
		},
		hideLoader: function() {
			$('#spinner')
			    .removeClass('is-active')
			    .css('display', 'none')
			;
		},
		flash: function(selector) {
			Waves.attach(selector);
			Waves.init();
			Waves.ripple(selector);
		},
		resetBackFab: function() {
			$('#back-fab').off('click').hide();
		},
		switchPanels: function(direction, callback) {
			LI.kiosk.utils.resetBackFab();

			$('.panel:visible').effect('slide', {
				'direction': direction,
				mode: 'hide',
				duration: 500,
				complete: callback
			});
		},
		showLocationPrompt: function() {
    		LI.kiosk.dialogs.location.showModal();
			LI.kiosk.utils.setupCountryField();    		
    		LI.kiosk.utils.setupKeyPad();
    		LI.kiosk.utils.addLocationDialogListeners();
		},
		setupKeyPad: function() {
			$('#keypad').keypad({
        		inputField: $('#postcode'),
        		deleteButtonText: '<i class="material-icons">keyboard_backspace</i>',
        		deleteButtonClass: 'mdl-cell--8-col',
        		buttonTemplate: '<button class="key mdl-button mdl-js-button mdl-button--raised waves-effect mdl-cell--4-col"></button>'
    		});
		},
		addLocationDialogListeners: function(dialog) {
			$(LI.kiosk.dialogs.location).on('close', function() {
    			LI.kiosk.cart.updateTransaction({ 
			    	transaction: {
			    		_csrf_token: LI.kiosk.CSRF,
			    		postalcode: $('#postcode').val(),
			    		country: $('#countries').val()
			        }
				});

				LI.kiosk.checkout();
    		});

    		$('#countries').change(function() {
    			$('#postcode').prop('disabled', true);
    		});

    		$('#postcode').change(function() {
    			$('#countries').val('FR');
    		});
		},
		setupCountryField: function() {
			if(LI.kiosk.countries.length == 0) {
				LI.kiosk.getCountries();
			}

			$.each(LI.kiosk.countries, function(key, country) {
    			if(undefined !== country.Translation[LI.kiosk.config.culture]) {
	    			$('<option>')
	    				.addClass('country')
	    				.prop('id', country.codeiso2.toLowerCase())
	    				.val(country.codeiso2)
	    				.html(country.Translation[LI.kiosk.config.culture].name)
	    				.appendTo('#countries')
	    			;
    			}
    		});

    		$('#' + LI.kiosk.culture).prop('selected', true);
		},
		showPaymentPrompt: function() {
			$(LI.kiosk.dialogs.status)
				.find('p')
				.html('Please follow payment terminal instructions')
			;

			LI.kiosk.dialogs.status.showModal();
		},
		showFailurePrompt: function() {
			LI.kiosk.dialogs.status.close();

			$(LI.kiosk.dialogs.status)
				.find('p')
				.html('Payment failed, retry ?')
			;

			LI.kiosk.dialogs.status.showModal();
		},
		showSuccessPrompt: function() {
			//LI.kiosk.dialogs.status.close();

			$(LI.kiosk.dialogs.status)
				.find('p')
				.html('Please wait for your tickets to be printed')
			;

			LI.kiosk.dialogs.status.showModal();
		},
		showFinalPrompt: function() {
			LI.kiosk.dialogs.status.close();

			$(LI.kiosk.dialogs.status)
				.find('p')
				.html('Thank you, come again')
			;

			LI.kiosk.dialogs.status.showModal();
		}
	}
}