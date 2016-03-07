$(document).ready(function() {
	$('#btnLogin').click(function(evt) {
		evt.preventDefault();
		console.log("Prueba");
		$('#slideLogin').toggleClass('active');
		$('#btnLogin .fa-inverse').toggleClass('fa-caret-up');
		$('#btnLogin .fa-inverse').toggleClass('fa-caret-down');
	});
});