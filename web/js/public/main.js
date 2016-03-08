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

	$('#slideLogin').click(function(evt) {
		evt.stopPropagation();
	})

	$('#forma-login').on('submit', function(evt) {
		evt.preventDefault();
		$('#errorLogin').fadeOut();
		$.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serialize(),
            dataType: "json",
            success     : function(data, status, object) {
            	if(data.error) {
            		$('#errorLogin').html(data.message);
           			$('#errorLogin').fadeIn();	
            	} else {
            		location.reload();
            	}
            },
           	error: function(data, status, object){
           		
           		$('#errorLogin').html("Error de autenticación");
           		$('#errorLogin').fadeIn();	
			}
        });
	})
});