function startSubscriptionChoise() {
    var tiempo_parcialSlider, medio_tiempoSlider, tiempo_completoSlider;
    $(document).ready(function () {

        tiempo_parcialSlider = document.getElementById('tiempo_parcial');
        noUiSlider.create(tiempo_parcialSlider, {
            start: 0,
            step: 1,
            range: {
                min: 0,
                max: 5
            },
            pips: {
                mode: 'values',
                values: [0, 1, 2, 3, 4, 5],
                density: 100
            }
        });
        tiempo_parcialSlider.noUiSlider.on('set', function () {
            calculatePrice("_calc");
        });
        tiempo_parcialSlider.noUiSlider.on('change', function () {
            calculatePrice("_calc");
        });
        tiempo_parcialSlider.noUiSlider.set($(".activo > .trabajo_por_dias").length);

        medio_tiempoSlider = document.getElementById('medio_tiempo');
        noUiSlider.create(medio_tiempoSlider, {
            start: 0,
            step: 1,
            range: {
                min: 0,
                max: 5
            },
            pips: {
                mode: 'values',
                values: [0, 1, 2, 3, 4, 5],
                density: 100
            }
        });
        medio_tiempoSlider.noUiSlider.on('set', function () {
            calculatePrice("_calc");
        });
        medio_tiempoSlider.noUiSlider.on('change', function () {
            calculatePrice("_calc");
        });
        medio_tiempoSlider.noUiSlider.set($(".activo > .medio_tiempo").length);

        tiempo_completoSlider = document.getElementById('tiempo_completo');
        noUiSlider.create(tiempo_completoSlider, {
            start: 0,
            step: 1,
            range: {
                min: 0,
                max: 5
            },
            pips: {
                mode: 'values',
                values: [0, 1, 2, 3, 4, 5],
                density: 100
            }
        });
        tiempo_completoSlider.noUiSlider.on('set', function () {
            calculatePrice("_calc");
        });
        tiempo_completoSlider.noUiSlider.on('change', function () {
            calculatePrice("_calc");
        });
        tiempo_completoSlider.noUiSlider.set($(".activo > .tiempo_completo").length);

        calculatePrice('');
    });

    var contratoid = '';
    var button = '';
    $('#modal_confirm').on('show.bs.modal', function (event) {
        //event.preventDefault();
        button = $(event.relatedTarget);
        contratoid = button.data('contrato-id');
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
            ajax(button, contratoid);
        } else {
            $('#modal_confirm').modal('hide');
        }
    });
    //$("#open_pricing_calc").on('click', function (e) {
    //    $('#modal_price_calculator').modal('show');
    //});
    function ajax(obj, url) {
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function (xhr) {
                $(".btn-change-state-contract-confirm").attr('disabled', true);
            }
        }).done(function (data) {
            parent = $(obj).parent().parent();
            female = parent.find(".female").length;
            male = parent.find(".male").length;
            state = '';
            if (data.state == 'Inactivo') {
                $(obj).html("Activar");
                if (female > 0) {
                    state = "inactivada";
                } else if (male > 0) {
                    state = "inactivado";
                }
                parent.removeClass("activo");
                parent.addClass("inactivo");
            }
            if (data.state == 'Activo') {
                $(obj).html("Inactivar");
                if (female > 0) {
                    state = "activada";
                } else if (male > 0) {
                    state = "activado";
                }
                parent.removeClass("inactivo");
                parent.addClass("activo");
            }
            $('#modal_confirm').modal('hide');
            name = parent.find(".employee_name").html();
            $('.result_ajax').html(name + " fue " + state + " exitosamente.").show(1000);
            setTimeout(function () {
                $('.result_ajax').html("").hide(1000);
            }, 2000);
            calculatePrice('');
            $(".btn-change-state-contract-confirm").attr('disabled', false);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
            $('#modal_confirm').modal('hide');
        });
    }
    function calculatePrice(contenedor) {
        var Tiempo_Completo = 0, Medio_tiempo = 0, Trabajo_por_días = 0;
        var descuento_html = '';
        var descuento = 0;
        var subtotal = 0;
        var total = 0;
        if (contenedor == '_calc') {
            Tiempo_Completo = tiempo_completoSlider ? parseInt(tiempo_completoSlider.noUiSlider.get()) : 0;
            Medio_tiempo = medio_tiempoSlider ? parseInt(medio_tiempoSlider.noUiSlider.get()) : 0;
            Trabajo_por_días = tiempo_parcialSlider ? parseInt(tiempo_parcialSlider.noUiSlider.get()) : 0;
        } else {
            Tiempo_Completo = $(".activo .tiempo_completo").length;
            Medio_tiempo = $(".activo .medio_tiempo").length;
            Trabajo_por_días = $(".activo .trabajo_por_dias").length;
        }
        console.log("Tiempo_Completo:" + Tiempo_Completo);
        console.log("Medio_tiempo:" + Medio_tiempo);
        console.log("Trabajo_por_días:" + Trabajo_por_días);
        if (Tiempo_Completo > 0) {
            total = total + (Tiempo_Completo * $("#PS3").val());
        }
        if (Medio_tiempo > 0) {
            total = total + (Medio_tiempo * $("#PS2").val());
        }
        if (Trabajo_por_días > 0) {
            total = total + (Trabajo_por_días * $("#PS1").val());
        }

        if ((Tiempo_Completo + Medio_tiempo + Trabajo_por_días) >= 3) {
            descuento = (total * $("#descuento_percent").val());
            descuento_html = "El descuento del " + ($("#descuento_percent").val() * 100) + "% por valor de " + getPrice(descuento) + " ya fue aplicado.";
            $("#result_discount" + contenedor).html(descuento_html);
        } else {
            descuento = 0;
            $("#result_discount" + contenedor).html('');
        }

        $("#result_price" + contenedor).html(getPrice(total - descuento));
    }
    function getPrice(valor) {
        price = parseFloat(valor.toString().replace(/,/g, ""))
                .toFixed(0)
                .toString()
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return "$ " + price;
    }
}

