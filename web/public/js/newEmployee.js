/**
 * Created by gabrielsamoma on 11/11/15.
 */

function startEmployee() {
    var validator;
    $.getScript("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("form[name='register_employee']").validate({
            //onfocusout: true,
            rules: {
                "register_employee[person][documentType]": "required",
                "register_employee[person][document]": {required: true, number: true},
                "register_employee[person][names]": "required",
                "register_employee[person][lastName1]": "required",
                "register_employee[person][mainAddress]": "required",
                //"register_employee[employeeHasEmployers][salary]": "required",
                "register_employee[person][department]": "required",
                "register_employee[person][city]": "required",
                "register_employee[personExtra][civilStatus]": "required",
                "register_employee[personExtra][gender]": "required",
                "register_employee[personExtra][documentExpeditionDate]": "required",
                "register_employee[personExtra][documentExpeditionPlace]": "required",
                "register_employee[personExtra][birthCountry]": "required",
                "register_employee[personExtra][birthDepartment]": "required",
                "register_employee[personExtra][birthCity]": "required",
                "register_employee[employeeHasEmployers][employeeType]": "required",
                "register_employee[employeeHasEmployers][contractType]": "required",
                "register_employee[employeeHasEmployers][timeCommitment]": "required",
                "register_employee[employeeHasEmployers][position]": "required",
                "register_employee[employeeHasEmployers][workplaces]": "required",
                "register_employee[employeeHasEmployers][transportAid]": "required",
                "register_employee[employeeHasEmployers][payMethod]": "required",
                "register_employee[employeeHasEmployers][frequencyFrequency]": "required"/*,
                 "register_employee[credit_card]": "required",
                 "register_employee[cvv]": "required",
                 "register_employee[expiry_date]": "required",
                 "register_employee[name_on_card]": "required"*/

            },
            messages: {
                "register_employee[person][documentType]": "Por favor selecciona un tipo de documento",
                "register_employee[person][document]": {
                    required: "Por favor ingresa un documento",
                    number: "ingresa solamente dígitos"
                },
                "register_employee[person][names]": "Por favor ingresa el nombre",
                "register_employee[person][lastName1]": "Por favor ingresa el primer apellido",
                "register_employee[person][mainAddress]": "Por favor ingresa una dirección",
                //"register_employee[employeeHasEmployers][salary]": "Por favor ingresa un salario",
                "register_employee[person][department]": "Por favor selecciona un departamento",
                "register_employee[person][city]": "Por favor selecciona una ciudad",
                "register_employee[personExtra][civilStatus]": "Por favor selecciona una opción",
                "register_employee[personExtra][gender]": "Por favor selecciona una opción",
                "register_employee[personExtra][documentExpeditionDate]": "Por favor selecciona una opción",
                "register_employee[personExtra][documentExpeditionPlace]": "Por favor selecciona una opción",
                "register_employee[personExtra][birthCountry]": "Por favor selecciona una opción",
                "register_employee[personExtra][birthDepartment]": "Por favor selecciona una opción",
                "register_employee[personExtra][birthCity]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][employeeType]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][contractType]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][timeCommitment]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][position]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][workplaces]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][transportAid]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][payMethod]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][frequencyFrequency]": "Por favor selecciona una opción"/*,
                 "register_employee[credit_card]": "Por favor ingresa el número de la tarjeta",
                 "register_employee[cvv]": "Por favor ingresa el código de seguridad de la tarjeta",
                 "register_employee[expiry_date]": "Por favor ingresa la fecha de expiración de la tarjeta",
                 "register_employee[name_on_card]": "Por favor ingresa el nombre del titular de la tarjeta"*/
            }
        });
        $("ul.phones input[name*='phoneNumber']").each(function () {
            $(this).rules("add", {
                minlength: 7,
                required: true,
                number: true,
                messages: {
                    minlength: "Por favor ingresa un número valido",
                    required: "Por favor ingresa un número de telefono",
                    number: "Por favor ingresa solo digitos"
                }
            });
        });
        $("ul.benefits input[name*='amount']").each(function () {
            $(this).rules("add", {
                required: true,
                number: true,
                messages: {
                    required: "Por favor ingresa una cantidad",
                    number: "Por favor ingresa solo digitos"
                }
            });
        });
        $("input[class*='autocom']").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "Por favor seleccione una opción"
                }
            });
        });
    });
    $('.btnPrevious-form').click(function () {
        $('#formNav > .active').prev('li').find('a').trigger('click');
    });
    $('.btnPrevious-contract').click(function () {
        $('#contractNav > .active').prev('li').find('a').trigger('click');
    });
    $('.btnNext-contract').click(function () {
        $('#contractNav > .active').next('li').find('a').trigger('click');
    });
    //dinamic loading contract type and commitment
    //first hide all
    $(".days").each(function () {
        $(this).hide();
    });
    $(".definite").each(function () {
        $(this).hide();
    });
    $("#existentDataToShow").hide();
    var timeCommitment = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']");
    timeCommitment.change(function () {
        var selectedVal = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text();
        if (selectedVal == " Trabajo por días") {
            $(".days").each(function () {
                $(this).show();
            });
            $(".complete").each(function () {
                $(this).hide();
            });
        } else {
            $(".days").each(function () {
                $(this).hide();
            });
            $(".complete").each(function () {
                $(this).show();
            });
        }
    });
    $("#register_employee_employeeHasEmployers_startDate").on("change", function () {
        if (!checkDate(new Date(
                $(this).find("#register_employee_employeeHasEmployers_startDate_year").val(),
                parseInt($(this).find("#register_employee_employeeHasEmployers_startDate_month").val()) - 1,
                $(this).find("#register_employee_employeeHasEmployers_startDate_day").val()
            ))) {

            var datenow = new Date();
            var year = datenow.getFullYear();
            var month = datenow.getMonth();
            var day = datenow.getDate();

            $(this).find("#register_employee_employeeHasEmployers_startDate_year").val(year);
            $(this).find("#register_employee_employeeHasEmployers_startDate_month").val(month + 1);
            $(this).find("#register_employee_employeeHasEmployers_startDate_day").val(day);
        }

    });
    $("#register_employee_employeeHasEmployers_endDate").on("change", function () {
        if (!checkDate(new Date(
                $(this).find("#register_employee_employeeHasEmployers_endDate_year").val(),
                parseInt($(this).find("#register_employee_employeeHasEmployers_endDate_month").val()) - 1,
                $(this).find("#register_employee_employeeHasEmployers_endDate_day").val()
            ))) {
            var datenow = new Date();
            var year = datenow.getFullYear();
            var month = datenow.getMonth();
            var day = datenow.getDate();

            $(this).find("#register_employee_employeeHasEmployers_endDate_year").val(year + 1);
            $(this).find("#register_employee_employeeHasEmployers_endDate_month").val(month + 1);
            $(this).find("#register_employee_employeeHasEmployers_endDate_day").val(day);
        }
    });
    var selectedVal = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text();
    if (selectedVal == " Trabajo por días") {
        $(".days").each(function () {
            $(this).show();
        });
        $(".complete").each(function () {
            $(this).hide();
        });
    } else {
        $(".days").each(function () {
            $(this).hide();
        });

        $(".complete").each(function () {
            $(this).show();
        });
    }
    $("#register_employee_person_documentType").change(function () {
        var selectedVal = $(this).find("option:selected").text();
        if (selectedVal == "Tarjeta de identidad") {
            $('#TIModal').modal('toggle');
        }
    });
    $('#btnToggleFijo').click(function () {
        $("#register_employee_employeeHasEmployers_contractType").val("2")
        $(".definite").each(function () {
            $(this).show();
        });
        $('#contractIndefinido').hide();
        $('#contractFijo').show();
        $(this).addClass('active');
        $('#btnToggleIndefinido').removeClass('active');
    });

    $('#btnToggleIndefinido').click(function () {
        $("#register_employee_employeeHasEmployers_contractType").val("1")
        $(".definite").each(function () {
            $(this).hide();
        });
        $('#contractIndefinido').show();
        $('#contractFijo').hide();
        $(this).addClass('active');
        $('#btnToggleFijo').removeClass('active');
    });
    var contractType = $("#register_employee_employeeHasEmployers_contractType");
    selectedVal = $(contractType).find("option:selected").text();
    if (selectedVal == "Término fijo") {
        $("#btnToggleFijo").trigger("click");
        $(".definite").each(function () {
            $(this).show();
        });
    } else {
        $("#btnToggleIndefinido").trigger("click");
        $(".definite").each(function () {
            $(this).hide();
        });
    }
    //funcion que agrega un listener a cada department
    addListeners();
    var payType = $("input[name='register_employee[employeeHasEmployers][payMethod]']");
    var payTypeChecked = $("input[name='register_employee[employeeHasEmployers][payMethod]']:checked");
    if (payTypeChecked.val() != null) {
        $.ajax({
            url: '/pay/method/fields/' + payTypeChecked.val() + '/' + $("input[name='register_employee[idContract]']").val(),
            type: 'GET'
        }).done(function (data) {
            $('#putFields_' + payTypeChecked.val()).html(
                // ... with the returned one from the AJAX response.
                $(data).find('#formFields'));
            if ($(payTypeChecked).parent().text() == " Daviplata") {
                payMethodListener();
            }
        });
    }

    payType.on('change', function () {
        var payMethod = $("input[name='register_employee[employeeHasEmployers][payMethod]']:checked");
        $.ajax({
            url: '/pay/method/fields/' + payMethod.val(),
            type: 'GET'
        }).done(function (data) {
            var $putFields = $('#putFields_' + payMethod.val()).html(
                // ... with the returned one from the AJAX response.
                $(data).find('#formFields'));
            $('#putFields_' + payTypeChecked.val()).html("");
            if ($(payMethod).parent().text() == " Daviplata") {
                payMethodListener();
            }
            payTypeChecked = payMethod;

        });
    });

    $("form").on("submit", function (e) {
        e.preventDefault();
        var form = $("form");
        var idsBenef = [], idsWorkpl = [];
        var i = 0;
        $(form).find("ul.benefits select[name*='benefits']").each(function () {
            idsBenef[i++] = $(this).val();
        });
        i = 0;
        $(form).find("ul.workplaces select[name*='workplaces']").each(function () {
            idsWorkpl[i++] = $(this).val();
        });

        if (!form.valid()) {
            return;
        }

        $.ajax({
            url: form.attr('action'),
            type: $(form).attr('method'),
            data: {
                payTypeId: $(form).find("input[name='register_employee[employeeHasEmployers][payMethod]']:checked").val(),
                bankId: $(form).find("select[name='method_type_fields[bankBank]']").val(),
                accountTypeId: $(form).find("select[name='method_type_fields[accountTypeAccountType]']").val(),
                frequencyId: $(form).find("select[name='register_employee[employeeHasEmployers][frequencyFrequency]']").val(),
                accountNumber: $(form).find("input[name='method_type_fields[accountNumber]']").val(),
                cellphone: $(form).find("input[name='method_type_fields[cellphone]']").val(),
                hasIt: $(form).find("select[name='method_type_fields[hasIt]']").val(),
                contractId: $(form).find("input[name='register_employee[idContract]']").val(),
            }
        }).done(function (data) {
            $('#formNav > .active').next('li').find('a').trigger('click');
        }).fail(function (data, textStatus, errorThrown) {
            if (jqXHR == errorHandleTry(jqXHR)) {
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    var $collectionHolderPhones;
    /*
     var $addPhoneLink = $('<a href="#" class="add_phone_link">Add Phone</a>');
     var $newLinkLiPhone = $('<li></li>').append($addPhoneLink)
     $collectionHolderPhones = $('ul.phones');
     $collectionHolderPhones.find('li').each(function() {
     addTagFormDeleteLink($(this));
     });
     $collectionHolderPhones.append($newLinkLiPhone);
     $collectionHolderPhones.data('index', $collectionHolderPhones.find(':input').length);
     $addPhoneLink.on('click', function(e) {
     // prevent the link from creating a "#" on the URL
     e.preventDefault();
     addPhoneForm($collectionHolderPhones, $newLinkLiPhone);
     });
     */

    var $collectionHolderB;
    var $collectionHolderW;
    $("#toHide").children().hide();
    var $addBenefitLink =
        $('<a href="#" class="col-md-4 add_phone_link" style="padding-top:2px !important;padding:10px;color:#00cdcc;text-decoration: none;">' +
            '<i class="fa fa-plus-circle" style="color:#00cdcc;"></i> Adicionar nuevo beneficio</a>');
    var $newLinkLi = $('<li></li>').append($addBenefitLink);
    // Get the ul that holds the collection of benefits
    $collectionHolderB = $('ul.benefits');
    $collectionHolderW = $('ul.workplaces');
    //add remove links
    $collectionHolderB.find('li').each(function () {
        addTagFormDeleteLink($(this));
    });
    $collectionHolderW.find('li').each(function () {
        addTagFormDeleteLink($(this));
    });
    // add the "add a tag" anchor and li to the tags ul
    $collectionHolderB.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    $collectionHolderB.data('index', $collectionHolderB.find(':input').length);
    $collectionHolderW.data('index', $collectionHolderW.find(':input').length);
    $addBenefitLink.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        addBenefitForm($collectionHolderB, $newLinkLi);
    });
    $('#si_tiene_contrato').change(function (evt) {
        $('#noTarget').collapse('hide')
    });
    $('#no_tiene_contrato').change(function (evt) {
        $('#noTarget').collapse('show')
    });
    $('#btn-inquiry').click(function (e) {
        e.preventDefault();
        inquiry();
    });
    $('#btn-entities').click(function (e) {
        e.preventDefault();
        var form = $("form");
        var i = 0;
        var flagValid = true;
        $(form).find("select[name*='[wealth]']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });
        $(form).find("select[name*='[pension]']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });
        $(form).find("input[name*='[wealthAC]']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });
        $(form).find("input[name*='[pensionAC]']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });
        $(form).find("input[name*='[beneficiaries]']:checked").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });

        if (!flagValid) {
            return;
        }

        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                idContract: $("input[name='register_employee[idContract]']").val(),
                beneficiaries: $("input[name='register_employee[entities][beneficiaries]']:checked").val(),
                pension: $("#register_employee_entities_pension").val(),
                wealth:  $("#register_employee_entities_wealth").val(),
                idEmployee: $("#register_employee_idEmployee").val()
            }
        }).done(function (data) {
            history.pushState("", "", data["url"]);
            sendAjax(data["url"]);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if(jqXHR==errorHandleTry(jqXHR)){
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    $('#btn-1').click(function (e) {
        e.preventDefault();
        var form = $("form");
        var documentType = $(form).find("select[name='register_employee[person][documentType]']");
        var document = $(form).find("input[name='register_employee[person][document]']");
        var names = $(form).find("input[name='register_employee[person][names]']");
        var lastName1 = $(form).find("input[name='register_employee[person][lastName1]']");
        var lastName2 = $(form).find("input[name='register_employee[person][lastName2]']");

        if (!form.valid()) {
            return;
        }

        //if (!(validator.element(documentType) || validator.element(document) || validator.element(names) || validator.element(lastName1))) {
        //alert("Llenaste algunos campos incorrectamente");
        //return;
        //}

        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                documentType: documentType.val(),
                document: document.val(),
                names: names.val(),
                lastName1: lastName1.val(),
                lastName2: lastName2.val(),
                civilStatus: $(form).find("select[name='register_employee[personExtra][civilStatus]']").val(),
                year: $(form).find("select[name='register_employee[person][birthDate][year]']").val(),
                month: $(form).find("select[name='register_employee[person][birthDate][month]']").val(),
                day: $(form).find("select[name='register_employee[person][birthDate][day]']").val(),
                documentExpeditionDateYear: $(form).find("select[name='register_employee[personExtra][documentExpeditionDate][year]']").val(),
                documentExpeditionDateMonth: $(form).find("select[name='register_employee[personExtra][documentExpeditionDate][month]']").val(),
                documentExpeditionDateDay: $(form).find("select[name='register_employee[personExtra][documentExpeditionDate][day]']").val(),
                birthCountry: $(form).find("select[name='register_employee[personExtra][birthCountry]']").val(),
                birthDepartment: $(form).find("select[name='register_employee[personExtra][birthDepartment]']").val(),
                birthCity: $(form).find("select[name='register_employee[personExtra][birthCity]']").val(),
                gender: $(form).find("select[name='register_employee[personExtra][gender]']").val(),
                employeeId: $(form).find("input[name='register_employee[idEmployee]']").val(),
                documentExpeditionPlace: $(form).find("input[name='register_employee[personExtra][documentExpeditionPlace]']").val(),
            }
        }).done(function (data) {
            console.log(data);
            $(form).find("input[name='register_employee[idEmployee]']").val(data['response']['idEmployee']);
            history.pushState("", "", "/register/employee/" + data['response']['idEmployee']);
            $('#formNav > .active').next('li').find('a').trigger('click');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if (jqXHR == errorHandleTry(jqXHR)) {
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    $('#btn-2').click(function (e) {
        e.preventDefault();
        var form = $("form");
        var idsPhones = [], phones = [];
        var mainAddress = $(form).find("input[name='register_employee[person][mainAddress]']");

        var department = $(form).find("select[name='register_employee[person][department]']");
        var city = $(form).find("select[name='register_employee[person][city]']");
        if (!form.valid()) {
            return;
        }
        //if (!(validator.element(mainAddress) || validator.element(department) || validator.element(city))) {
        //alert("Llenaste algunos campos incorrectamente");
        //    return;
        //}
        var i = 0;
        $(form).find("ul.phones input[name*='id']").each(function () {
            idsPhones[i++] = $(this).val();
        });
        i = 0;
        var flagValid = true;
        $(form).find("ul.phones input[name*='phoneNumber']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
            phones[i++] = $(this).val();
        });
        if (!flagValid) {
            //alert("Llenaste algunos campos incorrectamente");
            return;
        }
        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                mainAddress: mainAddress.val(),
                phonesIds: idsPhones,
                phones: phones,
                department: $(form).find("select[name='register_employee[person][department]']").val(),
                city: $(form).find("select[name='register_employee[person][city]']").val(),
                email: $(form).find("input[name='register_employee[personExtra][email]']").val(),
                employeeId: $(form).find("input[name='register_employee[idEmployee]']").val(),
            }
        }).done(function (data) {
            $('#formNav > .active').next('li').find('a').trigger('click');
            if($("#register_employee_employeeHasEmployers_contractType").val()=="2"){
                $("#contractIndefiniteModal").modal("toggle");
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if (jqXHR == errorHandleTry(jqXHR)) {
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    $('#btn-3').click(function (e) {
        e.preventDefault();
        var form = $("form");

        //var employeeType = $(form).find("select[name='register_employee[employeeHasEmployers][employeeType]']");
        var contractType = $(form).find("select[name='register_employee[employeeHasEmployers][contractType]']");
        var timeCommitment = $(form).find("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked");
        var position = $(form).find("select[name='register_employee[employeeHasEmployers][position]']");
        var idWorkplace = $(form).find("select[name='register_employee[employeeHasEmployers][workplaces]']");
        if (!form.valid()) {
            return;
        }
        //if (!(validator.element(employeeType) || validator.element(contractType) || validator.element(timeCommitment) || validator.element(position) || validator.element(idWorkplace))) {
        //alert("Llenaste algunos campos incorrectamente");
        //    return;
        //}

        var idsBenef = [], amountBenef = [], periodicityBenef = [], weekWorkableDaysIds = [], benefType = [];
        var flagValid = true;

        var i = 0;
        /*
         $(form).find("ul.benefits input[name*='idContractHasBenefits']").each(function () {
         idsBenef[i++] = $(this).val();
         });
         i = 0;
         $(form).find("ul.benefits select[name*='benefitType']").each(function () {
         if (!validator.element($(this))) {
         flagValid = false;
         return;
         }
         benefType[i++] = $(this).val();
         });
         i = 0;
         $(form).find("ul.benefits input[name*='amount']").each(function () {
         if (!validator.element($(this))) {
         flagValid = false;
         return;
         }
         amountBenef[i++] = $(this).val();
         });
         i = 0;
         $(form).find("ul.benefits select[name*='periodicity']").each(function () {
         if (!validator.element($(this))) {
         flagValid = false;
         return;
         }
         periodicityBenef[i++] = $(this).val();
         });
         */

        i = 0;
        $(form).find("[name='register_employee[employeeHasEmployers][weekDays][]']:checked").each(function () {
            weekWorkableDaysIds[i++] = $(this).val();
        });
        var salary = $(form).find("input[name='register_employee[employeeHasEmployers][salary]']");
        /*if (!(validator.element(salary))) {
         //alert("Llenaste algunos campos incorrectamente");
         return;
         }*/

        if (!flagValid) {
            //alert("Llenaste algunos campos incorrectamente");
            return;
        }
        if (!validateSalary()) {
            return false;
        }
        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            beforeSend: function (xhr) {

            },
            data: {
                //employeeType: employeeType.val(),
                contractType: contractType.val(),
                timeCommitment: timeCommitment.val(),
                position: position.val(),
                salary: accounting.unformat(salary.val()),
                salaryD: accounting.unformat($("#register_employee_employeeHasEmployers_salaryD").val()),
                idsBenefits: idsBenef,
                benefType: benefType,
                amountBenefits: amountBenef,
                periodicityBenefits: periodicityBenef,
                idWorkplace: idWorkplace.val(),
                transportAid: $(form).find("input[name='register_employee[employeeHasEmployers][transportAid]']:checked").val(),
                sisben: $(form).find("input[name='register_employee[employeeHasEmployers][sisben]']:checked").val(),
                //benefitsConditions: $(form).find("textarea[name='register_employee[employeeHasEmployers][benefitsConditions]']").val(),
                employeeId: $(form).find("input[name='register_employee[idEmployee]']").val(),
                startDate: $("#register_employee_employeeHasEmployers_startDate_year").val()+"-"+$("#register_employee_employeeHasEmployers_startDate_month").val()+"-"+$("#register_employee_employeeHasEmployers_startDate_day").val(),
                endDate: $("#register_employee_employeeHasEmployers_endDate_year").val()+"-"+$("#register_employee_employeeHasEmployers_endDate_month").val()+"-"+$("#register_employee_employeeHasEmployers_endDate_day").val(),
                weekDays: weekWorkableDaysIds,
                //workableDaysMonth: $(form).find("select[name='register_employee[employeeHasEmployers][workableDaysMonth]']").val(),
                //workTimeStart: {'hour': $(form).find("select[name='register_employee[employeeHasEmployers][workTimeStart][hour]']").val(),
                //    'minute': $(form).find("select[name='register_employee[employeeHasEmployers][workTimeStart][minute]']").val()},
                //workTimeEnd: {'hour': $(form).find("select[name='register_employee[employeeHasEmployers][workTimeEnd][hour]']").val(),
                //    'minute': $(form).find("select[name='register_employee[employeeHasEmployers][workTimeEnd][minute]']").val()},
                weekWorkableDays: $(form).find("#register_employee_employeeHasEmployers_weekWorkableDays").val(),
                contractId: $(form).find("input[name='register_employee[idContract]']").val()
            }
        }).done(function (data) {
            $('#formNav > .active').next('li').find('a').trigger('click');
            $(form).find("input[name='register_employee[idContract]']").val(data['response']['idContract']);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if (jqXHR == errorHandleTry(jqXHR)) {
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    loadConstrains();
}
function addPhoneForm($collectionHolderB, $newLinkLi) {
    var prototype = $collectionHolderB.data('prototype');
    var index = $collectionHolderB.data('index');
    var newForm = prototype.replace(/__name__/g, index);
    $collectionHolderB.data('index', index + 1);
    var $newFormLi = $('<li></li>').append(newForm);
    addTagFormDeleteLink($newFormLi);
    $newLinkLi.before($newFormLi);
}
function addBenefitForm($collectionHolderB, $newLinkLi) {
    var prototype = $collectionHolderB.data('prototype');
    var index = $collectionHolderB.data('index');
    var newForm = prototype.replace(/__name__/g, index);
    $collectionHolderB.data('index', index + 1);
    var $newFormLi = $('<li></li>').append(newForm);
    addTagFormDeleteLink($newFormLi);
    $newLinkLi.before($newFormLi);
}
function addTagFormDeleteLink($tagFormLi) {
    var $removeFormA = $('<a href="#" style="padding:10px;color:#fd5c5c;text-decoration: none;"><i class="fa fa-minus-circle " style="color:#fd5c5c;max-width: 30px;"></i> Eliminar este beneficio</a>');
    $tagFormLi.append($removeFormA);

    $removeFormA.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // remove the li for the tag form
        $tagFormLi.remove();
    });
}
function jsonToHTML(data) {
    var htmls = "<option value=''>Seleccionar una opción</option>";
    for (var i = 0; i < data.length; i++) {
        htmls += "<option value='" + data[i].id_city + "'>" + data[i].name + "</option>";
    }
    return htmls;
}
function jsonCalcToHTML(data) {
    var htmls = "<h2 class='modal-title'>Si su empleado tiene estas características debe pagar:</h2>" +
        "<ul class='lista_listo clearfix'>";
    htmls += "<li class='col-sm-6'><span class='titulo'><strong>Costo total</strong><br/>para el empleador</span> <span class='cifra'>" + getPrice(Math.floor(data.totalExpenses)) + "</span></li>";
    htmls += "<li class='col-sm-6'><span class='titulo'><strong>Ingreso neto</strong><br />para el empleado</span> <span class='cifra'>" + getPrice(Math.floor(data.totalIncome)) + "</span></li>";
    htmls += "<li class='col-sm-6'><span class='cifra'>" + getPrice(Math.floor(data.dailyExpenses)) + "</span></li>";
    htmls += "<li class='col-sm-6'><span class='cifra'>" + getPrice(Math.floor(data.dailyIncome)) + "</span></li>";
    htmls += "</ul>";
    htmls += "<h2 class='modal-title'>Detalles:</h2>" +
        "<ul class='lista_listo_detalle'>";
    htmls += "<li>Gastos Empleador EPS: " + getPrice(Math.floor(data.EPSEmployerCal)) + "</li>";
    htmls += "<li>Gastos Empleador Pensión: " + getPrice(Math.floor(data.PensEmployerCal)) + "</li>";
    htmls += "<li>Gastos Empleado ARL: " + getPrice(Math.floor(data.arlCal)) + "</li>";
    htmls += "<li>Gastos Empleado Cesantias: " + getPrice(Math.floor(data.cesCal)) + "</li>";
    htmls += "<li>Gastos Empleado Intereses/cesantias: " + getPrice(Math.floor(data.taxCesCal)) + "</li>";
    htmls += "<li>Gastos Empleado Caja Comp: " + getPrice(Math.floor(data.cajaCal)) + "</li>";
    htmls += "<li>Gastos Empleado Vacaciones: " + getPrice(Math.floor(data.vacationsCal)) + "</li>";
    htmls += "<li>Gastos Auxilio de Trasnporte: " + getPrice(Math.floor(data.transportCal)) + "</li>";
    htmls += "<li>Gastos Dotacion/150000 pesos trimestre: " + getPrice(Math.floor(data.dotationCal)) + "</li>";
    htmls += "<li>Gastos SENA: " + getPrice(Math.floor(data.senaCal)) + "</li>";
    htmls += "<li>Gastos ICBF: " + getPrice(Math.floor(data.icbfCal)) + "</li>";
    htmls += "<li>Gastos Empleado EPS: " + getPrice(Math.floor(data.EPSEmployeeCal)) + "</li>";
    htmls += "<li>Gastos Empleado Pensión: " + getPrice(Math.floor(data.PensEmployeeCal)) + "</li>";
    htmls += "</ul>";
    return htmls;
}

function addListeners() {
    $("#ex6").bootstrapSlider();
    $("#ex6").on("slide", function (slideEvt) {
        $("#register_employee_employeeHasEmployers_salaryD").val(slideEvt.value);
        calculator("d");
        formatMoney($("#totalExpensesVal"));
        formatMoney($("#register_employee_employeeHasEmployers_salaryD"));
    });
    $("#ex7").bootstrapSlider();
    $("#ex7").on("slide", function (slideEvt) {
        $("#register_employee_employeeHasEmployers_salary").val(slideEvt.value);
        calculator("m");
        formatMoney($("#totalExpensesVal"));
        formatMoney($("#register_employee_employeeHasEmployers_salary"));
    });
    initEntitiesFields();

    $(".hidden").each(function () {
        $(this).hide();
    });

    $("#chkAcept").on('click', function () {
        if ($(this).is(':checked')) {
            $("#btn-entities").removeClass('disabled');
        } else {
            $("#btn-entities").addClass('disabled');
        }
    });
    var documentType = $("select[name='register_employee[person][documentType]']");
    var document = $("input[name='register_employee[person][document]']");
    var lastName1 = $("input[name='register_employee[person][lastName1]']");
    $(documentType).blur(function () {
        if (documentType.val() != "" && document.val() != "" && lastName1.val() != "") {
            inquiry();
        }
    });
    $(document).blur(function () {
        if (documentType.val() != "" && document.val() != "" && lastName1.val() != "") {
            inquiry();
        }
    });
    $(lastName1).blur(function () {
        if (documentType.val() != "" && document.val() != "" && lastName1.val() != "") {
            inquiry();
        }
    });
    $("input[name='register_employee[employeeHasEmployers][transportAid]']").on("change", function () {
        calculator();
        formatMoney($("#totalExpensesVal"));
        formatMoney($("#register_employee_employeeHasEmployers_salary"));
    });
    $("#addWorplace").on("click", function (e) {
        e.preventDefault();
        $("#newWorkplaceModal").modal("show");
    });
    $("#saveWorplaceModal").on("click", function (e) {
        e.preventDefault();
        var nameW=$("#register_employee_employeeHasEmployers_workplace_name");
        var addW=$("#register_employee_employeeHasEmployers_workplace_mainAddress");
        var cityId=$("#register_employee_employeeHasEmployers_workplace_city");
        var deptId=$("#register_employee_employeeHasEmployers_workplace_department");
        $.ajax({
            url: $(this).attr('href'),
            type: "POST",
            data: {
                workName: nameW.val(),
                workMainAddress: addW.val(),
                workCity: cityId.val(),
                workDepartment: deptId.val(),
            }
        }).done(function (data) {
            $("#newWorkplaceModal").modal("hide");
            $("#register_employee_employeeHasEmployers_workplaces").append($('<option>', {
                value: data["idWorkplace"],
                text: nameW.val()
            }));
        }).fail(function (data, textStatus, errorThrown) {
            $("#newWorkplaceModal").modal("hide");
            if (jqXHR == errorHandleTry(jqXHR)) {
                $("#errorModal").modal("show");
            }
        });
    });


    $("#register_employee_employeeHasEmployers_weekDays").on("change", function () {
        var i = 0;
        $("[name='register_employee[employeeHasEmployers][weekDays][]']:checked").each(function () {
            i++;
        });
        $("#register_employee_employeeHasEmployers_weekWorkableDays").val(i);
        calculator();
        formatMoney($("#totalExpensesVal"));
    });
    $("#register_employee_employeeHasEmployers_existent").on("click", function () {
        $('#existentQuestion').hide();
        $("#existentDataToShow").show();
    });
    $("#register_employee_employeeHasEmployers_new").on("click", function () {
        $('#formNav > .active').next('li').find('a').trigger('click');
    });
    $("#register_employee_employeeHasEmployers_yesExistent").on("click", function () {
//        $('#formNav > .active').next('li').find('a').trigger('click');
        $('#siNuevoContrato').modal();
    });
    $("#register_employee_employeeHasEmployers_noExistent").on("click", function () {
        $('#noNuevoContrato').modal();
//        history.pushState("","","/manage/employees");
//        sendAjax("/manage/employees")
    });

    $("#register_employee_employeeHasEmployers_salaryD").on("focusout", function () {
        //validateSalary();
    });
    $("#register_employee_employeeHasEmployers_salary").on("focusout", function () {
        //validateSalary();
    });
    $("#register_employee_employeeHasEmployers_salaryD").on("input", function () {
        calculator();
        formatMoney($("#totalExpensesVal"));
        formatMoney($(this));
    });
    $("#register_employee_employeeHasEmployers_salary").on("input", function () {
        calculator();
        formatMoney($("#totalExpensesVal"));
        formatMoney($(this));    });

    calculator();
    formatMoney($("#totalExpensesVal"));
    formatMoney($("#register_employee_employeeHasEmployers_salaryD"));
    formatMoney($("#register_employee_employeeHasEmployers_salary"));

    $("#link_calculator").on("click", function (e) {
        e.preventDefault();
        $("#calculatorResultsModal").modal('toggle');
    });
    $('select').filter(function () {
        return this.id.match(/department/);
    }).change(function () {
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
    $('select').filter(function () {
        return this.id.match(/Department/);
    }).change(function () {
        var $department = $(this);
        // ... retrieve the corresponding form.
        var $form = $(this).closest('form');
        // Simulate form data, but only include the selected department value.
        var data = {};
        data[$department.attr('name')] = $department.val();
        var citySelectId = $department.attr("id").replace("Department", "City");
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
}
//Extract Constraints
var transportAid;
var smmlv;
var EPSEmployer;
var EPSEmployee;
var PensEmployer;
var PensEmployee;
var arl;
var caja;
var sena;
var icbf;
var vacations;
var taxCes;
var ces;
var dotation;
var transportAidDaily;
var vacations30D;
var dotationDaily;
function loadConstrains() {
    var constraints = null;
    $.ajax({
        url: "/api/public/v1/calculator/constraints",
        type: "GET",
        statusCode: {
            200: function (data) {
                //Extract Constraints
                constraints = data["response"];
                transportAid = parseFloat(constraints['auxilio transporte']);
                smmlv = parseFloat(constraints['smmlv']);
                EPSEmployer = parseFloat(constraints['eps empleador']);
                EPSEmployee = parseFloat(constraints['eps empleado']);
                PensEmployer = parseFloat(constraints['pension empleador']);
                PensEmployee = parseFloat(constraints['pension empleado']);
                arl = parseFloat(constraints['arl']);
                caja = parseFloat(constraints['caja']);
                sena = parseFloat(constraints['sena']);
                icbf = parseFloat(constraints['icbf']);
                vacations = parseFloat(constraints['vacaciones']);
                taxCes = parseFloat(constraints['intereses cesantias']);
                ces = parseFloat(constraints['cesantias']);
                dotation = parseFloat(constraints['dotacion']);
                transportAidDaily = transportAid / 30;
                vacations30D = vacations / 30;
                dotationDaily = dotation / 30;
                calculator();
            }
        }
    });

}

function calculator() {
    var type = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked");
    var salaryM = parseFloat(accounting.unformat($("#register_employee_employeeHasEmployers_salary").val()));
    var salaryD = parseFloat(accounting.unformat($("#register_employee_employeeHasEmployers_salaryD").val()));
    if (salaryD == "") {
        salaryD = 0;
    }if (salaryM == "") {
        salaryM = 0;
    }
    var numberOfDays = 30;
    var aid = 0;
    var aidD = 0;
    var sisben = $("input[name='register_employee[employeeHasEmployers][sisben]']:checked").val();
    var transport = $("input[name='register_employee[employeeHasEmployers][transportAid]']:checked").val();
    if (type.parent().text() == " Trabajo por días") {
        type = "days";
        numberOfDays=$("#register_employee_employeeHasEmployers_weekWorkableDays").val() * 4;
    } else {
        type = "complete";
    }
    var totalExpenses = 0;
    var totalIncome = 0;
    var EPSEmployerCal = 0;
    var EPSEmployeeCal = 0;
    var PensEmployeeCal = 0;
    var PensEmployerCal = 0;
    var transportCal = 0;
    var cesCal = 0;
    var taxCesCal = 0;
    var dotationCal = 0;
    var vacationsCal = 0;
    var arlCal = 0;
    var cajaCal = 0;
    var senaCal = 0;
    var icbfCal = 0;
    var base = 0;
    if (aid == 0) {
        aidD = 0;
    }
    if (type == "days") {
        transport = 1;
        if (transport == 1) {
            salaryD -= transportAidDaily;
        }
        //if it overpass the SMMLV calculates as a full time job  or
        //if does not belongs to SISBEN
        if (((salaryD + transportAidDaily + aidD) * numberOfDays) > smmlv || sisben == -1) {
            if (((salaryD + transportAidDaily + aidD) * numberOfDays) > smmlv) {
                base = (salaryD + aidD) * numberOfDays;
            } else {
                base = smmlv;
            }

            totalExpenses = ((salaryD + aidD + transportAidDaily + dotationDaily) * numberOfDays) + ((EPSEmployer +
                PensEmployer + arl + caja + sena + icbf) * base) + (vacations30D * numberOfDays * salaryD) +
                ((taxCes + ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            EPSEmployerCal = EPSEmployer * base;
            EPSEmployeeCal = EPSEmployee * base;
            PensEmployerCal = PensEmployer * base;
            PensEmployeeCal = PensEmployee * base;
            arlCal = arl * base;
            cesCal = ((ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            cajaCal = caja * base;
            vacationsCal = vacations30D * numberOfDays * salaryD;
            transportCal = transportAidDaily * numberOfDays;
            dotationCal = dotationDaily * numberOfDays;
            senaCal = sena * base;
            icbfCal = icbf * base;
            totalIncome = (salaryD * numberOfDays) - EPSEmployerCal - PensEmployerCal;
        } else {
            var EPSEmployee2 = 0;
            var EPSEmployer2 = 0;
            base = smmlv;
            //calculate the caja and pens in base of worked days
            if (numberOfDays <= 7) {
                PensEmployerCal = PensEmployer * base / 4;
                PensEmployeeCal = PensEmployee * base / 4;
                cajaCal = caja * base;
            } else if (numberOfDays <= 14) {
                PensEmployerCal = PensEmployer * base / 2;
                PensEmployeeCal = PensEmployee * base / 2;
                cajaCal = caja * base / 2;
            } else if (numberOfDays <= 21) {
                PensEmployerCal = PensEmployer * base * 3 / 4;
                PensEmployeeCal = PensEmployee * base * 3 / 4;
                cajaCal = caja * base * 3 / 4;
            } else {
                PensEmployerCal = PensEmployer * base;
                PensEmployeeCal = PensEmployee * base;
                cajaCal = caja * base;
            }
            //then calculate arl ces and the rest
            totalExpenses = ((salaryD + aidD + transportAidDaily + dotationDaily) * numberOfDays) + ((EPSEmployee2 + arl
                + sena + icbf) * base) + (vacations30D * numberOfDays * salaryD) + ((taxCes + ces) * (((salaryD + aidD)
                * numberOfDays * 30 / 28) + transportAid)) + PensEmployeeCal + cajaCal + PensEmployerCal;
            EPSEmployerCal = EPSEmployer2 * base;
            EPSEmployeeCal = EPSEmployer2 * base;
            arlCal = arl * base;
            cesCal = ((ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            vacationsCal = vacations30D * numberOfDays * salaryD;
            transportCal = transportAidDaily * numberOfDays;
            dotationCal = dotationDaily * numberOfDays;
            senaCal = sena * base;
            icbfCal = icbf * base;
            totalIncome = ((salaryD + transportAidDaily) * numberOfDays) - PensEmployeeCal;
        }

    } else {
        var transportAid2=0;
        if (transport == 1) {
            salaryM -= transportAid;
        } else if (salaryM + aidD > smmlv * 2) {
            transportAid2 = 0;
        }else{
            transportAid2=transportAid;
        }

        totalExpenses = salaryM + aidD + transportAid2 + dotation + ((EPSEmployer + PensEmployer + arl + caja +
            vacations30D + sena + icbf) * (salaryM + aidD)) + ((taxCes + ces) * (salaryM + aidD + transportAid2));
        EPSEmployerCal = EPSEmployer * (salaryM + aidD);
        EPSEmployeeCal = EPSEmployee * (salaryM + aidD);
        PensEmployerCal = PensEmployer * (salaryM + aidD);
        PensEmployeeCal = PensEmployee * (salaryM + aidD);
        arlCal = arl * (salaryM + aidD);
        cesCal = ces * (salaryM + aidD + transportAid2);
        taxCesCal = taxCes * (salaryM + aidD + transportAid2);
        cajaCal = caja * (salaryM + aidD);
        vacationsCal = vacations30D * (salaryM + aidD);
        transportCal = transportAid2;
        dotationCal = dotation;
        senaCal = sena * (salaryM + aidD);
        icbfCal = icbf * (salaryM + aidD);
        totalIncome = (salaryM + transportCal - EPSEmployerCal - PensEmployerCal);

    }
    var resposne = [];

    if ((type == "days"&&(salaryD == 0 || numberOfDays == null || numberOfDays == 0))||(type != "days"&&(salaryM<=0))) {
        totalExpenses = 0;
        resposne['totalExpenses'] = 0;
        resposne['dailyExpenses'] = 0;
        resposne['dailyIncome'] = 0;
        resposne['EPSEmployerCal'] = 0;
        resposne['EPSEmployeeCal'] = 0;
        resposne['PensEmployerCal'] = 0;
        resposne['PensEmployeeCal'] = 0;
        resposne['arlCal'] = 0;
        resposne['cesCal'] = 0;
        resposne['taxCesCal'] = 0;
        resposne['cajaCal'] = 0;
        resposne['vacationsCal'] = 0;
        resposne['transportCal'] = 0;
        resposne['dotationCal'] = 0;
        resposne['senaCal'] = 0;
        resposne['icbfCal'] = 0;
        resposne['totalIncome'] = 0;
    } else {
        resposne['totalExpenses'] = totalExpenses;
        resposne['dailyExpenses'] = totalExpenses / numberOfDays;
        resposne['dailyIncome'] = totalIncome / numberOfDays;
        resposne['EPSEmployerCal'] = EPSEmployerCal;
        resposne['EPSEmployeeCal'] = EPSEmployeeCal;
        resposne['PensEmployerCal'] = PensEmployerCal;
        resposne['PensEmployeeCal'] = PensEmployeeCal;
        resposne['arlCal'] = arlCal;
        resposne['cesCal'] = cesCal;
        resposne['taxCesCal'] = taxCesCal;
        resposne['cajaCal'] = cajaCal;
        resposne['vacationsCal'] = vacationsCal;
        resposne['transportCal'] = transportCal;
        resposne['dotationCal'] = dotationCal;
        resposne['senaCal'] = senaCal;
        resposne['icbfCal'] = icbfCal;
        resposne['totalIncome'] = totalIncome;
    }

    var htmlRes = jsonCalcToHTML(resposne);
    $("#calculatorResultsModal").find(".modal-body").html(htmlRes);

    $("#totalExpensesVal").val(totalExpenses.toFixed(0));

}
function checkDate(date) {
    var dateNow = new Date();
    if (date < dateNow) {
        $("#dateContract").modal("show");
        return false;
    }
    return true;
}
function getPrice(valor) {
    price = parseFloat(valor.toString().replace(/,/g, ""))
        .toFixed(0)
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return price;
}
function validateSalary() {
    var selectedVal = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text();
    if (selectedVal == " Trabajo por días") {
        salarioMinimoDiario = $("#salarioMinimoDiario").val();
        if (!salarioMinimoDiario) {
            salarioMinimoDiario = 21000;
        }
        salarioDias = (accounting.unformat($("#register_employee_employeeHasEmployers_salaryD").val()));
        if (salarioDias < salarioMinimoDiario) {
            $("#salarioMinimo").find('.modal-body').html('El salario minimo diario legal es de $ ' + getPrice(salarioMinimoDiario));
            $("#salarioMinimo").modal('show');
            $("#register_employee_employeeHasEmployers_salaryD").val((salarioMinimoDiario));
            calculator();
            formatMoney($("#totalExpensesVal"));
            formatMoney($("#register_employee_employeeHasEmployers_salaryD"));
            $("#ex6").bootstrapSlider("setValue", salarioMinimoDiario);
            return false;
        }
    } else {
        salarioMinimo = $("#salarioMinimo").val();
        if (!salarioMinimo) {
            salarioMinimo = 689454;
        }
        salarioMes = (accounting.unformat($("#register_employee_employeeHasEmployers_salary").val()));
        if (salarioMes < salarioMinimo) {
            $("#salarioMinimo").find('.modal-body').html('El salario minimo legal es de $ ' + getPrice(salarioMinimo));
            $("#salarioMinimo").modal('show');
            $("#register_employee_employeeHasEmployers_salary").val((salarioMinimo));
            formatMoney($("#register_employee_employeeHasEmployers_salary"));
            return false;
        }
    }
    return true;
}
function inquiry() {
    var form = $("form");
    var documentType = $(form).find("select[name='register_employee[person][documentType]']");
    var document = $(form).find("input[name='register_employee[person][document]']");
    var lastName1 = $(form).find("input[name='register_employee[person][lastName1]']");
    $.ajax({
        url: "/api/public/v1/inquiries/documents",
        type: 'POST',
        data: {
            documentType: documentType.val(),
            document: document.val(),
            lastName1: lastName1.val(),
        }
    }).done(function (data) {
        //alert("La cédula que nos proporcionó, ya existe en nuestro sistema, los dátos serán cargados automáticamente");
        //load the data
        var form = $("form");
        $(form).find("input[name='register_employee[person][names]']").val(data["names"]);
        $(form).find("input[name='register_employee[person][lastName2]']").val(data["lastName2"]);
        $(form).find("select[name='register_employee[personExtra][civilStatus]']").val(data["civilStatus"]);
        $(form).find("select[name='register_employee[person][birthDate][year]']").val(data["birthDate"]["year"]);
        $(form).find("select[name='register_employee[person][birthDate][month]']").val(data["birthDate"]["month"]);
        $(form).find("select[name='register_employee[person][birthDate][day]']").val(data["birthDate"]["day"]);
        $(form).find("select[name='register_employee[personExtra][documentExpeditionDate][year]']").val(data["documentExpeditionDate"]["year"]);
        $(form).find("select[name='register_employee[personExtra][documentExpeditionDate][month]']").val(data["documentExpeditionDate"]["month"]);
        $(form).find("select[name='register_employee[personExtra][documentExpeditionDate][day]']").val(data["documentExpeditionDate"]["day"]);
        $(form).find("select[name='register_employee[personExtra][birthCountry]']").val(data["birthCountry"]["id_country"]);
        $(form).find("select[name='register_employee[personExtra][birthDepartment]']").val(data["birthDepartment"]["id_department"]);
        $(form).find("select[name='register_employee[personExtra][birthCity]']").val(data["birthCity"]["id_city"]);
        $(form).find("select[name='register_employee[personExtra][gender]']").val(data["gender"]);
        $(form).find("input[name='register_employee[idEmployee]']").val(data["idEmployee"]);
        $(form).find("input[name='register_employee[personExtra][documentExpeditionPlace]']").val(data["documentExpeditionPlace"]);
        $(form).find("select[name='register_employee[person][department]']").val(data["department"]["id_department"]);
        $(form).find("select[name='register_employee[person][city]']").val(data["city"]["id_city"]);
        $(form).find("input[name='register_employee[personExtra][email]']").val(data["email"]);
        $(form).find("input[name='register_employee[person][mainAddress]']").val(data["mainAddress"]);
        $("#documentExistent").modal("show");
    }).fail(function (jqXHR, textStatus, errorThrown) {
        //show the other stuf
    });
}
function payMethodListener() {
    var $hasIt = $("#method_type_fields_hasIt");
    if (!$hasIt.parent().hasClass("formFieldsNo")) {
        $hasIt.parent().addClass("formFieldsNo");
        var selectedVal = $hasIt.val();
        if (selectedVal == 0) {
            $hasIt.parent().parent().find(".form-group").not(".formFieldsNo").each(function () {
                $(this).hide();
            });
        } else {
            $("#noDaviplata").modal("show");
            $hasIt.parent().parent().find(".form-group").each(function () {
                $(this).show();
            })
        }
        $hasIt.on("change", function () {
            var selectedVal = $(this).val();
            if (selectedVal == 1) {
                $hasIt.parent().parent().find(".form-group").each(function () {
                    $(this).show();
                })
            } else {

                $hasIt.parent().parent().find(".form-group").not(".formFieldsNo").each(function () {
                    $(this).hide();
                });
                $("#noDaviplata").modal("show");
            }
        });
    }
}

function infoNuevoContrato(from, to, template, event) {
    var btn = event;
    btn.button('loading');

    if (!to) {
        return false;
    }

    $.ajax({
        method: "POST",
        url: "/api/public/v1/sends/emails",
        data: {
            from: from,
            to: to,
            template: template
        }
    }).done(function (data) {
        btn.button('complete');
        $('#siNuevoContrato').modal('toggle');
        $('#formNav > .active').next('li').find('a').trigger('click');
    });
}
function initEntitiesFields(){
    var dataPen=[];
    $("#register_employee_entities_pension").find("> option").each(function() {
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
    $("#register_employee_entities_wealth").find("> option").each(function() {
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

}