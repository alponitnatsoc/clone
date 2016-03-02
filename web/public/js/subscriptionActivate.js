function startSubscriptionActivate() {
    var validator;
    $.getScript("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("form[name='pago_membresia']").validate({
//onfocusout: true,
            rules: {
                "pago_membresia[documentType]": {required: true, depends: function (element) {
                        return !$("#chkSameAdress").is(":checked");
                    }
                },
                "pago_membresia[document]": {required: true, number: true},
                "pago_membresia[names]": {required: true},
                "pago_membresia[lastName1]": {required: true},
                "pago_membresia[department]": {required: true},
                "pago_membresia[city]": {required: true},
                "pago_membresia[address]": {required: true},
                "pago_membresia[phone]": {required: true, number: true},
                "credit_card": {required: true, number: true, maxlength: 16, minlength: 16, min: 1},
                "card_name": {required: true},
                "expiry_date_month": {required: true, number: true, maxlength: 2, minlength: 1, max: 12, min: 1},
                "expiry_date_year": {required: true, number: true, maxlength: 4, minlength: 4, max: 9999, min: 2016},
                "cvv": {required: true, number: true, maxlength: 3, minlength: 3, max: 999, min: 1}

            },
            messages: {
                "pago_membresia[documentType]": {required: "Por favor selecciona el tipo de documento"},
                "pago_membresia[document]": {required: "Por favor ingresa un documento", number: "ingresa solamente dígitos"},
                "pago_membresia[names]": {required: "Por favor ingresa un nombre"},
                "pago_membresia[lastName1]": {required: "Por favor ingresa un nombre"},
                "pago_membresia[department]": {required: "Por favor selecciona un departamento"},
                "pago_membresia[city]": {required: "Por favor selecciona una ciudad"},
                "pago_membresia[address]": {required: "Por favor ingrese una dirección"},
                "pago_membresia[phone]": {required: "Por favor ingrese un telefono", number: "ingresa solamente dígitos"},
                "credit_card": {
                    required: "Por favor ingrese el numero de la tarjeta",
                    number: "ingresa solamente dígitos",
                    maxlength: "maximo 16 digitos",
                    minlength: "minimo 16 digitos",
                    min: ""

                },
                "card_name": {required: "Por favor ingrese el numero de la tarjeta"},
                "expiry_date_month": {
                    required: "Por favor ingrese el mes de vencimiento",
                    number: "ingresa solamente dígitos",
                    maxlength: "maximo mes de 2 digitos",
                    minlength: "minimo mes de 1 digitos",
                    max: "mes del 01 al 12",
                    min: "mes del 01 al 12"
                },
                "expiry_date_year": {
                    required: "Por favor ingrese el año de vencimiento",
                    number: "ingresa solamente dígitos",
                    maxlength: "maximo año de 4 digitos",
                    minlength: "minimo año de 4 digitos",
                    max: "año del 16 al 99",
                    min: "año del 16 al 99"
                },
                "cvv": {
                    required: "Por favor ingrese el codigo de verificación",
                    number: "ingresa solamente dígitos",
                    maxlength: "maximo 3 digitos",
                    minlength: "minimo 3 digitos",
                    max: "minimo 001",
                    min: "maximo 999"
                }
            }
        });
    });

    $.getScript("/public/js/jquery.creditCardValidator.js").done(function () {
        $('#credit_card').validateCreditCard(function (result) {
            $(this).removeClass();
            if (result.card_type == null) {
                $('.vertical.maestro').slideUp({
                    duration: 200
                }).animate({
                    opacity: 0
                }, {
                    queue: false,
                    duration: 200
                });
                return;
            }
            $(this).addClass(result.card_type.name);
            if (result.card_type.name === 'maestro') {
                $('.vertical.maestro').slideDown({
                    duration: 200
                }).animate({
                    opacity: 1
                }, {
                    queue: false
                });
            } else {
                $('.vertical.maestro').slideUp({
                    duration: 200
                }).animate({
                    opacity: 0
                }, {
                    queue: false,
                    duration: 200
                });
            }
            if (result.valid) {
                return $(this).addClass('valid');
            } else {
                return $(this).removeClass('valid');
            }
        });
    });

    function jsonToHTML(data) {
        var htmls = "<option value=''>Seleccionar una opción</option>";
        for (var i = 0; i < data.length; i++) {
            htmls += "<option value='" + data[i].id_city + "'>" + data[i].name + "</option>";
        }
        return htmls;
    }
    $('#pago_membresia_department').change(function () {
        var $department = $(this);
        // ... retrieve the corresponding form.
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected department value.
        var data = {};
        data[$department.attr('name')] = $department.val();
        var citySelectId = $department.attr("id").replace("_department", "_city");
        // Submit data via AJAX to the form's action path.
        $.ajax({
            method: "POST",
            url: "/api/public/v1/cities",
            data: {department: $department.val()}
        }).done(function (data) {
            $('#' + citySelectId).html(
                    // ... with the returned one from the AJAX response.
                    jsonToHTML(data)
                    );
        });
    });

    $(document).ready(function () {

        $("#chkSameAdress").change(function () {
            if ($(this).is(':checked')) {
                $("#divSameAdress").removeClass('toHide');
                $("#divOtherAdress").addClass('toHide');
            } else {
                $("#divOtherAdress").removeClass('toHide');
                $("#divSameAdress").addClass('toHide');
            }
        });

        $("#tos").on('change', function () {
            if ($(this).is(":checked")) {
                $("#sumbit").removeClass('disabled');
            } else {
                $("#sumbit").addClass('disabled');
            }
        });
        $(".cvvHelp").on('click', function () {
            $("#cvvHelp").toggleClass('toHide');
        });
        $("#pago_membresia_personType_0").change(function () {
            if ($(this).is(':checked')) {
                $("#pago_membresia_documentType").html('<option value="" selected="selected">Seleccionar una opción</option><option value="CC">Cedula Ciudadania</option><option value="CE">Cedula Extranjeria</option><option value="TI">Tarjeta de identidad</option>');
                $("#datosNIT").addClass('toHide');
            }
        });
        $("#pago_membresia_personType_1").change(function () {
            if ($(this).is(':checked')) {
                $("#pago_membresia_documentType").html('<option value="" selected="selected">Seleccionar una opción</option><option value="NIT">NIT</option>');
                $("#datosNIT").removeClass('toHide');
            }
        });
        $("#pago_membresia_personType_0").prop('checked', true);
        $("#chkSameTCC").change(function () {
            if ($(this).is(':checked')) {
                $("#divCardNumber").addClass('toHide');
                $("#divPaymentMethods").removeClass('toHide');
            } else {
                $("#divPaymentMethods").addClass('toHide');
                $("#divCardNumber").removeClass('toHide');
            }
        });
        $("#codigo_referido").focusout(function () {
            if ($("#codigo_referido").val().length >= 6) {
                $.ajax({
                    method: "POST",
                    url: "/api/public/v1/validates/codes",
                    data: {code: $(this).val()},
                    beforeSend: function (xhr) {
                        $("#codigo_referido_estado").html('Validando código...');
                        $("#codigo_referido_estado").removeClass('codigo_referido_valido');
                        $("#codigo_referido_estado").removeClass('codigo_referido_invalido');
                    }
                }).done(function (data) {
                    if (data == true) {
                        $("#esReferido").val(1);
                        $("#codigo_referido").attr('readonly', true)
                        $("#codigo_referido_estado").removeClass('codigo_referido_invalido');
                        $("#codigo_referido_estado").addClass('codigo_referido_valido');
                        $("#codigo_referido_estado").html('Código valido');
                    } else {
                        $("#codigo_referido_estado").removeClass('codigo_referido_valido');
                        $("#codigo_referido_estado").addClass('codigo_referido_invalido');
                        $("#codigo_referido_estado").html('Código invalido');
                    }
                }
                ).fail(function (jqXHR, textStatus, errorThrown) {
                    $("#codigo_referido_estado").removeClass('codigo_referido_valido');
                    $("#codigo_referido_estado").addClass('codigo_referido_invalido');
                    $("#codigo_referido_estado").html('No se pudo validar el código');
                    console.log("FAIL codigo_referido {{ path('api_public_post_validate_code') }}:");
                    console.log(jqXHR);
                    console.log(textStatus);
                    console.log(errorThrown);
                });
            } else {
                $("#codigo_referido_estado").removeClass('codigo_referido_valido');
                $("#codigo_referido_estado").addClass('codigo_referido_invalido');
                $("#codigo_referido_estado").html('Código invalido');
            }

        });
    });


}

