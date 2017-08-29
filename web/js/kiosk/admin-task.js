$(document).ready(function() {
	swapInputs();

	$('#admin_task_task').change(swapInputs);
});

var swapInputs = function() {
	var select = $('#admin_task_task');

	if(select.val() === 'pin') {
		select.attr('name', 'temp');
		
		$('<input>')
			.attr('type', 'text')
			.attr('name', 'admin_task_task')
			.insertAfter(select)
		;
	} else if(select.attr('name') === 'temp') {
		$('#admin_task_task').remove();
		select.attr('name', 'admin_task_task');
	}
}