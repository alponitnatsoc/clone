function validateLiqForm() {
    var validator;
    $.getScript("//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("form[name='rocketseller_twopickbundle_liquidation']").validate({
            rules: {
                "rocketseller_twopickbundle_liquidation[liquidationReason]": "required"
            },
            messages: {
                "rocketseller_twopickbundle_liquidation[liquidationReason]": "Por favor selecciona una opción"
            }
        });

        jQuery.extend(jQuery.validator.messages, {
            required: "Este campo es requerido",
            remote: "Please fix this field.",
            email: "Ingresa un email válido",
            url: "Please enter a valid URL.",
            date: "Inrega una fecha válida",
            dateISO: "Please enter a valid date (ISO).",
            number: "Please enter a valid number.",
            digits: "Please enter only digits.",
            creditcard: "Ingresa un número de tarjeta de crédito válido",
            equalTo: "Please enter the same value again.",
            accept: "Please enter a value with a valid extension.",
            maxlength: jQuery.validator.format("Please enter no more than {0} characters."),
            minlength: jQuery.validator.format("Please enter at least {0} characters."),
            rangelength: jQuery.validator.format("Please enter a value between {0} and {1} characters long."),
            range: jQuery.validator.format("Please enter a value between {0} and {1}."),
            max: jQuery.validator.format("Please enter a value less than or equal to {0}."),
            min: jQuery.validator.format("Please enter a value greater than or equal to {0}.")
        });
    });

    $("#liquidation-newstep1").click(function(e) {
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
            redirUri = data["url"];
            window.location.href = redirUri;
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
        });
    });
}

$('input[name="rocketseller_twopickbundle_liquidation[liquidationReason]"]:radio').on("change", function(){

    var form = $("form[name='rocketseller_twopickbundle_liquidation']");
    var day = form.find("select[name='rocketseller_twopickbundle_liquidation[lastWorkDay][day]']").val();
    var month = form.find("select[name='rocketseller_twopickbundle_liquidation[lastWorkDay][month]']").val();
    var year = form.find("select[name='rocketseller_twopickbundle_liquidation[lastWorkDay][year]']").val();

    var processDate = (0 + day).slice(-2) + "-" + (0 + month).slice(-2) + "-" + year;

    var employer_has_employee_id = form.find("input[name='rocketseller_twopickbundle_liquidation[idEmperHasEmpee]']").val();
    var username = form.find("input[name='rocketseller_twopickbundle_liquidation[username]']").val();

    var url = form.find("input[name='rocketseller_twopickbundle_liquidation[api_public_post_preview_pre_liquidation]']").val();
    console.log(url);
    var frequency = form.find("input[name='rocketseller_twopickbundle_liquidation[frequency]']").val();

    $("#liquidationValue").html('<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>')

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            employee_id: employer_has_employee_id,
            username: username,
            year: year,
            month: month,
            day: day,
            frequency: frequency,
            cutDate: processDate,
            processDate: processDate,
            retirementCause: form.find("input[name='rocketseller_twopickbundle_liquidation[liquidationReason]']:checked").val()
        }
    }).done(function (data) {
        $("#liquidationValue").html(getPrice(data["totalLiq"]["total"]));
        $("#liquidation-newstep1").removeAttr("disabled");
    }).fail(function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
    });
});

function getPrice(valor) {
    price = parseFloat(valor.toString().replace(/,/g, ""))
            .toFixed(0)
            .toString()
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    return "$ " + price;
}