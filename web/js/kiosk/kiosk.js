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
LI.kiosk.products = {
	manifestations: {},
	museum: {},
	store: {}
};


$(document).ready(function(){
	LI.kiosk.init();
});

LI.kiosk.init = function(){
	LI.kiosk.utils.showLoader();
	LI.kiosk.initPlugins();
	LI.kiosk.addListeners();
	LI.kiosk.getTransaction();

	$.when(LI.kiosk.getManifestations(), LI.kiosk.getMuseum(), LI.kiosk.getStore())
     .then(LI.kiosk.menu, LI.kiosk.menu);
    ;
}

LI.kiosk.menu = function(){
	
	var lists = {};

	$.each(LI.kiosk.products, function(key, productList){
		var listLength = Object.keys(productList).length;

		if( listLength > 0)
			lists[key] = listLength;
	});

	if(Object.keys(lists).length > 1)
		LI.kiosk.utils.setUpMenu(lists);
	else
		LI.kiosk.insertProducts(Object.keys(lists)[0]);

	LI.kiosk.utils.hideLoader();
}

LI.kiosk.utils.setUpMenu = function(productLists){

	$.each(productLists, function(type, length){
		
		var template = Handlebars.compile(LI.kiosk.templates.menuItem);
		var item = {
			name: type
		};

		$('#product-menu-items').append(template(item ));
	});

	$('.menu-item').click(function(){
		LI.kiosk.utils.switchMenuPanels($(this).data('type'));
	});
}

LI.kiosk.addListeners = function(){
	
	$('#info-fab').click(function(){
		$('#info-panel').show(500);
		setTimeout(function(){
			$('#info-panel').hide(500)
		}, 10000);
	});
	
	$('#products-list').on('click', '.product', function(event){
		var productCard = $(event.currentTarget);
		var type = productCard.data('type');
		var id = productCard.data('id');

	  	LI.kiosk.showDetails(LI.kiosk.products[type][id], $(this));
	});
}

LI.kiosk.getTransaction = function(){

	$.get('/tck.php/transaction/newJson', function(data){
		LI.kiosk.transaction.id = data;
	});
}

LI.kiosk.getManifestations = function(){

	return $.ajax({
	    url: '/tck.php/transaction/getManifestations',
	    type: 'GET',
	    data: { simplified: 1 },
	    dataType: 'json',
	    success: LI.kiosk.cacheManifestations,
	    error: LI.kiosk.utils.error
  	});
}

LI.kiosk.getStore = function(){

	return $.ajax({
	    url: '/tck.php/transaction/getStore',
	    type: 'GET',
	    data: { simplified: 1 },
	    dataType: 'json',
	    success: LI.kiosk.cacheStore,
	    error: LI.kiosk.utils.error
  	});
}

LI.kiosk.getMuseum = function(){

	return $.ajax({
	    url: '/tck.php/transaction/getPeriods',
	    type: 'GET',
	    data: { simplified: 1 },
	    dataType: 'json',
	    success: LI.kiosk.cacheMuseum,
	    error: LI.kiosk.utils.error
  	});
}

LI.kiosk.rearrangeProperties = function(manif){
	
	var manifDate = new Date(manif.happens_at.replace(' ', 'T'));
	var endDate = new Date(manif.ends_at);

	manif.declinations = {};
	manif.prices = {};
	manif.start = manifDate.toLocaleString().replace(/:\d\d( \w+){0,1}$/,'');
	manif.end = endDate.getHours() + ':' + endDate.getMinutes();
	manif.name = manif.name == null ? manif.category : manif.name;

	if(manif.image_id != undefined)
		manif.background = 'background-image: url("' + manif.image_url + '")' ;
	else
		manif.background = 'background-color: ' + manif.color;

	$.each(manif.gauges, function(i, gauge){

		var color = '#4FC3F7';

		if ( gauge.color == undefined )
			gauge.color = color;

		$.each(gauge.available_prices, function(key, price){
			if( price.color == undefined )
				price.color = color;

			manif.prices[price.id] = price;
		});

		manif.declinations[gauge.id] = gauge;
	});
}

LI.kiosk.cacheManifestations = function(data){

	var type = 'manifestations';

	$.each(data.success.success_fields[type].data.content, function(key, manif){
		
		if ( window.location.hash == '#debug' )
			console.log('Loading an item (#' + manif.id + ') from the ' + type);

		manif.type = type;
		LI.kiosk.rearrangeProperties(manif);
		LI.kiosk.products.manifestations[manif.id] = manif;
	});
}

