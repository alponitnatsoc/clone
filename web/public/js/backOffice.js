function changeNotificationStatus(){
	    $('.change-notification').on("click",function (e) {	    	
            e.preventDefault();
            $('.continue-express').removeAttr('hidden');
            $('.end-legal').attr('hidden','true');
            var idNotification = $(this).attr('value');
            $.ajax({
                url: $(this).attr('href'),
                type: 'POST',
                data: {
                    notificationId: idNotification,
                    status: 1
                }
            }).done(function (data) {                                
                
                location.reload();

            }).fail(function (jqXHR, textStatus, errorThrown) {
                alert("Hay un error");
            });
        });
        $('.true-express').on("click",function (e) {	    	
            e.preventDefault();
            var idPerson = $(this).attr('value');
            $.ajax({
                url: $(this).attr('href'),
                type: 'POST',
                data: {
                    idPerson: idPerson,
                    type: "Registro express",
                    accion: "Registrar usuario",

                }
            }).done(function (data) {                                
                alert("funcionó");
                location.reload();

            }).fail(function (jqXHR, textStatus, errorThrown) {
            	alert("Hay un error");
            	location.reload();                
            });
        });
}