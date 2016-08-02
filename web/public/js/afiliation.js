/**
 * Created by gabrielsamoma on 1/12/16.
 */
function startAfiliation() {
    var validator;
    $.getScript("//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
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
        $("input[class*='autocom']").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "Por favor escribe en el campo, hasta encontrar la entidad o a la cual te gustaría ser afiliado"
                }
            });
        });
    });
    initEntitiesFields();

    $(".hidden").each(function () {
        $(this).hide();
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
        $(form).find("input[name*='[wealthAC]']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });
        i = 0;
        $(form).find("input[name*='[pensionAC]']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
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
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if(jqXHR==errorHandleTry(jqXHR)){
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
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
            if(jqXHR==errorHandleTry(jqXHR)){
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    function initEntitiesFields(){
        var dataPen=[];
        $("#register_social_security_employerHasEmployees_0_pension").find("> option").each(function() {
            dataPen.push({'label':this.text,'value':this.value});
        });
        $(".autocomP").each(function () {
            var autoTo=$(this);
            $(this).autocomplete({
                source: function(request, response) {
                    var results = $.ui.autocomplete.filter(dataPen, request.term);

                    response(results.slice(0, 5));
                },
                minLength: 0,
                select: function(event, ui) {
                    event.preventDefault();
                    autoTo.val(ui.item.label);
                    $(autoTo.parent()).parent().parent().find("select").val(ui.item.value);
                },
                focus: function(event, ui) {
                    event.preventDefault();
                    autoTo.val(ui.item.label);

                }
            });
            $(this).on("focus",function () {
                $(autoTo).autocomplete("search", $(autoTo).val());
            });
        });
        var dataWe=[];
        $("#register_social_security_employerHasEmployees_0_wealth").find("> option").each(function() {
            dataWe.push({'label':this.text,'value':this.value});
        });
        $(".autocomW").each(function () {
            var autoTo=$(this);
            $(this).autocomplete({
                source: function(request, response) {
                    var results = $.ui.autocomplete.filter(dataWe, request.term);

                    response(results.slice(0, 5));
                },                minLength: 0,
                select: function(event, ui) {
                    event.preventDefault();
                    autoTo.val(ui.item.label);
                    $(autoTo.parent()).parent().parent().find("select").val(ui.item.value);
                },
                focus: function(event, ui) {
                    event.preventDefault();
                    autoTo.val(ui.item.label);

                }
            });
            $(this).on("focus",function () {
                $(autoTo).autocomplete("search", $(autoTo).val());
            });

        });
        var dataSev=[];
        $("#register_social_security_severances").find("> option").each(function() {
            dataSev.push({'label':this.text,'value':this.value});
        });
        $(".autocomS").each(function () {
            var autoTo=$(this);
            $(this).autocomplete({
                source: function(request, response) {
                    var results = $.ui.autocomplete.filter(dataSev, request.term);

                    response(results.slice(0, 5));
                },                minLength: 0,
                select: function(event, ui) {
                    event.preventDefault();
                    autoTo.val(ui.item.label);
                    $(autoTo.parent()).parent().parent().find("select").val(ui.item.value);
                },
                focus: function(event, ui) {
                    event.preventDefault();
                    autoTo.val(ui.item.label);

                }
            });
            $(this).on("focus",function () {
                $(autoTo).autocomplete("search", $(autoTo).val());
            });

        });
        var dataArl=[];
        $("#register_social_security_arl").find("> option").each(function() {
            dataArl.push({'label':this.text,'value':this.value});
        });
        $(".autocomA").each(function () {
            var autoTo=$(this);
            $(this).autocomplete({
                source: function(request, response) {
                    var results = $.ui.autocomplete.filter(dataArl, request.term);

                    response(results.slice(0, 5));
                },
                minLength: 0,
                select: function(event, ui) {
                    event.preventDefault();
                    autoTo.val(ui.item.label);
                    $(autoTo.parent()).parent().parent().find("select").val(ui.item.value);
                },
                focus: function(event, ui) {
                    event.preventDefault();
                    autoTo.val(ui.item.label);

                }
            });
            $(this).on("focus",function () {
                $(autoTo).autocomplete("search", $(autoTo).val());
            });

        });
        var severances = $("select[name='register_social_security[severances]']");
        var arl = $("select[name='register_social_security[arl]']");
        $("#register_social_security_severancesAC").val($(severances).children("option:selected").text());
        $("#register_social_security_arlAC").val($(arl).children("option:selected").text());

    }
}
