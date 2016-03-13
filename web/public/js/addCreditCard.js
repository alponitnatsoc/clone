function startAddCreditCard() {
    $("#btn-addCreditCard").on('click', function (event) {
        event.preventDefault();
        url = $(this).attr('href');
        $.ajax({
            url: url,
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
            //$(".btn-addCreditCard").hide();
            //$(".btn-exit").show();
            ajaxUpdateListPaymentMethods();
            //console.log(data);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $("#divError").html(jqXHR.responseJSON.error.exception[0].message);
            $("#divError").show();
            $(".btn-addCreditCard").removeAttr('disabled');
            //console.log(jqXHR.responseJSON.error.exception[0].message);
            //console.log(jqXHR);
            //console.log(textStatus);
            //console.log(errorThrown);
        }).always(function () {
            $(".btn-addCreditCard").html('Guardar');
            //$(".btn-addCreditCard").removeAttr('disabled');
        });
    });
    $(".btn-exit").on('click', function () {
        $("#modal_add_credit_card").modal('hide');
    });
}

function ajaxUpdateListPaymentMethods() {
    $.ajax({
        url: url_update_list,
        type: 'GET',
        beforeSend: function (xhr) {
            $(".btn-addCreditCard").html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Guardando...');
            //$(".btn-addCreditCard").attr('disabled', true);
            //$("#divError").hide();
            //$("#divSuccess").hide();
        }
    }).done(function (data) {
        $("#divSuccess").html('Listado de tarjetas actualizado');
        $("#divSuccess").show();
        $(".btn-addCreditCard").hide();
        $(".btn-exit").show();
        updateListPaymentMethods(data);
        //$("#divSuccess").html('Tarjeta de credito agregada correctamente');
        //$(".btn-addCreditCard").hide();
        //$(".btn-exit").show();
        //console.log(data);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        //$("#divError").html(jqXHR.responseJSON.error.exception[0].message);
        //$("#divError").show();
        //console.log(jqXHR.responseJSON.error.exception[0].message);
        //console.log(jqXHR);
        //console.log(textStatus);
        //console.log(errorThrown);
    }).always(function () {
        //$(".btn-addCreditCard").removeAttr('disabled');
    });
}
