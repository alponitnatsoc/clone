/**
 * Created by gabrielsamoma on 11/11/15.
 */

function startEmployee() {
    var validator;

    var loadedStartDateDay = $("#register_employee_employeeHasEmployers_startDate_day").val();
    var loadedStartDateMonth = $("#register_employee_employeeHasEmployers_startDate_month").val();
    var loadedStartDateYear = $("#register_employee_employeeHasEmployers_startDate_year").val();

    var loadedEndDateDay = $("#register_employee_employeeHasEmployers_endDate_day").val();
    var loadedEndDateMonth = $("#register_employee_employeeHasEmployers_endDate_month").val();
    var loadedEndDateYear = $("#register_employee_employeeHasEmployers_endDate_year").val();

    $.getScript("//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        $.ajax({
            url: "//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/additional-methods.min.js",
            dataType: "script",
            timeout: 4000
        }).done(function() {
            //var focus_element = undefined;
            validator = $("form[name='register_employee']").validate({
                errorPlacement: function(error, element) {
                    var placement = $(element).data('error');
                    //if(typeof focus_element == 'undefined') {
                    //focus_element = element;
                    //}
                    if(placement) {
                        $(placement).append(error);
                    } else {
                        error.insertAfter(element);
                    }
                    //focus_element.focus();
                },
                /*onfocusout: false,
                 invalidHandler: function(form, validator) {
                 var errors = validator.numberOfInvalids();
                 console.log('Errors');
                 console.log(errors);
                 if (errors) {
                 console.log(errors);
                 console.log(validator.errorList);
                 console.log(validator.errorList[0]);
                 validator.errorList[0].element.focus();
                 }
                 },*/
                rules: {
                    "register_employee[person][documentType]": "required",
                    "register_employee[person][document]": {required: true, number: true, maxlength:10},
                    "register_employee[person][names]": "required",
                    "register_employee[person][lastName1]": "required",
                    "register_employee[person][mainAddress]": "required",
                    //"register_employee[employeeHasEmployers][salary]": "required",
                    "register_employee[person][department]": "required",
                    "register_employee[person][city]": "required",
                    "register_employee[personExtra][civilStatus]": "required",
                    "register_employee[personExtra][gender]": "required",
                    "register_employee[personExtra][documentExpeditionDate][day]": "required",
                    "register_employee[personExtra][documentExpeditionDate][month]": "required",
                    "register_employee[personExtra][documentExpeditionDate][year]": "required",
                    "register_employee[person][birthDate][day]": "required",
                    "register_employee[person][birthDate][month]": "required",
                    "register_employee[person][birthDate][year]": "required",
                    "register_employee[personExtra][documentExpeditionPlace]": "required",
                    "register_employee[personExtra][birthCountry]": "required",
                    "register_employee[personExtra][birthDepartment]": "required",
                    "register_employee[personExtra][birthCity]": "required",
                    "register_employee[employeeHasEmployers][employeeType]": "required",
                    "register_employee[employeeHasEmployers][contractType]": "required",
                    "register_employee[employeeHasEmployers][timeCommitment]": "required",
                    "register_employee[employeeHasEmployers][worksSaturday]": "required",
                    //"register_employee[employeeHasEmployers][sisben]": "required",
                    "register_employee[employeeHasEmployers][position]": "required",
                    "register_employee[employeeHasEmployers][workplaces]": "required",
                    "register_employee[employeeHasEmployers][transportAid]": "required",
                    "register_employee[employeeHasEmployers][payMethod]": "required",
                    "register_employee[employeeHasEmployers][paysPens]": "required",
                    "register_employee[verificationCode]": "required",
                    "register_employee[employeeHasEmployers][frequencyFrequency]": "required",
                    "register_employee[employeeHasEmployers][holidayDebt]": "required",
                    "register_employee[entities][pension]":"required"
                    /*,
                     "register_employee[credit_card]": "required",
                     "register_employee[cvv]": "required",
                     "register_employee[expiry_date]": "required",
                     "register_employee[name_on_card]": "required"*/

                },
                messages: {
                    "register_employee[person][documentType]": "Por favor selecciona un tipo de documento",
                    "register_employee[person][document]": {
                        required: "Por favor ingresa un documento",
                        number: "ingresa solamente dígitos",
                        maxlength: "El documento no puede ser tan largo"
                    },
                    "register_employee[person][names]": "Por favor ingresa el nombre",
                    "register_employee[person][lastName1]": "Por favor ingresa el primer apellido",
                    "register_employee[person][mainAddress]": "Por favor ingresa una dirección",
                    //"register_employee[employeeHasEmployers][salary]": "Por favor ingresa un salario",
                    "register_employee[person][department]": "Por favor selecciona un departamento",
                    "register_employee[person][city]": "Por favor selecciona una ciudad",
                    "register_employee[personExtra][civilStatus]": "Por favor selecciona una opción",
                    "register_employee[personExtra][gender]": "Por favor selecciona una opción",
                    "register_employee[personExtra][documentExpeditionDate][day]": "Por favor selecciona una opción",
                    "register_employee[personExtra][documentExpeditionDate][month]": "Por favor selecciona una opción",
                    "register_employee[personExtra][documentExpeditionDate][year]": "Por favor selecciona una opción",
                    "register_employee[person][birthDate][day]": "Por favor selecciona una opción",
                    "register_employee[person][birthDate][month]": "Por favor selecciona una opción",
                    "register_employee[person][birthDate][year]": "Por favor selecciona una opción",
                    "register_employee[personExtra][documentExpeditionPlace]": "Por favor escribe algún lugar",
                    "register_employee[personExtra][birthCountry]": "Por favor selecciona una opción",
                    "register_employee[personExtra][birthDepartment]": "Por favor selecciona una opción",
                    "register_employee[personExtra][birthCity]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][employeeType]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][contractType]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][timeCommitment]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][worksSaturday]": "Por favor selecciona una opción",
                    //"register_employee[employeeHasEmployers][sisben]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][position]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][workplaces]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][transportAid]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][payMethod]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][paysPens]": "Por favor selecciona una opción",
                    "register_employee[verificationCode]": "Por favor ingrese el código",
                    "register_employee[employeeHasEmployers][frequencyFrequency]": "Por favor selecciona una opción",
                    "register_employee[employeeHasEmployers][holidayDebt]" : "Por favor ingrese un número de días o cambie de opción",
                    "register_employee[entities][pension]":"Debes seleccionar una entidad",
                    "register_employee[entities][severances]":"Debes seleccionar una entidad"
                    /*,
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
                        required: "Por favor escribe en el campo, hasta encontrar tu entidad o a la cual te gustaría ser afiliado"
                    }
                });
            });
        }).fail(function() {
            location.reload();
        });
    });
    $("#register_employee_person_documentType").find("option[value='PASAPORTE']").remove();
    $('.btnPrevious-form').click(function () {
        $('#formNav > .active').prev('li').find('a').trigger('click');
    });
    $('.btnPrevious-contract').click(function () {
        $('#contractNav > .active').prev('li').find('a').trigger('click');
    });
    $('.btnPrevious-sms').click(function () {
        $('#finalStepNav > .active').prev('li').find('a').trigger('click');
    });
    $('.btnNext-contract').click(function () {
        var dateOk = true;
        var mustCheck = false;

        if($("#changeBehavior").text() !="1"){
            if( loadedStartDateDay != $("#register_employee_employeeHasEmployers_startDate_day").val()
                || loadedStartDateMonth != $("#register_employee_employeeHasEmployers_startDate_month").val()
                || loadedStartDateYear != $("#register_employee_employeeHasEmployers_startDate_year").val()){
                mustCheck = true;
                if (!checkDate(new Date(
                        $("#register_employee_employeeHasEmployers_startDate_year").val(),
                        parseInt($("#register_employee_employeeHasEmployers_startDate_month").val()),
                        $("#register_employee_employeeHasEmployers_startDate_day").val()
                    ))) {
                    var $permittedDate= $("#datePermitted");
                    $("#register_employee_employeeHasEmployers_startDate_year").val(parseInt($permittedDate.find(".year").text()));
                    $("#register_employee_employeeHasEmployers_startDate_month").val(parseInt($permittedDate.find(".month").text()));
                    $("#register_employee_employeeHasEmployers_startDate_day").val(parseInt($permittedDate.find(".day").text()));
                    dateOk = false;
                }
            }
        }

        if( $("#fijo").find("input[type=radio]").prop("checked") == true  && dateOk == true){

            if( loadedEndDateDay != $("#register_employee_employeeHasEmployers_endDate_day").val()
                || loadedEndDateMonth != $("#register_employee_employeeHasEmployers_endDate_month").val()
                || loadedEndDateYear != $("#register_employee_employeeHasEmployers_endDate_year").val()
                || mustCheck == true){

                if (!checkDateVsStart(new Date(
                        $("#register_employee_employeeHasEmployers_endDate_year").val(),
                        parseInt($("#register_employee_employeeHasEmployers_endDate_month").val()),
                        $("#register_employee_employeeHasEmployers_endDate_day").val()
                    ))) {

                    var setEndDate = oneYearFromNow(new Date(
                        $("#register_employee_employeeHasEmployers_startDate_year").val(),
                        parseInt($("#register_employee_employeeHasEmployers_startDate_month").val()),
                        $("#register_employee_employeeHasEmployers_startDate_day").val()));

                    $("#register_employee_employeeHasEmployers_endDate_year").val($("#register_employee_employeeHasEmployers_startDate_year").val());
                    $("#register_employee_employeeHasEmployers_endDate_month").val(parseInt($("#register_employee_employeeHasEmployers_startDate_month").val()));
                    $("#register_employee_employeeHasEmployers_endDate_day").val($("#register_employee_employeeHasEmployers_startDate_day").val());
                    dateOk = false;
                }
            }
        }

        var valid = true;
        var shouldBeEmpty = false;
        if ( $("#changeBehavior").text() == "1" ){
            $("#alDiaDias").find("input[type=radio]").each(function () {
                if( $(this).prop("checked") == true){
                    shouldBeEmpty = true;
                    $("#register_employee_employeeHasEmployers_holidayDebt").val(0);
                }
            });

            if(!shouldBeEmpty){
                if (!validator.element($("#register_employee_employeeHasEmployers_holidayDebt"))) {
                    valid = false;
                }
            }
        }

        if(dateOk == true && valid == true){
            $('#contractNav > .active').next('li').find('a').trigger('click');
        }
    });

    $('.btnNext-infoPago').click(function () {

        var valid = true;
        if (!validator.element($("#register_employee_employeeHasEmployers_position"))) {
            valid = false;
        }

        if (!validator.element($("[name='register_employee[employeeHasEmployers][timeCommitment]']"))) {
            valid = false;
        }
        if (!validator.element($("[name='register_employee[employeeHasEmployers][worksSaturday]']"))) {
            valid = false;
        }

        /*if($("#register_employee_employeeHasEmployers_timeCommitment_2").prop("checked") == true){
         if (!validator.element($("[name='register_employee[employeeHasEmployers][sisben]']"))) {
         valid = false;
         }
         }*/

        if (!validator.element($("#register_employee_employeeHasEmployers_workplaces"))) {
            valid = false;
        }

        if(valid == true){
            $('#contractNav > .active').next('li').find('a').trigger('click');
        }
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
    if($("#register_employee_employeeHasEmployers_timeCommitment_2").is("[checked]")){
        $('#radio_diario').prop('checked', true);
        $('#radio_mensual').prop('checked', false);
    }
    else{
        $('#radio_diario').prop('checked', false);
        $('#radio_mensual').prop('checked', true);
    }
    timeCommitment.change(function () {
        var selectedVal = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text();
        if (selectedVal == " Trabajador por días") {
            $('#radio_diario').prop('checked', true);
            $('#radio_mensual').prop('checked', false);
            $(".days").each(function () {
                $(this).show();
            });
            $(".complete").each(function () {
                $(this).hide();
            });
            //checkSisben();
        } else {
            $('#radio_diario').prop('checked', false);
            $('#radio_mensual').prop('checked', true);
            $(".days").each(function () {
                $(this).hide();
            });
            $(".complete").each(function () {
                $(this).show();
            });
        }
        calculator();
    });

    /*var sisbenBut = $("input[name='register_employee[employeeHasEmployers][sisben]']");
     sisbenBut.change(function () {
     var selectedVal = $("input[name='register_employee[employeeHasEmployers][sisben]']:checked").parent().text();

     if(selectedVal == " No"){
     showModal(21);
     }
     calculator();
     });*/

    $("#register_employee_employeeHasEmployers_position").on("change", function () {
        calculator();
    });
    /*$("#register_employee_employeeHasEmployers_startDate").on("change", function () {
     if($("#changeBehavior").text()=="1"){
     return;
     }
     if (!checkDate(new Date(
     $(this).find("#register_employee_employeeHasEmployers_startDate_year").val(),
     parseInt($(this).find("#register_employee_employeeHasEmployers_startDate_month").val()) - 1,
     $(this).find("#register_employee_employeeHasEmployers_startDate_day").val()
     ))) {
     var $permittedDate= $("#datePermitted");
     $(this).find("#register_employee_employeeHasEmployers_startDate_year").val(parseInt($permittedDate.find(".year").text()));
     $(this).find("#register_employee_employeeHasEmployers_startDate_month").val(parseInt($permittedDate.find(".month").text()));
     $(this).find("#register_employee_employeeHasEmployers_startDate_day").val(parseInt($permittedDate.find(".day").text()));
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
     });*/
    var selectedVal = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text();
    if (selectedVal == " Trabajador por días") {
        $(".days").each(function () {
            $(this).show();
        });
        $(".complete").each(function () {
            $(this).hide();
        });
        // checkSisben();
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
    $('#fijo').click(function () {
        $("#register_employee_employeeHasEmployers_contractType").val("2")
        $(".definite").each(function () {
            $(this).show();
        });
        $('#contractIndefinido').hide();
        $('#contractFijo').show();
        $(this).addClass('active');
        $('#indef').removeClass('active');
    });

    $('#indef').click(function () {
        $("#register_employee_employeeHasEmployers_contractType").val("1")
        $(".definite").each(function () {
            $(this).hide();
        });
        $('#contractIndefinido').show();
        $('#contractFijo').hide();
        $(this).addClass('active');
        $('#fijo').removeClass('active');
    });
    var contractType = $("#register_employee_employeeHasEmployers_contractType");
    selectedVal = $(contractType).find("option:selected").text();
    if (selectedVal == "Término fijo") {
        $("#fijo").trigger("click");
        $(".definite").each(function () {
            $(this).show();
        });
    } else {
        $("#indef").trigger("click");
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
        //aqui miramos que metodo de pago escojió
        var campaignvar = $("#start150Campaign");
        var campaignEndVar = $("#end150Campaign");
        var dateStart = new Date(
            parseInt(campaignvar.find(".year").text()),
            parseInt(campaignvar.find(".month").text()-1),
            parseInt(campaignvar.find(".day").text()));
        var dateEnd = new Date(
            parseInt(campaignEndVar.find(".year").text()),
            parseInt(campaignEndVar.find(".month").text()-1),
            parseInt(campaignEndVar.find(".day").text()));
        var nowDate=new Date();

        var payMethod = $("input[name='register_employee[employeeHasEmployers][payMethod]']:checked");
        if(dateStart<=nowDate&&dateEnd>=nowDate){
            if(payMethod.parent().parent().parent().find(".paymentMethodImage").attr("src")=="/img/icon_cash.png"){
                $("#150kCampaign").modal("show");
            }
        }
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

    $("[name='register_employee']").on("submit", function (e) {
        e.preventDefault();
        var form = $("[name='register_employee']");
        var idsBenef = [], idsWorkpl = [];
        var i = 0;
        $(form).find("ul.benefits select[name*='benefits']").each(function () {
            idsBenef[i++] = $(this).val();
        });
        i = 0;
        $(form).find("ul.workplaces select[name*='workplaces']").each(function () {
            idsWorkpl[i++] = $(this).val();
        });

        $(form).find("input[name='method_type_fields[cellphone]']").each(function () {
            $(this).rules("add", {
                maxlength: 10,
                required: true,
                number: true,
                pattern: /3[\d]{9}/,
                messages: {
                    required: "Por favor ingresa un número de teléfono de celular en el siguiente formato, ejemplo 3508330000",
                    number: "Por favor ingresa solo digitos",
                    pattern: "El número no tiene la estructura de un celular colombiano",
                    maxlength: "No es un número de celular válido; ejemplo 3508330000"
                }
            });
        });

        $(form).find("select[name='method_type_fields[hasIt]']").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "Necesitamos saber si tiene cuenta Daviplata"
                }
            });
        });

        $(form).find("input[name='method_type_fields[accountNumber]']").each(function () {
            $(this).rules("add", {
                required: true,
                number: true,
                messages: {
                    required: "Por favor ingresa el número de cuenta",
                    number: "Por favor ingresa solo digitos"
                }
            });
        });

        $(form).find("select[name='method_type_fields[accountTypeAccountType]']").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "Escoge el tipo de cuenta"
                }
            });
        });

        $(form).find("select[name='method_type_fields[bankBank]']").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "Selecciona el banco"
                }
            });
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

    $('#btn-verificaion').click(function (e) {
        if (!validator.element($("#register_employee_verificationCode"))) {
            return false;
        }

        e.preventDefault();
        var form = $("[name='register_employee']");
        var url= $(this).attr('href');
        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                verificationCode: $("#register_employee_verificationCode").val(),
                contractId: $("input[name='register_employee[idContract]']").val()
            }
        }).done(function (data) {
            history.pushState("", "", data["url"]);
            sendAjax(data["url"]);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert("El código de verificación es incorrecto, un nuevo código ha sido enviado");
        });

    });
    $('#btn-reenviar').click(function (e) {
        // We send a fake code, so that it generates a new one.
        e.preventDefault();
        var form = $("[name='register_employee']");
        var url= $(this).attr('href');
        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                verificationCode: '01',
                contractId: $("input[name='register_employee[idContract]']").val()
            }
        }).done(function (data) {
            history.pushState("", "", data["url"]);
            sendAjax(data["url"]);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            showModal(14);
        });
    });
    $('#btn-entities').click(function (e) {
        e.preventDefault();
        var form = $("[name='register_employee']");
        var i = 0;
        var flagValid = true;
        var selectedVal = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text();
        //var sisben = $("input[name='register_employee[employeeHasEmployers][sisben]']:checked").parent().text();

        if (selectedVal == " Trabajador por días" /*&&sisben==" Si"*/) {
            /*  $(form).find("select[name*='[ars]']").each(function () {
             if (!validator.element($(this))) {
             flagValid = false;
             return;
             }
             });

             $(form).find("input[name*='[arsAC]']").each(function () {
             if (!validator.element($(this))) {
             flagValid = false;
             return;
             }
             });*/
        }else{
            $(form).find("select[name*='[wealth]']").each(function () {
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

            if (!validator.element($("[name='register_employee[entities][wealthExists]']"))) {
                flagValid = false;
                return;
            }

        }

        $(form).find("select[name*='[pension]']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });

        $(form).find("select[name*='[severances]']").each(function () {
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

        if (!validator.element($("[name='register_employee[entities][pensionExists]']"))) {
            flagValid = false;
            return;
        }

        if (!validator.element($("[name='register_employee[entities][severancesExists]']"))) {
            flagValid = false;
            return;
        }

        if (!flagValid) {
            return;
        }
        if (selectedVal == " Trabajador por días" /*&&sisben==" Si"*/) {
            $("#register_employee_entities_wealth").val("");
        }/*else{
         $("#register_employee_entities_ars").val("");
         }*/

        var severancesExists;
        $("#register_employee_entities_severancesExists").find("input[type=radio]").each(function () {
            if($(this).is(":checked")){
                severancesExists = $(this).val();
            }
        });

        var wealthExists;
        $("#register_employee_entities_wealthExists").find("input[type=radio]").each(function () {
            if($(this).is(":checked")){
                wealthExists = $(this).val();
            }
        });

        var pensionExists;
        $("#register_employee_entities_pensionExists").find("input[type=radio]").each(function () {
            if($(this).is(":checked")){
                pensionExists = $(this).val();
            }
        });

        if( $("#register_employee_entities_wealthAC").is(":visible") == true){
            var wealthL = $(form).find("#register_employee_entities_wealth");
            var wealthACL = $(form).find("#register_employee_entities_wealthAC");
            $(wealthL).val($("#register_employee_entities_wealth option").filter(function () { return $.trim($(this).html()) == $.trim($(wealthACL).val()); }).val());
            if($(wealthL).val() == undefined || $(wealthL).val() == ""){
                $("#errorWealth").show();
                return;
            }
            else {
                $("#errorWealth").hide();
            }
        }




        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                idContract: $("input[name='register_employee[idContract]']").val(),
                //beneficiaries: $("input[name='register_employee[entities][beneficiaries]']:checked").val(),
                pension: $("#register_employee_entities_pension").val(),
                pensionExists: pensionExists,
                wealth:  $("#register_employee_entities_wealth").val(),
                wealthExists: wealthExists,
                severances:  $("#register_employee_entities_severances").val(),
                severancesExists:  severancesExists,
                idEmployee: $("#register_employee_idEmployee").val()
            }
        }).done(function (data) {
            if(typeof data['url'] == 'undefined'){
                $('#finalStepNav > .active').next('li').find('a').trigger('click');
            }else{
                history.pushState("", "", data["url"]);
                sendAjax(data["url"]);
            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if(jqXHR==errorHandleTry(jqXHR)){
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    $('#btn-1').click(function (e) {
        e.preventDefault();
        var form = $("[name='register_employee']");
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
        var form = $("[name='register_employee']");
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
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if (jqXHR == errorHandleTry(jqXHR)) {
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    $('#btn-3').click(function (e) {
        e.preventDefault();
        var form = $("[name='register_employee']");

        //var employeeType = $(form).find("select[name='register_employee[employeeHasEmployers][employeeType]']");
        var contractType = $(form).find("select[name='register_employee[employeeHasEmployers][contractType]']");
        var timeCommitment = $(form).find("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked");
        var worksSat = $(form).find("input[name='register_employee[employeeHasEmployers][worksSaturday]']:checked");
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
        if(!$("input[name='register_employee[employeeHasEmployers][transportAid]']:checked").val()){
            showModal(13);
            return false;
        }
        if (!validateSalary()) {
            return false;
        }

        var holidayDebtTrueValue = 0;
        var numericValueHolidayDebt = Math.abs($(form).find("#register_employee_employeeHasEmployers_holidayDebt").val());
        $("#meDebeDias").find("input[type=radio]").each(function () {
            if( $(this).prop("checked") == true){
                holidayDebtTrueValue = numericValueHolidayDebt * -1;
            }
        });
        $("#alDiaDias").find("input[type=radio]").each(function () {
            if( $(this).prop("checked") == true){
                holidayDebtTrueValue = 0;
            }
        });
        $("#leDeboDias").find("input[type=radio]").each(function () {
            if( $(this).prop("checked") == true){
                holidayDebtTrueValue = numericValueHolidayDebt;
            }
        });

        var sisbenS = null;
        if( timeCommitment.val() == "2" ){
            sisbenS = "1";
        }
        else{
            sisbenS = "-1";
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
                worksSat: worksSat.val(),
                position: position.val(),
                salary: accounting.unformat(salary.val()),
                salaryD: accounting.unformat($("#totalExpensesValD").val()),
                idsBenefits: idsBenef,
                benefType: benefType,
                amountBenefits: amountBenef,
                periodicityBenefits: periodicityBenef,
                idWorkplace: idWorkplace.val(),
                transportAid: $(form).find("input[name='register_employee[employeeHasEmployers][transportAid]']:checked").val(),
                sisben: sisbenS,
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
                contractId: $(form).find("input[name='register_employee[idContract]']").val(),
                holidayDebt: holidayDebtTrueValue
            }
        }).done(function (data) {
            $('#contractNav > .active').next('li').find('a').trigger('click');
            $(form).find("input[name='register_employee[idContract]']").val(data['response']['idContract']);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if (jqXHR == errorHandleTry(jqXHR)) {
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });

    if($("#register_employee_employeeHasEmployers_holidayDebt").val() == 0){
        $("#alDiaDias").find("input[type=radio]").each(function () {
            $(this).prop("checked", true);
        });
        $("#register_employee_employeeHasEmployers_holidayDebt").hide();
    }else if ($("#register_employee_employeeHasEmployers_holidayDebt").val() > 0) {
        $("#leDeboDias").find("input[type=radio]").each(function () {
            $(this).prop("checked", true);
        });
    }else {
        $("#meDebeDias").find("input[type=radio]").each(function () {
            $(this).prop("checked", true);
        });
    }

    $("#register_employee_employeeHasEmployers_holidayDebt").val(Math.abs($("#register_employee_employeeHasEmployers_holidayDebt").val()));
    $("#totalExpensesVal").attr("disabled", true);
    loadConstrains();

    if($("#register_employee_employeeHasEmployers_timeCommitment_2").is("[checked]")){
        $(document).ajaxStop(function () {
            if(leavingPage == false){
                reverseCalculator();
                leavingPage = true;
            }
        });
    }

    if( $("#register_employee_employeeHasEmployers_transportAid_0").prop("checked") == false
        && $("#register_employee_employeeHasEmployers_transportAid_1").prop("checked") == false ){
        $("#register_employee_employeeHasEmployers_transportAid_1").prop("checked",true);
    }

    $( "label[for='register_employee_person_phones_0_phoneNumber']").text("Número de teléfono");

    if( $("#register_employee_entities_pension").val() == 50 ){
        $("#register_employee_employeeHasEmployers_paysPens_1").prop('checked', true);
        $("#pensionHide").hide();
        //$("#register_employee_entities_pension option[value*=50]").prop('disabled',false);
        $("#register_employee_entities_pension option[value*=50]").show();
    }
    else {
        $("#register_employee_employeeHasEmployers_paysPens_0").prop('checked', true);
        $("#pensionHide").show();
        //$("#register_employee_entities_pension option[value*=50]").prop('disabled',true);
        $("#register_employee_entities_pension option[value*=50]").hide();
    }
    calculator();

    $("#register_employee_employeeHasEmployers_paysPens").on("change",function(){
        if($(this).find("input:checked").val()=="1"){
            $("#pensionHide").show();
            $("#register_employee_entities_pension").val("");
            //$("#register_employee_entities_pension option[value*=50]").prop('disabled',true);
            $("#register_employee_entities_pension option[value*=50]").hide();
        }else{
            $("#pensionHide").hide();
            //$("#register_employee_entities_pension option[value*=50]").prop('disabled',false);
            $("#register_employee_entities_pension option[value*=50]").show();
            $("#register_employee_entities_pension").val(50);

        }
        calculator();
    });

    $("#errorWealth").hide();

    var selectedVal = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text();
    if (selectedVal == " Trabajador por días") {
        $("#wealthBlock").hide();
    }
    else {
        $("#wealthBlock").show();
    }
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

$('#radio_diario').click(function() {
    //$("#labelCosto").html("Costo diario </br> del empleado (no incluye prestaciones)");
    //$("#ingresoNeto").html("Esto recibirá neto el empleado diariamente");
    radioChange = true;
    calculator();
});

$('#radio_mensual').click(function() {
    //$("#labelCosto").html("Costo </br> del empleado (no incluye prestaciones)");
    //$("#ingresoNeto").html("Esto recibirá neto el empleado mensualmente");
    radioChange = true;
    calculator();
});

var sueldo_plano = 0;
var radioChange = false;

function changeValues(data) {
    // We call calculator to have everything fresh.
    var division = 1;
    var elementExists = document.getElementById("radio_diario");
    if(elementExists != null)
        if(document.getElementById('radio_diario').checked) {
            division = data.numberOfDays;
        }else {
            division = 1;
        }
    // Plain salary is what the employee should recieve.
    var salario_bruto = Math.round((data.plainSalary - data.transportCal)/0.92);
    var total_modal = data.plainSalary + data.transportCal + data.EPSEmployerCal + data.PensEmployerCal + data.cajaCal + data.arlCal;
    var pagos_netos = (Math.round(data.plainSalary) + Math.round(data.transportCal)) - (Math.round(data.EPSEmployeeCal) + Math.round(data.PensEmployeeCal));
    var total_prestaciones = Math.round(data.cesCal + data.taxCesCal + data.vacationsCal + data.primaCal);

    document.getElementById('salario_ingreso_bruto').innerHTML = getPrice(Math.round(data.plainSalary)/division);
    document.getElementById('subsidio_transporte').innerHTML = getPrice(Math.round(data.transportCal)/division);
    document.getElementById('descuento_salud').innerHTML = getPrice(Math.round(data.EPSEmployeeCal)/division);
    document.getElementById('descuento_pension').innerHTML = getPrice(Math.round(data.PensEmployeeCal)/division);
    document.getElementById('pagos_netos').innerHTML = getPrice(Math.round(pagos_netos/division));

    document.getElementById('salario_ingreso_bruto2').innerHTML = getPrice(Math.round(data.plainSalary)/division);
    document.getElementById('subsidio_transporte2').innerHTML = getPrice(Math.round(data.transportCal)/division);
    document.getElementById('salud_empleador').innerHTML = getPrice(Math.round(data.EPSEmployerCal)/division);
    document.getElementById('pension_empleador').innerHTML = getPrice(Math.round(data.PensEmployerCal)/division);
    document.getElementById('ccf_empleador').innerHTML = getPrice(Math.round(data.cajaCal)/division);
    document.getElementById('arl_empleador').innerHTML = getPrice(Math.round(data.arlCal)/division);
    document.getElementById('costo_total').innerHTML = getPrice(Math.round(total_modal)/division);

    document.getElementById('cesantias').innerHTML = getPrice(Math.round(data.cesCal)/division);
    document.getElementById('int_cesantias').innerHTML = getPrice(Math.round(data.taxCesCal)/division);
    document.getElementById('vacaciones').innerHTML = getPrice(Math.round(data.vacationsCal)/division);
    document.getElementById('prima').innerHTML = getPrice(Math.round(data.primaCal)/division);
    document.getElementById('total_prestaciones').innerHTML = getPrice(total_prestaciones/division);

    sueldo_plano = Math.round(data.plainSalary/data.numberOfDays);

    if( radioChange == false ){
        $("#totalExpensesVal").val(getPrice(Math.round(pagos_netos/division)));
        $("#totalExpensesVal2").val(getPrice(Math.round(total_modal)/division));
        $("#totalExpensesValD").val(getPrice(Math.round(data.plainSalary)/division));
    } else {
        radioChange = false;
    }


    if($("#totalExpensesVal2").val() == 'NaN')
        $("#totalExpensesVal2").val(getPrice(0));
    if($("#totalExpensesValD").val() == 'NaN')
        $("#totalExpensesValD").val(getPrice(0));
    if($("#totalExpensesVal").val() == 'NaN')
        $("#totalExpensesVal").val(getPrice(0));


}

function jsonCalcToHTML(data) {
    var htmls = "";

    htmls += "<div class='row'>";
    htmls += "    <div class='col-sm-6 text-center'>";
    htmls += "        <strong>Costo total para el empleador</strong>:<br />";
    htmls += "        $<strong>" + getPrice(Math.floor(data.totalExpenses)) + "</strong> ($" + getPrice(Math.floor(data.dailyExpenses)) + " diarios )";
    htmls += "        <br/><small class='text-muted'>El valor estimado que tu vas a pagar</small>";
    htmls += "    </div>";

    htmls += "    <div class='col-sm-6 text-center'>";
    htmls += "        <strong>Ingreso neto para el empleado</strong>:<br />";
    htmls += "        $<strong>" + getPrice(Math.floor(data.totalIncome)) + "</strong> ($" + getPrice(Math.floor(data.dailyIncome)) + " diarios )";
    htmls += "        <br/><small class='text-muted'>El valor estimado que recibirá tu empleado</small><br />";
    htmls += "    </div>";
    htmls += "</div>";


    htmls += "<hr /><table class='table table-striped'> ";
    htmls += "    <thead> ";
    htmls += "        <tr> ";
    htmls += "            <th>Concepto</th> ";
    htmls += "            <th>Detalle</th> ";
    htmls += "        </tr> ";
    htmls += "    </thead> ";
    htmls += "    <tbody> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Empleadddddddor EPS:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.EPSEmployerCal)) + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Empleador Pensión:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.PensEmployerCal)) + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Empleado ARL:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.arlCal)) + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Empleado Cesantias:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.cesCal)) + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Empleado Intereses/cesantias:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.taxCesCal)) + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Empleado Caja Compensación:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.cajaCal)) + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Empleado Vacaciones:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.vacationsCal))  + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Auxilio de Transporte:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.transportCal))  + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Dotacion/150000 pesos trimestre:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.dotationCal))  + "</td> ";
    htmls += "        </tr> ";


    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos SENA:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.senaCal))  + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos ICBF: </th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.icbfCal))   + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Empleado EPS:</th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.EPSEmployeeCal))  + "</td> ";
    htmls += "        </tr> ";

    htmls += "        <tr> ";
    htmls += "            <th scope='row'>Gastos Empleado Pensión: </th> ";
    htmls += "            <td>$" + getPrice(Math.floor(data.PensEmployeeCal))   + "</td> ";
    htmls += "        </tr> ";


    htmls += "    </tbody> ";
    htmls += "</table>    ";

    /*
     htmls += "<ul class='lista_listo clearfix'><li class='col-sm-6'><span class='titulo'><strong>Costo total</strong><br/>para el empleador</span> <span class='cifra'>" + getPrice(Math.floor(data.totalExpenses)) + "</span></li>";
     htmls += "<li class='col-sm-6'><span class='titulo'><strong>Ingreso neto</strong><br />para el empleado</span> <span class='cifra'>" + getPrice(Math.floor(data.totalIncome)) + "</span></li>";
     //htmls += "<li class='col-sm-6'><span class='cifra'>" + getPrice(Math.floor(data.dailyExpenses)) + "</span></li>";
     //htmls += "<li class='col-sm-6'><span class='cifra'>" + getPrice(Math.floor(data.dailyIncome)) + "</span></li>";
     htmls += "</ul>";
     htmls += "<h2 class='modal-title'>Detalles:</h2>" +
     "<ul class='lista_listo_detalle'>";
     htmls += "<li>11Gastos Empleador EPS: " + getPrice(Math.floor(data.EPSEmployerCal)) + "</li>";
     htmls += "<li>11Gastos Empleador Pensión: " + getPrice(Math.floor(data.PensEmployerCal)) + "</li>";
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
     */
    return htmls;
}

function addListeners() {
    $("#sisbenTooltip").on('click', function(){
        $(this).tooltip('show');
    });
    $("#pensionTooltip").on('click', function(){
        $(this).tooltip('show');
    });
    $("#costoTooltip").on('click', function(){
        $(this).tooltip('show');
    });
    $("#ex6").bootstrapSlider();
    $("#ex6").on("slide", function (slideEvt) {
        $("#register_employee_employeeHasEmployers_salaryD").val(slideEvt.value);
        calculator("d");
        formatMoney($("#totalExpensesValD"));
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
    $("#register_employee_employeeHasEmployers_sisben").on("change",function(){
        if($(this).find("input:checked").val()=="0"){
            $("#sisbenUnknown").show();
        }else{
            $("#sisbenUnknown").hide();
        }
    });
    $("#arsNotAplicable").hide();
    $("#sisbenUnknown").hide();
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
        formatMoney($("#totalExpensesValD"));
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
        formatMoney($("#totalExpensesValD"));
        formatMoney($(this));
    });
    $("#register_employee_employeeHasEmployers_salary").on("input", function () {
        calculator();
        formatMoney($("#totalExpensesVal"));
        formatMoney($(this));    });

    calculator();
    formatMoney($("#totalExpensesVal"));
    formatMoney($("#totalExpensesValD"));
    formatMoney($("#register_employee_employeeHasEmployers_salaryD"));
    formatMoney($("#register_employee_employeeHasEmployers_salary"));

    $("#link_calculator").on("click", function (e) {
        e.preventDefault();
        $("#calculatorResultsModal").modal('toggle');
    });
    $("#link_calculator2").on("click", function (e) {
        e.preventDefault();
        $("#calculatorResultsModal").modal('toggle');
    });
    $('select').filter(function () {
        return this.id.match(/department/);
    }).change(function () {
        var $department = $(this);
        $department.parent().parent().next().find("select[name*='ity']").html("<option value =''>Cargando Ciudades...</option>");

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
        $department.parent().parent().next().find("select[name*='ity']").html("<option value =''>Cargando Ciudades...</option>");
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

    $('#register_employee_personExtra_birthCountry').on('change', function () {
        var $country = $(this);
        $("#register_employee_personExtra_birthDepartment").html("<option value =''>Cargando Departamentos...</option>");

        $.ajax({
            method: "POST",
            url: "/api/public/v1/departments",
            data: {country: $country.val()}
        }).done(function (data) {
            $("#register_employee_personExtra_birthDepartment").html(
                // ... with the returned one from the AJAX response.
                jsonToHTML(data)
            );
        });
    });

    $("#meDebeDias").click(function(){
        $("#register_employee_employeeHasEmployers_holidayDebt").show();
        $("#register_employee_employeeHasEmployers_holidayDebt").removeClass("error");
        $("#register_employee_employeeHasEmployers_holidayDebt").addClass("valid");
        $(this).find("input[type=radio]").each(function () {
            $(this).prop("checked", true);
        });
    });
    $("#alDiaDias").click(function(){
        //$("#register_employee_employeeHasEmployers_holidayDebt").val(0);
        $("#register_employee_employeeHasEmployers_holidayDebt-error").hide();
        $("#register_employee_employeeHasEmployers_holidayDebt").hide();
        $(this).find("input[type=radio]").each(function () {
            $(this).prop("checked", true);
        });
    });
    $("#leDeboDias").click(function(){
        $("#register_employee_employeeHasEmployers_holidayDebt").show();
        $("#register_employee_employeeHasEmployers_holidayDebt").removeClass("error");
        $("#register_employee_employeeHasEmployers_holidayDebt").addClass("valid");
        $(this).find("input[type=radio]").each(function () {
            $(this).prop("checked", true);
        });
    });

    $("#fijo").click(function(){
        $("#tipocont1").hide("slow");
        $("#tipocont1").hide(3000);
        $("#tipocont2").show("slow");
        $("#tipocont2").show(3000);
        $("#fijo").find("input[type=radio]").each(function () {
            $(this).prop("checked", true);
        });
    });
    $("#indef").click(function(){
        $("#tipocont2").hide("slow");
        $("#tipocont2").hide(3000);
        $("#tipocont1").show("slow");
        $("#tipocont1").show(3000);
        $("#indef").find("input[type=radio]").each(function () {
            $(this).prop("checked", true);
        });
    });

    $("#cerrarModalDetalle").click(function(){
        var selectedVal = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text();
        if (selectedVal == " Trabajador por días") {
            $('#radio_diario').prop('checked', true);
            $('#radio_mensual').prop('checked', false);
        }
        else {
            $('#radio_diario').prop('checked', false);
            $('#radio_mensual').prop('checked', true);
        }
        calculator();
    });

    $("#employerDismiss").click(function(){
        var selectedVal = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text();
        if (selectedVal == " Trabajador por días") {
            $('#radio_diario').prop('checked', true);
            $('#radio_mensual').prop('checked', false);
        }
        else {
            $('#radio_diario').prop('checked', false);
            $('#radio_mensual').prop('checked', false);
        }
        calculator();
    });
}
//Extract Constraints
var leavingPage =  false;
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
var firstLoad = true;
var lockCalc = false;
var prima;
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
                prima =  parseFloat(constraints['prima']);
                calculator();
            }
        }
    });

}

function calculator() {

    $("#arsNotAplicable").hide();
    $("#dontHaveSisben").hide();

    var type = $("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked");
    var salaryM = parseFloat(accounting.unformat($("#register_employee_employeeHasEmployers_salary").val()));
    var salaryD = parseFloat(accounting.unformat($("#register_employee_employeeHasEmployers_salaryD").val()));
    if (salaryD == "") {
        salaryD = 0;
    }if (salaryM == "") {
        salaryM = 0;
    }
    var numberOfDays = 30;
    var arlChoose = $("#register_employee_employeeHasEmployers_position").val();

    var arlProf = 0;
    if( arlChoose == 1 ){ //empleada
        arlProf = 0.00522;
    }
    else if (arlChoose == 2) { //conductor
        arlProf = 0.02436;
    }
    else if (arlChoose == 3) { //ninero
        arlProf = 0.00522;
    }
    else if (arlChoose == 4) { //enfermero
        arlProf = 0.01044;
    }
    else if (arlChoose == 5) { //mayordomo
        arlProf = 0.01044;
    }

    var aportaPens = $("#register_employee_employeeHasEmployers_paysPens").find("input:checked").val();
    var lPensEmployer = PensEmployer;
    var lPensEmployee = PensEmployee;

    if(aportaPens == "-1"){
        lPensEmployer = 0;
        lPensEmployee = 0;
    }

    $("#diasTrabajadosMod").text("");
    var aid = 0;
    var aidD = 0;
    var sisben = null;
    var transport = $("input[name='register_employee[employeeHasEmployers][transportAid]']:checked").val();
    if (type.parent().text() == " Trabajador por días") {
        type = "days";
        sisben = 1;
        //numberOfDays=$("#register_employee_employeeHasEmployers_weekWorkableDays").val() * 4.34523810;
        numberOfDays=$("#register_employee_employeeHasEmployers_weekWorkableDays").val() * 4;
        $("#diasTrabajadosMod").text("Los cálculos asumen que el empleado trabajará " + numberOfDays/*.toFixed(2)*/ + " días al mes");
    } else {
        $("#diasTrabajadosMod").text("");
        type = "complete";
    }
    var totalExpenses = 0;
    var totalIncome = 0;
    var plainSalary = 0;
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
    var salaryM2 = 0;
    var base = 0;
    var primaCal = 0;
    if (aid == 0) {
        aidD = 0;
    }
    if (type == "days") {
        transport = 1;
        if (transport == 1) {
            //salaryD -= transportAidDaily;
        }
        //if it overpass the SMMLV calculates as a full time job  or
        //if does not belongs to SISBEN
        var PensEmployeeCal2 = 0;
        var salaryD2 = 0;

        var base2=smmlv;
        if (numberOfDays <= 7) {
            PensEmployeeCal2 = lPensEmployee * base2 / 4;
            salaryD2 = (salaryD - transportAidDaily)+(PensEmployeeCal2/numberOfDays);
        } else if (numberOfDays <= 14) {
            PensEmployeeCal2 = lPensEmployee * base2 / 2;
            salaryD2 = (salaryD - transportAidDaily)+(PensEmployeeCal2/numberOfDays);
        } else if (numberOfDays <= 21) {
            PensEmployeeCal2 = lPensEmployee * base2 * 3 / 4;
            salaryD2 = (salaryD - transportAidDaily)+(PensEmployeeCal2/numberOfDays);
        } else {
            PensEmployeeCal2 = lPensEmployee * base2;
            salaryD2 = (salaryD - transportAidDaily)+(PensEmployeeCal2/numberOfDays);
        }

        var displayError = false;
        if (salaryD2  > smmlv/numberOfDays || sisben == -1) {
            displayError = true;
            if (((salaryD + transportAidDaily + aidD) * numberOfDays) > smmlv) {
                base = (salaryD + aidD) * numberOfDays;
            } else {
                base = smmlv;
            }
            transportCal = transportAidDaily * numberOfDays;
            var localEPS = smmlv / 30 / numberOfDays;;
            var localPens =smmlv / 30 / numberOfDays;
            if(aportaPens == "-1"){
                localPens = 0;
            }
            salaryD = salaryD - transportAidDaily + localEPS + localPens;
            //salaryD = (salaryD - transportAidDaily)/(1-(lPensEmployee + EPSEmployee));
            totalExpenses = ((salaryD + aidD + transportAidDaily + dotationDaily) * numberOfDays) + ((EPSEmployer +
                lPensEmployer + arlProf + caja + sena + icbf) * base) + (vacations30D * numberOfDays * salaryD) +
                ((taxCes + ces) * (((salaryD + aidD) * numberOfDays) + transportAidDaily*numberOfDays));
            EPSEmployerCal = EPSEmployer * base;
            EPSEmployeeCal = smmlv / 30;
            PensEmployerCal = lPensEmployer * base;
            PensEmployeeCal = smmlv / 30;
            if(aportaPens == "-1"){
                PensEmployeeCal = 0;
            }
            arlCal = arlProf * base;
            //cesCal = ((ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            cesCal = ((ces) * (((salaryD + aidD) * numberOfDays ) + transportAidDaily*numberOfDays));
            primaCal = ((prima) * (((salaryD + aidD) * numberOfDays ) + transportAidDaily*numberOfDays));
            //taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays) + transportAidDaily*numberOfDays));
            cajaCal = caja * base;
            vacationsCal = vacations30D * numberOfDays * salaryD;
            dotationCal = dotationDaily * numberOfDays;
            senaCal = sena * base;
            icbfCal = icbf * base;
            totalIncome = (salaryD * numberOfDays) - EPSEmployerCal - PensEmployerCal;
            plainSalary = salaryD * numberOfDays;

        } else {
            transportCal = transportAidDaily * numberOfDays;
            var EPSEmployee2 = 0;
            var EPSEmployer2 = 0;
            base = smmlv;
            //calculate the caja and pens in base of worked days
            if (numberOfDays <= 7) {
                PensEmployerCal = lPensEmployer * base / 4;
                PensEmployeeCal = lPensEmployee * base / 4;
                cajaCal = caja * base / 4;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);

            } else if (numberOfDays <= 14) {
                PensEmployerCal = lPensEmployer * base / 2;
                PensEmployeeCal = lPensEmployee * base / 2;
                cajaCal = caja * base / 2;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);
            } else if (numberOfDays <= 21) {
                PensEmployerCal = lPensEmployer * base * 3 / 4;
                PensEmployeeCal = lPensEmployee * base * 3 / 4;
                cajaCal = caja * base * 3 / 4;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);
            } else {
                PensEmployerCal = lPensEmployer * base;
                PensEmployeeCal = lPensEmployee * base;
                cajaCal = caja * base;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);
            }
            //then calculate arl ces and the rest
            totalExpenses = ((salaryD + aidD + transportAidDaily + dotationDaily) * numberOfDays) + ((EPSEmployee2 + arlProf
                + sena + icbf) * base) + (vacations30D * numberOfDays * salaryD) + ((taxCes + ces) * (((salaryD + aidD)
                * numberOfDays) + transportAidDaily*numberOfDays)) + PensEmployeeCal + cajaCal + PensEmployerCal;
            EPSEmployerCal = EPSEmployer2 * base;
            EPSEmployeeCal = EPSEmployer2 * base;
            arlCal = arlProf * base;
            //cesCal = ((ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            cesCal = ((ces) * (((salaryD + aidD) * numberOfDays ) + transportAidDaily*numberOfDays));
            primaCal = ((prima) * (((salaryD + aidD) * numberOfDays ) + transportAidDaily*numberOfDays));
            //taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays) + transportAidDaily*numberOfDays));
            vacationsCal = vacations30D * numberOfDays * salaryD;
            dotationCal = dotationDaily * numberOfDays;
            senaCal = sena * base;
            icbfCal = icbf * base;
            totalIncome = ((salaryD + transportAidDaily) * numberOfDays) - PensEmployeeCal;
            plainSalary = salaryD * numberOfDays;
        }

    } else {
        var transportAid2=0;
        if (transport == 1) {
            //salaryM -= transportAid;
        } else if (salaryM + aidD > smmlv * 2) {
            transportAid2 = 0;
        }else{
            transportAid2=transportAid;
        }

        salaryM2 = (salaryM - transportAid2)/(1-(EPSEmployee+lPensEmployee));
        totalExpenses = salaryM + aidD + transportAid2 + dotation + ((EPSEmployer + lPensEmployer + arlProf + caja +
            vacations30D + sena + icbf) * (salaryM + aidD)) + ((taxCes + ces) * (salaryM + aidD + transportAid2));
        EPSEmployerCal = EPSEmployer * (salaryM + aidD);
        EPSEmployeeCal = EPSEmployee * (salaryM + aidD);
        PensEmployerCal = lPensEmployer * (salaryM + aidD);
        PensEmployeeCal = lPensEmployee * (salaryM + aidD);
        arlCal = arlProf * (salaryM + aidD);
        cesCal = ces * (salaryM + aidD + transportAid2);
        primaCal = prima * (salaryM + aidD + transportAid2);
        taxCesCal = taxCes * (salaryM + aidD + transportAid2);
        cajaCal = caja * (salaryM + aidD);
        vacationsCal = vacations30D * (salaryM + aidD);
        transportCal = transportAid2;
        dotationCal = dotation;
        senaCal = sena * (salaryM + aidD);
        icbfCal = icbf * (salaryM + aidD);
        totalIncome = (salaryM + transportCal - EPSEmployerCal - PensEmployerCal);
        plainSalary = salaryM;
    }
    var resposne = [];

    if ((type == "days"&&(salaryD <= 0 || numberOfDays == null || numberOfDays == 0))||(type != "days"&&(salaryM<=0))) {
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
        resposne['primaCal'] = 0;
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
        resposne['plainSalary'] = plainSalary;
        resposne['numberOfDays'] = numberOfDays;
        resposne['salaryM2'] = salaryM2;
        resposne['primaCal'] = primaCal;

        if(type=="days"&& EPSEmployerCal>0 && sisben==1){
            $("#arsNotAplicable").show();
            lockCalc = true;
        }else{
            $("#arsNotAplicable").hide();
            lockCalc = false;
        }

        /*if ( displayError == true && type=="days" ){
         $("#dontHaveSisben").show();
         }
         else {
         $("#dontHaveSisben").hide();
         }*/
    }
    // Calculate the days again.
    var i = 0;

    $("[name='register_employee[employeeHasEmployers][weekDays][]']:checked").each(function () {
        i++;
    });
    $("#register_employee_employeeHasEmployers_weekWorkableDays").val(i);
    var htmlRes = jsonCalcToHTML(resposne);
    if ($("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked").parent().text() == " Trabajador por días") {
        if( firstLoad == true){
            $("#labelCosto").html("Costo diario </br> del empleado (sin prestaciones)");
            firstLoad = false;
        }

        //$('#radio_diario').prop('checked', true);
        //$('#radio_mensual').prop('checked', false);
    }
    changeValues(resposne);

    //$("#calculatorResultsModal").find(".modal-body").html(htmlRes);

    //$("#totalExpensesVal").val(totalExpenses.toFixed(0));

}

