if ( LI === undefined )
  var LI = {};
if ( LI.chartActions === undefined )
  LI.chartActions = [];

$(document).ready(function(){

  LI.chartActions.exportImg();
});

LI.chartActions.exportImg = function(){
	
	$('.img-export').click(function(){
		
		var imgData = $(this).parent().siblings('.chart').jqplotToImageStr({});
		var img = $('<img/>').attr('src',imgData);
		window.open(img.attr('src'));
	});
};