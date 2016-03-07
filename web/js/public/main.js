$(document).ready(function() {
	$('#btnLogin').click(function(evt) {
		evt.preventDefault();
		evt.stopPropagation();
		console.log("Prueba");
		$('#slideLogin').toggleClass('active');
		$('#btnLogin .fa-inverse').toggleClass('fa-caret-up');
		$('#btnLogin .fa-inverse').toggleClass('fa-caret-down');
	});

	$('html').click(function() {
		$('#slideLogin').removeClass('active');
		$('#btnLogin .fa-inverse').addClass('fa-caret-up');
		$('#btnLogin .fa-inverse').removeClass('fa-caret-down');
	});

});