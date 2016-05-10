function startSubscriptionChoise() {
    var tiempo_parcialSlider, medio_tiempoSlider, tiempo_completoSlider;

    $(document).ready(function () {
        loadConstrains();
        calculatePrice('');
    });

    var url = '';
    var button = '';
    var employee_id = '';
    var total = 0;
    var subtotal = 0;
    $(".btn-change-state-contract").click(function (event) {
        button = $(this);
        if (button.html() == 'Activar') {
            ajax(button);
        } else {
            $('#modal_confirm').modal('show');
        }
    });
    $(".modal-content .close").hide();
    $('#modal_confirm').on('show.bs.modal', function (event) {
        //event.preventDefault();
        //button = $(event.relatedTarget);
        //url = button.data('href');
        if ($(".activo").length > 1 || $(button).html() == "Activar") {
            $(".btn-change-state-contract-confirm").show();
        } else {
            $(".btn-change-state-contract-confirm").hide();
        }
    });
    //$(".btn-change-state-contract").on('click', function (e) {
    //    $('#modal_confirm').modal('show');
    //});
    $(".btn-change-state-contract-confirm").on('click', function (e) {
        if ($(".activo").length > 1 || $(button).html() == "Activar") {
            ajax(button);
        } else {
            $('#modal_confirm').modal('hide');
        }
    });
    //$("#open_pricing_calc").on('click', function (e) {
    //    $('#modal_price_calculator').modal('show');
    //});
    function ajax(button) {
        url = button.data('href');
        employee_id = button.data('id');
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function (xhr) {
                $(button).attr('disabled', true);
                $(".btn-change-state-contract-confirm").attr('disabled', true);
            }
        }).success(function (data) {
            parent = $(button).parent().parent();
            //console.log(parent);
            female = parent.find(".female").length;
            male = parent.find(".male").length;
            state = '';
            //console.log(data);
            if (data.state == 'Inactivo') {
                employee[employee_id]['state'] = 0;
                $(button).html("Activar");
                $(button).removeClass("on");
                $(button).addClass("off");
                parent.removeClass("activo");
                parent.addClass("inactivo");
                if (female > 0) {
                    state = "inactivada";
                } else if (male > 0) {
                    state = "inactivado";
                } else {
                    state = "inactivado";
                }
            } else if (data.state == 'Activo') {
                employee[employee_id]['state'] = 1;
                $(button).html("Inactivar");
                $(button).removeClass("off");
                $(button).addClass("on");
                parent.removeClass("inactivo");
                parent.addClass("activo");
                if (female > 0) {
                    state = "activada";
                } else if (male > 0) {
                    state = "activado";
                } else {
                    state = "activado";
                }
            }
            //console.log(parent);
            $('#modal_confirm').modal('hide');
            name = parent.find(".employee_name").html();
            $('.result_ajax_msg').html(name + " fue " + state + " exitosamente.");
            $('.result_ajax').show();
            setTimeout(function () {
                $('.result_ajax_msg').html("");
                $('.result_ajax').hide();
            }, 2000);
            calculatePrice('');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
            $('#modal_confirm').modal('hide');
        }).always(function () {
            $(button).attr('disabled', false);
            $(".btn-change-state-contract-confirm").attr('disabled', false);
        });
    }
    function calculatePrice(contenedor) {
        var Tiempo_Completo = 0, Medio_tiempo = 0, Trabajo_por_días = 0, total = 0, subtotal = 0, count_employee = 0;
        if (contenedor == '_calc') {
            Tiempo_Completo = tiempo_completoSlider ? parseInt(tiempo_completoSlider.noUiSlider.get()) : 0;
            Medio_tiempo = medio_tiempoSlider ? parseInt(medio_tiempoSlider.noUiSlider.get()) : 0;
            Trabajo_por_días = tiempo_parcialSlider ? parseInt(tiempo_parcialSlider.noUiSlider.get()) : 0;
        } else {
            Tiempo_Completo = $(".activo .tiempo_completo").length;
            Medio_tiempo = $(".activo .medio_tiempo").length;
            Trabajo_por_días = $(".activo .trabajo_por_dias").length;
        }
        //console.log("Tiempo_Completo:" + Tiempo_Completo);
        //console.log("Medio_tiempo:" + Medio_tiempo);
        //console.log("Trabajo_por_días:" + Trabajo_por_días);
        if (Tiempo_Completo > 0) {
            PS3 = parseFloat($("#PS3").val());
            PS3_IVA = 1 + parseFloat($("#PS3_IVA").val());
            subtotal = Math.ceil(subtotal + (Tiempo_Completo * (PS3 * PS3_IVA)));
        }
        if (Medio_tiempo > 0) {
            PS2 = parseFloat($("#PS2").val());
            PS2_IVA = 1 + parseFloat($("#PS2_IVA").val());
            subtotal = Math.ceil(subtotal + (Medio_tiempo * (PS2 * PS2_IVA)));
        }
        if (Trabajo_por_días > 0) {
            PS1 = parseFloat($("#PS1").val());
            PS1_IVA = 1 + parseFloat($("#PS1_IVA").val());
            subtotal = Math.ceil(subtotal + (Trabajo_por_días * (PS1 * PS1_IVA)));
        }

        for (key in contrato) {
            //console.log("calculate(item, index)");
            //console.log(contrato[key]);
            if (employee[key]['state'] > 0) {
                count_employee = count_employee + 1;
                type = (contrato[key]['timeCommitment'] == 'XD' ? 'days' : 'complete');
                salaryM = contrato[key]['salary'];
                salaryD = contrato[key]['salary'] / contrato[key]['workableDaysMonth'];
                numberOfDays = contrato[key]['workableDaysMonth'];
                sisben = contrato[key]['sisben'];
                transport = contrato[key]['transportAid'];
                resultado = calculator(type, salaryM, salaryD, numberOfDays, sisben, transport);

                total = total + resultado['totalExpenses2'];

                $("#sueldos").html(getPrice(total));
                $("#primerPago").html(getPrice(total));
                $("#segundoPago").html(getPrice(total + subtotal));
                $("#count_employee").html(count_employee + ' Empleados');

            }
        }

        $("#divSubtotal").html(getPrice(subtotal));

        if (subtotal == 0) {
            $("input[type=submit]").attr('disabled', true);
        } else {
            $("input[type=submit]").attr('disabled', false);
        }

    }

    function getPrice(valor) {
        price = parseFloat(valor.toString().replace(/,/g, ""))
                .toFixed(0)
                .toString()
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return "$ " + price;
    }

    $("#btnRedimir").on('click', function () {
        if ($("#codigo_referido").val().length >= 6) {
            $.ajax({
                method: "POST",
                url: "/api/public/v1/validates/codes",
                data: {code: $("#codigo_referido").val()},
                beforeSend: function (xhr) {
                    $("#codigo_referido_estado").html('Validando código...');
                    $("#codigo_referido_estado").removeClass('codigo_referido_valido');
                    $("#codigo_referido_estado").removeClass('codigo_referido_invalido');
                }
            }).done(function (data) {
                if (data == true) {
                    $("#esReferido").val(1);
                    $("#codigo_referido").attr('readonly', true);
                    $("#codigo_referido_estado").removeClass('codigo_referido_invalido');
                    $("#codigo_referido_estado").addClass('codigo_referido_valido');
                    $("#codigo_referido_estado").html('Código valido');
                    $("#codigoReferido").hide();
                    $("#btnRedimir").attr('disabled', true);
                    $("#btnRedimir").addClass('off', true);
                } else {
                    $("#codigo_referido_estado").removeClass('codigo_referido_valido');
                    $("#codigo_referido_estado").addClass('codigo_referido_invalido');
                    $("#codigo_referido_estado").html(data);
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
        }
    });
}