$("input[name='register_employee[employeeHasEmployers][timeCommitment]']").on("click", function () {
    if( $(this).val() == 1 ){
        $('#radio_diario').prop('checked', false);
        $('#radio_mensual').prop('checked', true);
        $("#labelCosto").html("Costo del empleado +</br> seguridad social (sin prestaciones)");
        $("#wealthBlock").show();
    }
    else {
        $('#radio_diario').prop('checked', true);
        $('#radio_mensual').prop('checked', false);
        $("#labelCosto").html("Costo diario </br> del empleado (sin prestaciones)");
        $("#wealthBlock").hide();
    }
});

function checkDate(date) {
    var $permittedDate= $("#datePermitted");
    var dateNow = new Date(
        $permittedDate.find(".year").text(),
        parseInt($permittedDate.find(".month").text()),
        $permittedDate.find(".day").text()
    );
    if (date < dateNow) {
        $("#dateContract").modal("show");
        return false;
    }
    return true;
}

function checkDateVsStart(date) {
    var $permittedDate= $("#datePermitted");
    var dateNow = new Date(
        $("#register_employee_employeeHasEmployers_startDate_year").val(),
        parseInt($("#register_employee_employeeHasEmployers_startDate_month").val()),
        $("#register_employee_employeeHasEmployers_startDate_day").val()
    );

    if (date < dateNow) {
        $("#dateContract2").modal("show");
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
    if (selectedVal == " Trabajador por días") {
        var i = 0;
        $("[name='register_employee[employeeHasEmployers][weekDays][]']:checked").each(function () {
            i++;
        });
        if(i==0){
            showModal(12);
            return false;
        }

        var salMinDiario = Math.round(smmlv / 30);
        var numberToCmp = parseFloat(accounting.unformat($("#totalExpensesValD").val()));

        if(numberToCmp < salMinDiario)
        {
            if(numberToCmp !=0 || $("#register_employee_employeeHasEmployers_salaryD").val() != 0) {
                $("#salarioMinimo").find('.modal-body').html('El salario diario debe ser más alto para que el salario contractual sea del mínimo legal vigente ($' + getPrice(salMinDiario) + ').');
                $("#salarioMinimo").modal('show');
                $("#register_employee_employeeHasEmployers_salaryD").val(salMinDiario);
                reverseCalculator();
            }
            else{
                showModal(3);
            }
            return false;
        }

        if(lockCalc == true){
            return false;
        }

    } else {
        var salarioMinimo = smmlv;

        salarioMes = parseFloat(accounting.unformat($("#register_employee_employeeHasEmployers_salary").val()));
        if(!salarioMes){
            if(salarioMes!= 0){
                $("#salarioMinimo").find('.modal-body').html('El salario mínimo legal es de $ ' + getPrice(salarioMinimo)+' pesos.');
                $("#salarioMinimo").modal('show');
                $("#register_employee_employeeHasEmployers_salary").val((salarioMinimo));
                calculator();
                formatMoney($("#totalExpensesVal"));
                formatMoney($("#register_employee_employeeHasEmployers_salary"));
                formatMoney($(this));
            }else{
                showModal(3);
            }
            return false;
        }
        if (salarioMes < salarioMinimo) {
            $("#salarioMinimo").find('.modal-body').html('El salario mínimo legal es de $ ' + getPrice(salarioMinimo)+' pesos.');
            $("#salarioMinimo").modal('show');
            $("#register_employee_employeeHasEmployers_salary").val((salarioMinimo));
            calculator();
            formatMoney($("#totalExpensesVal"));
            formatMoney($("#register_employee_employeeHasEmployers_salary"));
            formatMoney($(this));
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
            personType: '2'
        }
    }).done(function (data) {
        //alert("La cédula que nos proporcionó, ya existe en nuestro sistema, los dátos serán cargados automáticamente");
        //load the data
        var form = $("[name='register_employee']");
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
        $(form).find("#register_employee_person_phones_0_phoneNumber").val(data["phones"]);
        //$("#documentExistent").modal("show");
        //console.log(data);
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
                $hasIt.parent().parent().find( "label[for*='cellphone']" ).text("Celular del empleado que usará para Daviplata");
                //$(this).hide();
            });
        } else {
            $hasIt.parent().parent().find(".form-group").each(function () {
                $hasIt.parent().parent().find( "label[for*='cellphone']" ).text("Número celular Daviplata");
                $(this).show();
            })
        }
        $hasIt.on("change", function () {
            var selectedVal = $(this).val();
            if (selectedVal == 1) {
                $hasIt.parent().parent().find( "label[for*='cellphone']" ).text("Número celular Daviplata");
                $hasIt.parent().parent().find(".form-group").each(function () {
                    $(this).show();
                })
            } else {

                /*$hasIt.parent().parent().find(".form-group").not(".formFieldsNo").each(function () {
                 $(this).hide();
                 });*/
                $hasIt.parent().parent().find( "label[for*='cellphone']" ).text("Celular del empleado que usará para Daviplata");
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
function checkSisben(){
    var sisben = $("input[name='register_employee[employeeHasEmployers][sisben]']:checked").parent().text();
    if(sisben==" No"){
        $("#arsBlock").hide();
        $("#wealthBlock").show();
    }else if(sisben==" Si"){
        $("#arsBlock").show();
        $("#wealthBlock").hide();
    }
}
function initEntitiesFields(){
    checkSisben();
    $("#register_employee_employeeHasEmployers_sisben").on("change", function () {
        var sisben=$(this).find("input:checked").parent().text();
        if(sisben==" No") {
            $("#arsBlock").hide();
            $("#wealthBlock").show();
        }else if(sisben==" Si"){
            $("#arsBlock").show();
            $("#wealthBlock").hide();
        }
    });

    var dataWe=[];
    $("#register_employee_entities_wealth").find("> option").each(function() {
        dataWe.push({'label':this.text,'value':this.value});
    });
    $(".autocomW").each(function () {
        var autoTo=$(this);
        $(autoTo).autocomplete({
            source: function(request, response) {
                var results;
                if(request.term.length != 0){
                    results = $.ui.autocomplete.filter(dataWe, request.term);
                }
                else {
                    results = $.ui.autocomplete.filter("", request.term);
                }
                response(results.slice(0, 5));
            },                minLength: 0,
            select: function(event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
                $($(this).parent()).parent().find("select").each(function() {
                    if($(this).parent().parent().attr("class") == "hidden"){
                        $(this).val(ui.item.value);
                    }
                });
            },
            focus: function(event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
                $($(this).parent()).parent().find("select").each(function() {
                    if($(this).parent().parent().attr("class") == "hidden"){
                        $(this).val(ui.item.value);
                    }
                });
            }

        });
        $(autoTo).on("focus",function () {
            $(autoTo).autocomplete("search", $(autoTo).val());
        });

    });

}

function oneYearFromNow(date){
    date.setDate( date.getDate() + 364 );
    return date;
}

function reverseCalculator(){

    var plainSalary = parseFloat(accounting.unformat($("#register_employee_employeeHasEmployers_salaryD").val()));
    if(plainSalary == ""){
        $("#register_employee_employeeHasEmployers_salaryD").val(0);
        calculator();
        return;
    }
    var salaryD = 0;

    var aid = 0;
    var aidD = 0;
    var sisben = $("input[name='register_employee[employeeHasEmployers][sisben]']:checked").val();
    var transport = $("input[name='register_employee[employeeHasEmployers][transportAid]']:checked").val();
    //var numberOfDays= $("#register_employee_employeeHasEmployers_weekWorkableDays").val() * 4.345;
    var numberOfDays= $("#register_employee_employeeHasEmployers_weekWorkableDays").val() * 4;

    var PensEmployeeCal = 0;
    var base = 0;

    var aportaPens = $("#register_employee_employeeHasEmployers_paysPens").find("input:checked").val();
    var lPensEmployee = PensEmployee;

    var pensL = smmlv / 30 / numberOfDays;
    var saluL = smmlv / 30 / numberOfDays;

    if(aportaPens == "-1"){
        lPensEmployee = 0;
        pensL = 0;
    }

    if( sisben == -1 || (plainSalary + transportAidDaily + aidD) * numberOfDays > smmlv){
        salaryD = plainSalary + transportAidDaily - pensL - saluL;
    }
    else {
        base = smmlv;
        salaryD = plainSalary;

        if (numberOfDays <= 7) {
            PensEmployeeCal = lPensEmployee * base / 4;
            salaryD = (salaryD + transportAidDaily) - (PensEmployeeCal/numberOfDays);
        } else if (numberOfDays <= 14) {
            PensEmployeeCal = lPensEmployee * base / 2;
            salaryD = (salaryD + transportAidDaily) - (PensEmployeeCal/numberOfDays);
        } else if (numberOfDays <= 21) {
            PensEmployeeCal = lPensEmployee * base * 3 / 4;
            salaryD = (salaryD + transportAidDaily) - (PensEmployeeCal/numberOfDays);
        } else {
            PensEmployeeCal = lPensEmployee * base;
            salaryD = (salaryD + transportAidDaily) - (PensEmployeeCal/numberOfDays);
        }
    }

    $("#register_employee_employeeHasEmployers_salaryD").val(Math.round(salaryD));
    calculator();

}
