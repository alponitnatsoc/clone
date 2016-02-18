function validateLiqForm() {
    var validator;
    $.getScript("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("form[name='rocketseller_twopickbundle_liquidation']").validate({
            rules: {
                "rocketseller_twopickbundle_liquidation[liquidationReason]": "required"
            },
            messages: {
                "rocketseller_twopickbundle_liquidation[liquidationReason]": "Por favor selecciona una opción"
            }
        });
    });

    $("#liquidation-step1").click(function(e) {
        e.preventDefault();

        var form = $("form");

        if (!form.valid()) {
            return;
        }

        var href = $(this).attr('href');

        $.ajax({
            url: href,
            type: 'POST',
            data: {
                last_work_day: form.find("select[name='rocketseller_twopickbundle_liquidation[lastWorkDay][day]']").val(),
                last_work_month: form.find("select[name='rocketseller_twopickbundle_liquidation[lastWorkDay][month]']").val(),
                last_work_year: form.find("select[name='rocketseller_twopickbundle_liquidation[lastWorkDay][year]']").val(),
                liquidation_reason: form.find("input[name='rocketseller_twopickbundle_liquidation[liquidationReason]']:checked").val(),
                id_liq: form.find("input[name='rocketseller_twopickbundle_liquidation[id_liq]']").val()
            }
        }).done(function (data) {
//            alert(data);
//            $("#liqStep2").html(data);
            if (data["liquidation_reason"] == 2) { //justa causa
                $("#liqSinJustaCausa").hide();
                $('#contLiq').show();
            } else if (data["liquidation_reason"] == 10 || data["liquidation_reason"] == 7)  {
                $("#liqJustaCausa").hide();
            } else {
                $("#liqJustaCausa").hide();
                $("#liqSinJustaCausa").hide();
                $('#contLiq').show();
            }
            $("#liquidationStep1").hide();
            $("#liquidationStep2").show();
            /*
            $("#agregarNovedad").load("/novelty/select/{{ payroll.idPayroll }}");
            $.getScript("{{ asset('public/js/novelty.js') }}").done(function () {
                startNovelty();
            });
            */
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
        });
    });
}