/**
 * Created by gabrielsamoma on 1/12/16.
 */
function startAfiliation() {
    var validator;
    $.getScript("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("form[name='register_social_security']").validate({
            rules: {
                "register_social_security[severances]": "required",
                "register_social_security[arl]": "required"
            },
            messages: {
                "register_social_security[severances]": "Por favor seleccione su caja de compensación",
                "register_social_security[arl]": "Por favor seleccione su ARL"
            }
        });
        $("select").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "Por favor seleccione una opción"
                }
            });
        });
    });

    $("#chkAcept").on('click', function () {
        if ($(this).is(':checked')) {
            $("#btn-1").removeClass('disabled');
        } else {
            $("#btn-1").addClass('disabled');
        }
    });

    $('.btnPrevious').click(function () {
        $('.nav-tabs > .active').prev('li').find('a').trigger('click');
    });
    $('#btn-1').click(function (e) {
        e.preventDefault();
        var form = $("form");
        var idEmployees = [], beneficiaries = [], pension = [], wealth = [];
        var i = 0;
        $(form).find("input[name*='[idEmployerHasEmployee]']").each(function () {
            idEmployees[i++] = $(this).val();
        });
        i = 0;
        var flagValid = true;
        $(form).find("select[name*='[wealth]']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
            wealth[i++] = $(this).val();
        });
        i = 0;
        $(form).find("select[name*='[pension]']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
            pension[i++] = $(this).val();
        });
        i = 0;
        $(form).find("input[name*='[beneficiaries]']:checked").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
            beneficiaries[i++] = $(this).val();
        });

        if (!flagValid) {
            alert("Llenaste algunos campos incorrectamente");
            return;
        }

        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
//            data: {
//                idEmployerHasEmployee: idEmployees,
//                beneficiaries: beneficiaries,
//                pension: pension,
//                wealth: wealth,
//                idEmployer: $(form).find("input[name='register_social_security[idEmployer]']").val(),
//            }
            data: form.serialize()
        }).done(function (data) {
            console.log(data);
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            console.log(jqXHR);
        });
    });
    $('#btn-2').click(function (e) {
        e.preventDefault();
        var form = $("form");

        var severances = $(form).find("select[name='register_social_security[severances]']");
        var arl = $(form).find("select[name='register_social_security[arl]']");
        if (!(validator.element(severances) && validator.element(arl))) {
            alert("Llenaste algunos campos incorrectamente");
            return;
        }

        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
//            data: {
//                idEmployer: 			$(form).find("input[name='register_social_security[idEmployer]']").val(),
//                severances: 			$(form).find("select[name='register_social_security[severances]']").val(),
//                arl: 					$(form).find("select[name='register_social_security[arl]']").val(),
//                economicalActivity: 	$(form).find("input[name='register_social_security[economicalActivity]']").val(),
//            }
            data: form.serialize()
        }).done(function (data) {
            console.log(data);
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            console.log(jqXHR);
        });
    });
}