LI.kiosk.cacheMuseum = function(data){

	var type = 'museum';
	LI.kiosk.products.museum = {};

	$.each(data.success.success_fields[type].data.content, function(key, manif){
		
		if ( window.location.hash == '#debug' )
			console.log('Loading an item (#' + manif.id + ') from the ' + type);

		manif.type = type;
		LI.kiosk.rearrangeProperties(manif);
		LI.kiosk.products.museum[manif.id] = manif;
	});
}

LI.kiosk.cacheStore = function(data){

	var type = 'store';
	LI.kiosk.products.store = {};

	$.each(data.success.success_fields[type].data.content, function(key, product){
		
		if ( window.location.hash == '#debug' )
			console.log('Loading an item (#' + product.id + ') from the ' + type);

		//re arrange properties
		product.type = type;
		// product.name = product.name == null ? product.category : product.name;
		// var productDate = new Date(product.happens_at.replace(' ', 'T'));
		// product.start = productDate.toLocaleString().replace(/:\d\d( \w+){0,1}$/,'');
		// var endDate = new Date(product.ends_at);
		// product.end = endDate.getHours() + ':' + endDate.getMinutes();

		// if(product.image_id != undefined)
		// 	product.background = 'background-image: url("' + product.image_url + '")' ;
		// else
		// 	product.background = 'background-color: ' + product.color;

		// $.each(product.gauges, function(i, gauge){
		// 	if( gauge.name == 'INDIVIDUELS' ){
		// 		product.gauge_url = gauge.url;
		// 		product.prices = {};

		// 		$.each(gauge.available_prices, function(key, price){

		// 			if( price.color == undefined )
		// 				price.color = '#4FC3F7';

		// 			product.prices[price.id] = price;
		// 		});
		// 	}
		// });

		LI.kiosk.products.store[product.id] = product;
	});
}

LI.kiosk.insertProducts = function(type){

	var cardTemplate = LI.kiosk.templates['productCard'][type];

	if((cardTemplate == null))
		cardTemplate = LI.kiosk.templates['productCard']['manifestations'];

	$('#products-list').empty();
	
	$.each(LI.kiosk.products[type], function(key, product){
		var template = Handlebars.compile(cardTemplate);
		$('#products-list').append(template(product));
	});
}

LI.kiosk.utils.error = function(error){
	console.error(error);
}

LI.kiosk.utils.showLoader = function(){
	$('#spinner').addClass('is-active');
	$('#spinner').css('display', 'block');
}

LI.kiosk.utils.hideLoader = function(){
	$('#spinner').removeClass('is-active');
	$('#spinner').css('display', 'none');
}

LI.kiosk.utils.flash = function(selector){
	Waves.attach(selector);
	Waves.init();
	Waves.ripple(selector);
}

