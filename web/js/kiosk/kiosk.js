if ( LI === undefined )
  var LI = {};
if ( LI.kiosk === undefined )
  LI.kiosk = [];

LI.kiosk.cart = [];
LI.kiosk.utils = [];
LI.kiosk.mustache = [];
LI.kiosk.manifestations = {};
LI.kiosk.cart.lines = {};

$(document).ready(function(){
	LI.kiosk.initPlugins();
  	LI.kiosk.getManifestations();
});

LI.kiosk.getManifestations = function(){
	LI.kiosk.utils.showLoader();

	$.ajax({
    url: '/tck_dev.php/transaction/getManifestations/action',
    type: 'GET',
    data: { simplified: 1 },
    dataType: 'json',
    success: LI.kiosk.insertManifestations,
    error: LI.kiosk.utils.error
  });
};

LI.kiosk.insertManifestations = function(data){
	var type = 'manifestations';
	var cardTemplate = $('#manif-card-template').html();

	$.each(data.success.success_fields[type].data.content, function(key, manif){
		
		if ( window.location.hash == '#debug' )
			console.error('Loading an item (#' + manif.id + ') from the ' + type);

		var pdt;
		switch ( type ) {
			case 'museum':
			case 'manifestations':
				var manifDate = new Date(manif.happens_at.replace(' ', 'T'));
				pdt = manifDate.toLocaleString().replace(/:\d\d( \w+){0,1}$/,'');
				break;
			case 'store':
				pdt = manif.name;
				break;
			default:
				pdt = '';
				break;
		}

		//re arrange properties
		manif.name = manif.name == null ? manif.category : manif.name;
		manif.start = pdt;
		manif.end = manif.ends_at.split(' ')[1];

		$.each(manif.gauges, function(i, gauge){
			if( gauge.name == 'INDIVIDUELS' ){

				manif.prices = {};

				$.each(gauge.available_prices, function(key, price){

					if( price.color == undefined )
						price.color = '#009688';

					manif.prices[price.id] = price;
				});
			}
		});

		//add manif to global variable for future retrieval
		LI.kiosk.manifestations[manif.id] = manif;

		//Render and insert card
  		var card = Mustache.render(cardTemplate, { manif: manif });
  		$('#manifs-list').append(card);
	});

	LI.kiosk.addManifListener();
	LI.kiosk.utils.hideLoader();
};

LI.kiosk.utils.error = function(error){
	console.error(error);
	LI.kiosk.utils.hideLoader();
};

LI.kiosk.utils.showLoader = function(){
	$('#spinner').addClass('is-active');
	$('#spinner').css('display', 'block');
};

LI.kiosk.utils.hideLoader = function(){
	$('#spinner').removeClass('is-active');
	$('#spinner').css('display', 'none');
};

LI.kiosk.utils.setupDialog = function(){
	LI.kiosk.utils.dialog = document.querySelector('dialog');
	if (! LI.kiosk.utils.dialog.showModal)
      dialogPolyfill.registerDialog(LI.kiosk.utils.dialog);
};

LI.kiosk.addManifListener = function(){
	$('#manifs-list').on('click', '.manif', function(event){
  	$('#manifs-list').show(500);
		var manif = LI.kiosk.manifestations[$(event.currentTarget.children).attr('id')];
	  	LI.kiosk.openOrderDialog(manif);
	});
}

LI.kiosk.mustache.cacheTemplates = function(){
	var templates = ['manif-card-template'];
	//make mustache cache the templates for quicker future uses
	$('script[type="x-tmpl-mustache"]').each(function(id, template){
		Mustache.parse($(template).html());
	});
};

LI.kiosk.openOrderDialog = function(manif){
	var dialogTemplate = $('#manif-dialog-template').html();

	$('dialog').html(Mustache.render(dialogTemplate, { manif: manif }));

	LI.kiosk.insertPrices(manif);

	LI.kiosk.utils.dialog.showModal();
		
	$('.close, .backdrop').click(function(){
		LI.kiosk.utils.dialog.close();
	});
};

LI.kiosk.insertPrices = function(manif){
	var priceTemplate = $('#price-card-template').html();

	for(key in manif.prices)
		$('dialog #prices').append(Mustache.render(priceTemplate, { price: manif.prices[key] }));

	LI.kiosk.addPriceListener(manif);
};

LI.kiosk.addPriceListener = function(manif){
	$('#prices').on('click', '.price', function(event){
		LI.kiosk.cart.addItem(manif, manif.prices[$(event.currentTarget.children).attr('id')])
	});
};

LI.kiosk.initPlugins = function(){
	Waves.attach('.waves-effect');
	Waves.init();
	LI.kiosk.utils.setupDialog();
	LI.kiosk.mustache.cacheTemplates();
};

LI.kiosk.cart.addItem = function(item, price){

	var newLine;
	var exists = false;

	$.each(LI.kiosk.cart.lines, function(key, line){
		
		if(line.name == item.name && line.price.id == price.id){
			var htmlLine = $('#' + line.id);
			line.qty++;
			LI.kiosk.cart.lineTotal(line);
			exists = true;
			htmlLine.find('.total').text(line.total);
			htmlLine.find('.qty').text(line.qty);
		}
	});

	if(!exists){
		var newLine = {
			id: LI.kiosk.utils.generateUUID(),
			name: item.name,
			price: price,
			qty: 1,
			total: price.raw_value
		};

		LI.kiosk.cart.lines[newLine.id] = newLine;
		LI.kiosk.cart.insertLine(LI.kiosk.cart.lines[newLine.id]);
	}

	$('#cart').show(500);
	LI.kiosk.cart.cartTotal();
};

LI.kiosk.cart.removeItem = function(lineId) {
	var line = LI.kiosk.cart.lines[lineId];
	var htmlLine = $('#' + lineId);

	line.qty--;
	LI.kiosk.cart.lineTotal(line);

	if(line.qty == 0){
		delete LI.kiosk.cart.lines[lineId]
		LI.kiosk.cart.removeLine(htmlLine);
	}
	else{
		LI.kiosk.lines[lineId] = line;
		htmlLine.find('.qty').text(line.qty);
		htmlLine.find('.total').text(line.total);
	}

	if(Object.keys(LI.kiosk.cart.lines) < 1)
		$('#cart').hide(200);
};

LI.kiosk.utils.generateUUID = function(){

    var d = new Date().getTime();
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c == 'x' ? r : (r & 0x7 | 0x8)).toString(16);
    });

    return uuid.toUpperCase();
};

LI.kiosk.cart.insertLine = function(line){
	var lineTemplate = $('#cart-line-template').html();

	$('#cart-lines').append(Mustache.render(lineTemplate, { line: line }));
};

LI.kiosk.cart.removeLine = function(htmlLine){

	$(htmlLine).remove();
};

LI.kiosk.cart.lineTotal = function(line){
	line.total = line.price.raw_value * line.qty;
};

LI.kiosk.cart.cartTotal = function(){
	var total = 0;

	$.each(LI.kiosk.cart.lines, function(key, line){
		total += line.total;
	});

	$('#cart-total').text(total);
};