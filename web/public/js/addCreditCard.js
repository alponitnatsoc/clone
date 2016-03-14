function startAddCreditCard() {
    $("#btn-addCreditCard").on('click', function (event) {
        event.preventDefault();
        if ($("#btn-addCreditCard").html() == 'Salir') {
            $("#modal_add_credit_card").modal('hide');
        } else {
            $.ajax({
                url: url_add_credit_card,
                type: 'POST',
                data: $('form').serialize(),
                beforeSend: function (xhr) {
                    $(".btn-addCreditCard").html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Guardando...');
                    $(".btn-addCreditCard").attr('disabled', true);
                    $("#divError").hide();
                    $("#divSuccess").hide();
                }
            }).done(function (data) {
                $("#divSuccess").html('Tarjeta de credito agregada correctamente');
                $("#divSuccess").show();
                ajaxUpdateListPaymentMethods();
                //console.log(data);
            }).fail(function (jqXHR, textStatus, errorThrown) {
                $("#divError").html(jqXHR.responseJSON.error.exception[0].message);
                $("#divError").show();
                $(".btn-addCreditCard").html('Guardar');
                $(".btn-addCreditCard").removeAttr('disabled');
                //$(".btn-addCreditCard").hide();
                //$(".btn-exit").show();
                //console.log(jqXHR.responseJSON.error.exception[0].message);
                //console.log(jqXHR);
                //console.log(textStatus);
                //console.log(errorThrown);
            }).always(function () {
                //$(".btn-addCreditCard").hide();
                //$(".btn-exit").show();
                //$(".btn-addCreditCard").html('Guardar');
                //$(".btn-addCreditCard").removeAttr('disabled');
            });
        }
    });
}

function ajaxUpdateListPaymentMethods() {
    $.ajax({
        url: url_update_list,
        type: 'GET',
        beforeSend: function (xhr) {
            $(".btn-addCreditCard").html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Actualizando...');
            //$(".btn-addCreditCard").attr('disabled', true);
            //$("#divError").hide();
            //$("#divSuccess").hide();
        }
    }).done(function (data) {
        $("#divSuccess").html('Listado de tarjetas actualizado');
        $("#divSuccess").show();
        $(".btn-addCreditCard").html('Salir');
        updateListPaymentMethods(data);
        //$("#divSuccess").html('Tarjeta de credito agregada correctamente');
        //$(".btn-addCreditCard").hide();
        //$(".btn-exit").show();
        //console.log(data);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        $(".btn-addCreditCard").html('Guardar');
        //$("#divError").html(jqXHR.responseJSON.error.exception[0].message);
        //$("#divError").show();
        //console.log(jqXHR.responseJSON.error.exception[0].message);
        //console.log(jqXHR);
        //console.log(textStatus);
        //console.log(errorThrown);
    }).always(function () {
        $(".btn-addCreditCard").removeAttr('disabled');
        //$(".btn-addCreditCard").removeAttr('disabled');
    });
}