LI.kiosk.cacheTemplates = function(){
	//make mustache cache the templates for quicker future uses
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

LI.kiosk.showDetails = function(product, card){
	var detailsTemplate = Handlebars.compile(LI.kiosk.templates.productDetails);

	// insert manif info
	$('#product-details-card').html(detailsTemplate(product));

	if( Object.keys(product.declinations).length > 1 ){

		var declinationTemplate = Handlebars.compile(LI.kiosk.templates.declinationCard);
		
		$.each(product.declinations, function(id, declination){
			$('#product-details-card #declinations').append(declinationTemplate(declination));	
		});

		$('#declinations').css('display', 'flex');

		$('.declination').click(function(event){
			var declination = product.declinations[$(event.currentTarget.children).attr('id')];

			$('#declinations').hide();
			$('#declination-name').text(declination.name);
			LI.kiosk.insertPrices(product, product.declinations[$(event.currentTarget.children).attr('id')].available_prices);
		});
	}else{
		LI.kiosk.insertPrices(product, Object.values(product.declinations)[0].available_prices);
	}

	//show details panel
	LI.kiosk.utils.switchPanels();
}

LI.kiosk.insertPrices = function(product, prices){
	var priceTemplate = $('#price-card-template').html();

	$('#product-details-card #prices').empty();

	for(key in prices){
		var template = Handlebars.compile(priceTemplate);
		$('#product-details-card #prices').append(template(prices[key]));
	}

	LI.kiosk.addPriceListener(product);

	$('#prices').css('display', 'flex');
}

LI.kiosk.addPriceListener = function(product){
	$('#prices').on('click', '.price', function(event){
		var id = $(event.currentTarget).attr('id');
		
		LI.kiosk.cart.addItem(product, product.prices[$(event.currentTarget.children).attr('id')])
	});
}

LI.kiosk.initPlugins = function(){
	Waves.attach('.waves-effect');
	Waves.init();
	LI.kiosk.cacheTemplates();
	toastr.options = {
		positionClass: 'toast-bottom-full-width',
		closeButton: true,
		timeOut: 0
	};
}

LI.kiosk.cart.addItem = function(item, price){

	var newLine;
	var lineId;
	var exists = false;

	$.each(LI.kiosk.cart.lines, function(key, line){
		
		if(line.productId == item.id && line.price.id == price.id){
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

	if(!exists){
		var newLine = {
			id: LI.kiosk.utils.generateUUID(),
			name: item.name,
			productId: item.id,
			value: price.value,
			price: price,
			qty: 1,
			total: price.value
		};

		LI.kiosk.cart.lines[newLine.id] = newLine;
		LI.kiosk.cart.insertLine(LI.kiosk.cart.lines[newLine.id]);
		LI.kiosk.utils.flash('#' + newLine.id);
		lineId = newLine.id;
	}

	$('#cart').show(500);
	$('#cart').css('display', 'flex');
	LI.kiosk.cart.cartTotal();

	if(item.gauge_url !== undefined)
		LI.kiosk.checkAvailability(item.gauge_url, lineId, item.id);
}

LI.kiosk.cart.removeItem = function(lineId) {
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

	LI.kiosk.cart.cartTotal();

	if(Object.keys(LI.kiosk.cart.lines) < 1)
		$('#cart').hide(200);
}

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

LI.kiosk.utils.switchPanels = function(){
	
	$('#products').effect('slide', {
		direction: 'left', 
		mode: 'hide', 
		duration: '300',
		complete: function(){
			$('#product-details-card').effect('slide', {
				direction: 'right', 
				mode: 'show',
				duration: '300'
			});
		}
	});
	
	$('#back-fab').unbind('click').click(function(){

		$(this).hide();

		$('#product-details-card').effect('slide', {
			direction: 'right', 
			mode: 'hide', 
			duration: '300',
			complete: function(){
				$('#products').effect('slide', {
					direction: 'left', 
					mode: 'show', 
					duration: '300'
				});
			}
		});

		if($('#product-menu-items .menu-item').length > 0)
			$(this).unbind('click').click(function(){
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

LI.kiosk.utils.switchMenuPanels = function(type){
		
		$('#product-menu').effect('slide', {
			direction: 'left', 
			mode: 'hide', 
			duration: '300',
			complete: function(){

				LI.kiosk.insertProducts(type);

				$('#products').effect('slide', {
					direction: 'right', 
					mode: 'show',
					duration: '300'
				});
			}
		});
		
		$('#back-fab').unbind('click').click(function(){

			$(this).hide();

			$('#products').effect('slide', {
				direction: 'right', 
				mode: 'hide', 
				duration: '300',
				complete: function(){

					$('#product-menu').effect('slide', {
						direction: 'left', 
						mode: 'show', 
						duration: '300'
					});
				}
			});
			
		}).show();
	}

LI.kiosk.cart.insertLine = function(line){
	var lineTemplate = Handlebars.compile(LI.kiosk.templates.cartLine);
console.log(line);
	$('#cart-lines').append(lineTemplate(line ));
	$('#' + line.id + ' .remove-item').click(function(){
		LI.kiosk.cart.removeItem(line.id);
	});
}

LI.kiosk.cart.removeLine = function(htmlLine){
	$(htmlLine).hide(500).remove();
}

LI.kiosk.cart.lineTotal = function(line){
	line.total = LI.format_currency(line.price.raw_value * line.qty, false);
}

LI.kiosk.cart.cartTotal = function(){

	var total = 0;

	$.each(LI.kiosk.cart.lines, function(key, line){
		total += parseFloat(line.total);
	});

	$('#cart-total-value').text(LI.format_currency(total, false));
}

LI.kiosk.checkAvailability = function(gaugeUrl, lineId, productId){

	var qty = 0;

	$.each(LI.kiosk.cart.lines, function(key, lineObject){
		if(lineObject.productId == productId)
			qty += lineObject.qty;
	});

	$.get(gaugeUrl, function(data){

		if(data.free < qty){
			$('#' + lineId + ' .remove-item').click();
			toastr.info('The last item added to the cart was removed as it wasn\'t available anymore');
		}
	});
}