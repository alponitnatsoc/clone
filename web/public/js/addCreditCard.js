function startAddCreditCard() {
    var validator;
    $.getScript("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("form[name='formAddCreditCard']").validate({
//onfocusout: true,
            rules: {
                "credit_card": {required: true, number: true, min: 1},
                "name_on_card": {required: true},
                "expiry_date_month": {required: true, number: true, maxlength: 2, minlength: 1, max: 12, min: 1},
                "expiry_date_year": {required: true, number: true, maxlength: 4, minlength: 4, max: 9999, min: 2016},
                "cvv": {required: true, number: true, maxlength: 4, minlength: 3, max: 9999, min: 1}

            },
            messages: {
                "credit_card": {
                    required: "Por favor ingrese el número de la tarjeta",
                    number: "ingresa solamente dígitos",
                    maxlength: "maximo 16 dígitos",
                    minlength: "minimo 16 dígitos",
                    min: ""

                },
                "name_on_card": {required: "Por favor ingrese el nombre de la tarjeta"},
                "expiry_date_month": {
                    required: "Por favor ingrese el mes de vencimiento",
                    number: "ingresa solamente dígitos",
                    maxlength: "maximo mes de 2 dígitos",
                    minlength: "minimo mes de 1 dígitos",
                    max: "mes del 01 al 12",
                    min: "mes del 01 al 12"
                },
                "expiry_date_year": {
                    required: "Por favor ingrese el año de vencimiento",
                    number: "ingresa solamente dígitos",
                    maxlength: "maximo año de 4 dígitos",
                    minlength: "minimo año de 4 dígitos",
                    max: "año maximo 9999",
                    min: "año minimo 2016"
                },
                "cvv": {
                    required: "Por favor ingrese el código de verificación",
                    number: "ingresa solamente dígitos",
                    maxlength: "maximo 4 dígitos",
                    minlength: "minimo 3 dígitos",
                    max: "minimo 001",
                    min: "maximo 9999"
                }
            }
        });
    });

    $.getScript("/public/js/jquery.creditCardValidator.js").done(function () {
        $('#credit_card').validateCreditCard(function (result) {
            $(this).removeClass();
            $(this).addClass('form-control');
            if (result.valid) {
                $(this).addClass(result.card_type.name);
                return $(this).addClass('valid');
            } else {
                return $(this).removeClass('valid');
            }
        });
    });

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
