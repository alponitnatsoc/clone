function startSubscriptionChoise() {
    var tiempo_parcialSlider, medio_tiempoSlider, tiempo_completoSlider;

    //var calculator = $.getScript("/public/js/calculatorSubscription.js");
    //data = calculator.calculatorCalculate();
    //$.getScript("/public/js/calculatorSubscription.js").done(function () {
    //    calculator = calculatorCalculate();
    //});
    $(document).ready(function () {
        calculatePrice('');
    });

    var url = '';
    var button = '';
    var employee_id = '';
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
        }).done(function (data) {
            parent = $(button).parent().parent();
            female = parent.find(".female").length;
            male = parent.find(".male").length;
            state = '';
            if (data.state == 'Inactivo') {
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
                //$('#' + employee_id).hide();
                setTimeout(function () {
                    $('#' + employee_id).hide();
                }, 2000);
            } else if (data.state == 'Activo') {
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
                //$('#' + employee_id).show();
                setTimeout(function () {
                    $('#' + employee_id).show();
                }, 2000);
            }
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
        var Tiempo_Completo = 0, Medio_tiempo = 0, Trabajo_por_días = 0;
        var descuento_3er = descuento_isRefered = descuento_haveRefered = 0;
        var total = 0;
        var subtotal = 0;
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
        total = subtotal;
        $("#divSubtotal").html(getPrice(subtotal));

        if ((Tiempo_Completo + Medio_tiempo + Trabajo_por_días) >= 3) {
            descuento_3er = Math.ceil(subtotal * parseFloat($("#descuento_3er_percent").val()));
        } else {
            descuento_3er = 0;
        }
        $("#divDescuento_3er").html(getPrice(descuento_3er));
        if ($("#descuento_isRefered_value").val() > 0) {
            descuento_isRefered = Math.ceil(subtotal * parseFloat($("#descuento_isRefered_percent").val()));
            $("#divDescuento_isRefered").html(getPrice(descuento_isRefered));
            $("#descuento_isRefered_value").val(descuento_isRefered);
        }

        if ($("#descuento_haveRefered_value").val() > 0) {
            descuento_haveRefered = Math.ceil(subtotal * parseFloat($("#descuento_haveRefered_percent").val()));
            $("#divDescuento_haveRefered").html(getPrice(descuento_haveRefered));
            $("#descuento_haveRefered_value").val(descuento_haveRefered);
        }
        if (total == 0) {
            $("input[type=submit]").attr('disabled', true);
        } else {
            $("input[type=submit]").attr('disabled', false);
        }
        total = subtotal - (descuento_3er + descuento_isRefered + descuento_haveRefered);
        $("#result_price" + contenedor).html(getPrice(total));

        //for (i = 0; i < contrato.length; i++) {
        //    console.log(contrato[i]);
        //   type = (contrato[i]['timeCommitment'] == 'XD' ? 'days' : 'mes');
        //   salaryM = contrato[i]['salary'];
        //    calculator = calculatorCalculate(type, salaryM, salaryD, numberOfDays, aid, aidD, sisben, transport);
        //}
        contrato.forEach(calculate);

    }
    function calculate(item, index) {
        console.log(index);
        console.log(item);
        type = (item['timeCommitment'] == 'XD' ? 'days' : 'complete');
        salaryM = item['salary'];
        salaryD = item['salary'] / item['workableDaysMonth'];
        numberOfDays = item['workableDaysMonth'];
        sisben = item['sisben'];
        transport = item['transportAid'];
        resultado = calculatorCalculate(type, salaryM, salaryD, numberOfDays, sisben, transport);
        console.log(resultado);
    }

    function getPrice(valor) {
        price = parseFloat(valor.toString().replace(/,/g, ""))
                .toFixed(0)
                .toString()
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return "$ " + price;
    }
}

