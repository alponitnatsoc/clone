
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
                "register_employee[employeeHasEmployers][employeeType]": "required",
                "register_employee[employeeHasEmployers][contractType]": "required",
                "register_employee[employeeHasEmployers][timeCommitment]": "required",
                "register_employee[employeeHasEmployers][position]": "required",
                "register_employee[employeeHasEmployers][workplaces]": "required",
                "register_employee[employeeHasEmployers][transportAid]": "required",
                "register_employee[employeeHasEmployers][payMethod]": "required"/*,
                "register_employee[credit_card]": "required",
                "register_employee[cvv]": "required",
                "register_employee[expiry_date]": "required",
                "register_employee[name_on_card]": "required"*/

            },
            messages: {
                "register_employee[person][documentType]": "Por favor selecciona un tipo de documento",
                "register_employee[person][document]": {required: "Por favor ingresa un documento", number: "ingresa solamente dígitos"},
                "register_employee[person][names]": "Por favor ingresa el nombre",
                "register_employee[person][lastName1]": "Por favor ingresa el primer apellido",
                "register_employee[person][mainAddress]": "Por favor ingresa una dirección",
                //"register_employee[employeeHasEmployers][salary]": "Por favor ingresa un salario",
                "register_employee[person][department]": "Por favor selecciona un departamento",
                "register_employee[person][city]": "Por favor selecciona una ciudad",
                "register_employee[employeeHasEmployers][employeeType]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][contractType]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][timeCommitment]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][position]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][workplaces]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][transportAid]": "Por favor selecciona una opción",
                "register_employee[employeeHasEmployers][payMethod]": "Por favor selecciona una opción"/*,
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
    var contractType=$("#register_employee_employeeHasEmployers_contractType");
    /*contractType.change(function () {
        var selectedVal = $(this).find("option:selected").text();
        if (selectedVal == "Término fijo") {
            $(".definite").each(function () {
                $(this).show();
            });
        } else {
            $(".definite").each(function () {
                $(this).hide();
            });
        }
    });*/
    selectedVal = $(contractType).find("option:selected").text();
    if (selectedVal == "Término fijo") {
        $("#register_employee_employeeHasEmployers_existentNew_0").attr("checked", true);
        $(".definite").each(function () {
            $(this).show();
        });
    } else {
        $(".definite").each(function () {
            $(this).hide();
        });
    }
    //funcion que agrega un listener a cada department
    addListeners();
    var payType=$("input[name='register_employee[employeeHasEmployers][payMethod]']");
    var payTypeChecked=$("input[name='register_employee[employeeHasEmployers][payMethod]']:checked");
    if(payTypeChecked.val()!=null){
        $.ajax({
            url: '/pay/method/fields/' + payTypeChecked.val()+'/'+$("input[name='register_employee[idContract]']").val(),
            type: 'GET'
        }).done(function (data) {
            $('#putFields_' + payTypeChecked.val()).html(
                // ... with the returned one from the AJAX response.
                $(data).find('#formFields'));
        });
    }

    payType.on('change', function () {
        var payMethod = $("input[name='register_employee[employeeHasEmployers][payMethod]']:checked");
        $.ajax({
            url: '/pay/method/fields/' + payMethod.val(),
            type: 'GET'
        }).done(function (data) {
            $('#putFields_' + payMethod.val()).html(
                    // ... with the returned one from the AJAX response.
                    $(data).find('#formFields'));
            $('#putFields_' + payTypeChecked.val()).html("");
            payTypeChecked=payMethod;

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
                frequencyId: $(form).find("select[name='method_type_fields[frequencyFrequency]']").val(),
                accountNumber: $(form).find("input[name='method_type_fields[accountNumber]']").val(),
                cellphone: $(form).find("input[name='method_type_fields[cellphone]']").val(),
                contractId: $(form).find("input[name='register_employee[idContract]']").val(),
            }
        }).done(function (data) {
            console.log(data["url"]);
            history.pushState("","",data["url"]);
            sendAjax(data["url"]);
        }).fail(function (data, textStatus, errorThrown) {
            console.log(data);
            console.log(textStatus);
            console.log(errorThrown);
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
    var $addBenefitLink = $('<a href="#" class="col-md-4 add_phone_link" style="padding-top:2px !important;padding:10px;color:#00cdcc;text-decoration: none;"><i class="fa fa-plus-circle" style="color:#00cdcc;"></i> Adicionar nuevo beneficio</a>');
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
    $('#si_tiene_contrato').change(function(evt) {
        $('#noTarget').collapse('hide')
    });
    $('#no_tiene_contrato').change(function(evt) {
        $('#noTarget').collapse('show')
    });
    $('#btn-inquiry').click(function (e) {
        e.preventDefault();
        var form = $("form");
        var documentType = $(form).find("select[name='register_employee[person][documentType]']");
        var document = $(form).find("input[name='register_employee[person][document]']");
        var lastName1 = $(form).find("input[name='register_employee[person][lastName1]']");
        //if (!form.valid()) {
        //    return;
        //}
        if (!(validator.element(documentType) && validator.element(document) && validator.element(lastName1))) {
            //alert("Llenaste algunos campos incorrectamente");
            return;
        }

        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            beforeSend: function () {
                $('#btn-inquiry').html('Buscando...');
            },
            data: {
                documentType: documentType.val(),
                document: document.val(),
                lastName1: lastName1.val(),
            }
        }).done(function (data) {
            $('#btn-inquiry').html('Consultar');
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

        }).fail(function (jqXHR, textStatus, errorThrown) {
            //show the other stuf
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
            $('#formNav > .active').next('li').find('a').trigger('click');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            console.log(jqXHR);
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
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
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

        var idsBenef = [], amountBenef = [], periodicityBenef = [], weekWorkableDaysIds = [],benefType=[];
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
        $(form).find("[name='register_employee[employeeHasEmployers][weekWorkableDays][]']:checked").each(function () {
            weekWorkableDaysIds[i++] = $(this).val();
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
        var salary = $(form).find("input[name='register_employee[employeeHasEmployers][salary]']");
        /*if (!(validator.element(salary))) {
            //alert("Llenaste algunos campos incorrectamente");
            return;
        }*/

        if (!flagValid) {
            //alert("Llenaste algunos campos incorrectamente");
            return;
        }

        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
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
                startDate: $("#register_employee_employeeHasEmployers_startDate").val(),
                endDate: $("#register_employee_employeeHasEmployers_endDate").val(),
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
            alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
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
    var htmls="<h2 class='text-center'>Si su empleado tiene estas características debe pagar:</h2>" +
        "<ul class='list-group'>";
    htmls+="<li class='list-group-item'>Costo total para el empleador: "+data.totalExpenses+"</li>";
    htmls+="<li class='list-group-item'>Ingreso neto para el empleado: "+data.totalIncome+"</li>";
    htmls+="<li class='list-group-item'>Diario Gastos: "+data.dailyExpenses+"</li>";
    htmls+="<li class='list-group-item'>Diario Ingreso: "+data.dailyIncome+"</li>";
    htmls+="</ul>";
    htmls+="<h2 class='text-center'>Detalles:</h2>" +
        "<ul class='list-group'>";
    htmls+="<li class='list-group-item'>Gastos Empleador EPS: "+data.EPSEmployerCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleador Pensión: "+data.PensEmployerCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado ARL: "+data.arlCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Cesantias: "+data.cesCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Intereses/cesantias: "+data.taxCesCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Caja Comp: "+data.cajaCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Vacaciones: "+data.vacationsCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Auxilio de Trasnporte: "+data.transportCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Dotacion/150000 pesos trimestre: "+data.dotationCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos SENA: "+data.senaCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos ICBF: "+data.icbfCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado EPS: "+data.EPSEmployeeCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Pensión: "+data.PensEmployeeCal+"</li>";
    htmls+="</ul>";
    return htmls;
}

function addListeners() {
    var $salary =$("#register_employee_employeeHasEmployers_salaryD");
    $salary.val(accounting.unformat($salary.val()))
    var $slidder=$("#ex6");
    $slidder.slider();
    $slidder.on("slide", function(slideEvt) {
        $salary.val(slideEvt.value);
        calculator();
        formatMoney($("#totalExpensesVal"));
        formatMoney($salary);
    });
    $("input[name='register_employee[employeeHasEmployers][existentNew]']").on("change", function(){
        if($("input[name='register_employee[employeeHasEmployers][existentNew]']:checked").val()=="1") {
            $("#register_employee_employeeHasEmployers_contractType").find("option").each(function () {
                if ($(this).text() == "Término fijo") {
                    $(this).attr('selected', 'selected');
                    $(".definite").each(function () {
                        $(this).show();
                    });
                }
            });
        }
        else {
            $("#register_employee_employeeHasEmployers_contractType option").each(function () {
                if ($(this).text() == "Término indefinido") {
                    $(this).attr('selected', 'selected');
                    $(".definite").each(function () {
                        $(this).hide();
                    });
                }
            });
        }
    });
    $("#register_employee_employeeHasEmployers_weekWorkableDays").on("change",function(){
       calculator();
        formatMoney($("#totalExpensesVal"));

    });
    $("#register_employee_employeeHasEmployers_indefinite").on("click", function(){
        $("#register_employee_employeeHasEmployers_contractType option").each(function() {
            if($(this).text() == "Término indefinido") {
                $(this).attr('selected', 'selected');
                $(".definite").each(function () {
                    $(this).hide();
                });
            }
        });
    });
    $("#register_employee_employeeHasEmployers_existent").on("click", function(){
        $("#existentDataToShow").show();
    });
    $("#register_employee_employeeHasEmployers_new").on("click", function(){
        $('#contractNav > .active').next('li').find('a').trigger('click');
    });
    $("#register_employee_employeeHasEmployers_yesExistent").on("click", function(){
        $("#existentNext").attr('disabled',false);
    });
    $("#register_employee_employeeHasEmployers_noExistent").on("click", function(){
        history.pushState("","","/manage/employees");
        sendAjax("/manage/employees")
    });

    $salary.on("input", function(){
        calculator();
        formatMoney($("#totalExpensesVal"));
    });
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
function loadConstrains(){
    var constraints=null;
    $.ajax({
        url : "/api/public/v1/calculator/constraints",
        type: "GET",
        statusCode:{
            200: function(data){
                //Extract Constraints
                constraints= data["response"];
                transportAid=parseFloat(constraints['auxilio transporte']);
                smmlv=parseFloat(constraints['smmlv']);
                EPSEmployer=parseFloat(constraints['eps empleador']);
                EPSEmployee=parseFloat(constraints['eps empleado']);
                PensEmployer=parseFloat(constraints['pension empleador']);
                PensEmployee=parseFloat(constraints['pension empleado']);
                arl=parseFloat(constraints['arl']);
                caja=parseFloat(constraints['caja']);
                sena=parseFloat(constraints['sena']);
                icbf=parseFloat(constraints['icbf']);
                vacations=parseFloat(constraints['vacaciones']);
                taxCes=parseFloat(constraints['intereses cesantias']);
                ces=parseFloat(constraints['cesantias']);
                dotation=parseFloat(constraints['dotacion']);
                transportAidDaily=transportAid/30;
                vacations30D=vacations/30;
                dotationDaily=dotation/30;
                calculator();
            }
        }
    });

}

function calculator(){
    var type=$("input[name='register_employee[employeeHasEmployers][timeCommitment]']:checked");
    var salaryM=parseFloat(accounting.unformat($("#register_employee_employeeHasEmployers_salary").val()));
    var salaryD=parseFloat(accounting.unformat($("#register_employee_employeeHasEmployers_salaryD").val()));
    var numberOfDays=parseFloat($("#register_employee_employeeHasEmployers_weekWorkableDays").val())*4;
    var aid=0;
    var aidD=0;
    var sisben=$("input[name='register_employee[employeeHasEmployers][sisben]']:checked").val();
    var transport=$("input[name='register_employee[employeeHasEmployers][transportAid]']:checked").val();
    if(transport==1){
        transportAid=0;
    }
    if(type.parent().text()==" Trabajo por días"){
        type="days";
    }else{type="complete";}
    transport=0;
    var totalExpenses=0;
    var totalIncome=0;
    var EPSEmployerCal=0;
    var EPSEmployeeCal=0;
    var PensEmployeeCal=0;
    var PensEmployerCal=0;
    var transportCal=0;
    var cesCal=0;
    var taxCesCal=0;
    var dotationCal=0;
    var vacationsCal=0;
    var arlCal=0;
    var cajaCal=0;
    var senaCal=0;
    var icbfCal=0;
    var base=0;
    if(aid==0){
        aidD=0;
    }
    if(type=="days"){
        if(transport==1){
            salaryD-=transportAidDaily;
        }
        //if it overpass the SMMLV calculates as a full time job  or
        //if does not belongs to SISBEN
        if(((salaryD+transportAidDaily+aidD)*numberOfDays)>smmlv||sisben==0){
            if(((salaryD+transportAidDaily+aidD)*numberOfDays)>smmlv){
                base=(salaryD+aidD)*numberOfDays;
            }else{base=smmlv;}

            totalExpenses=((salaryD+aidD+transportAidDaily+dotationDaily)*numberOfDays)+((EPSEmployer+PensEmployer+arl+caja+sena+icbf)*base)+(vacations30D*numberOfDays*salaryD)+((taxCes+ces)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            EPSEmployerCal=EPSEmployer*base;
            EPSEmployeeCal=EPSEmployee*base;
            PensEmployerCal=PensEmployer*base;
            PensEmployeeCal=PensEmployee*base;
            arlCal=arl*base;
            cesCal=((ces)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            taxCesCal=((taxCes)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            cajaCal=caja*base;
            vacationsCal=vacations30D*numberOfDays*salaryD;
            transportCal=transportAidDaily*numberOfDays;
            dotationCal=dotationDaily*numberOfDays;
            senaCal=sena*base;
            icbfCal=icbf*base;
            totalIncome=(salaryD*numberOfDays)-EPSEmployerCal-PensEmployerCal;
        }else{
            EPSEmployee=0;
            EPSEmployer=0;
            base=smmlv;
            //calculate the caja and pens in base of worked days
            if(numberOfDays<=7){
                PensEmployerCal=PensEmployer*base/4;
                PensEmployeeCal=PensEmployee*base/4;
                cajaCal=caja*base;
            }else if(numberOfDays<=14){
                PensEmployerCal=PensEmployer*base/2;
                PensEmployeeCal=PensEmployee*base/2;
                cajaCal=caja*base/2;
            }else if(numberOfDays<=21){
                PensEmployerCal=PensEmployer*base*3/4;
                PensEmployeeCal=PensEmployee*base*3/4;
                cajaCal=caja*base*3/4;
            }else {
                PensEmployerCal=PensEmployer*base;
                PensEmployeeCal=PensEmployee*base;
                cajaCal=caja*base;
            }
            //then calculate arl ces and the rest
            totalExpenses=((salaryD+aidD+transportAidDaily+dotationDaily)*numberOfDays)+((EPSEmployer+arl+sena+icbf)*base)+(vacations30D*numberOfDays*salaryD)+((taxCes+ces)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid))+PensEmployeeCal+cajaCal+PensEmployerCal;
            EPSEmployerCal=EPSEmployer*base;
            EPSEmployeeCal=EPSEmployee*base;
            arlCal=arl*base;
            cesCal=((ces)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            taxCesCal=((taxCes)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            vacationsCal=vacations30D*numberOfDays*salaryD;
            transportCal=transportAidDaily*numberOfDays;
            dotationCal=dotationDaily*numberOfDays;
            senaCal=sena*base;
            icbfCal=icbf*base;
            totalIncome=((salaryD+transportAidDaily)*numberOfDays)-PensEmployeeCal;
        }

    }else{
        if(transport==1){
            salaryM-=transportAid;
        }else if(salaryM+aidD>smmlv*2){
            transportAid=0;
        }

        totalExpenses=salaryM+aidD+transportAid+dotation+((EPSEmployer+PensEmployer+arl+caja+vacations30D+sena+icbf)*(salaryM+aidD))+((taxCes+ces)*(salaryM+aidD+transportAid));
        EPSEmployerCal=EPSEmployer*(salaryM+aidD);
        EPSEmployeeCal=EPSEmployee*(salaryM+aidD);
        PensEmployerCal=PensEmployer*(salaryM+aidD);
        PensEmployeeCal=PensEmployee*(salaryM+aidD);
        arlCal=arl*(salaryM+aidD);
        cesCal=ces*(salaryM+aidD+transportAid);
        taxCesCal=taxCes*(salaryM+aidD+transportAid);
        cajaCal=caja*(salaryM+aidD);
        vacationsCal=vacations30D*(salaryM+aidD);
        transportCal=transportAid;
        dotationCal=dotation;
        senaCal=sena*(salaryM+aidD);
        icbfCal=icbf*(salaryM+aidD);
        totalIncome=(salaryM+transportCal-EPSEmployerCal-PensEmployerCal);

    }
    var resposne=[];
    resposne['totalExpenses']=totalExpenses;
    resposne['dailyExpenses']=totalExpenses/numberOfDays;
    resposne['dailyIncome']=totalIncome/numberOfDays;
    resposne['EPSEmployerCal']=EPSEmployerCal;
    resposne['EPSEmployeeCal']=EPSEmployeeCal;
    resposne['PensEmployerCal']=PensEmployerCal;
    resposne['PensEmployeeCal']=PensEmployeeCal;
    resposne['arlCal']=arlCal;
    resposne['cesCal']=cesCal;
    resposne['taxCesCal']=taxCesCal;
    resposne['cajaCal']=cajaCal;
    resposne['vacationsCal']=vacationsCal;
    resposne['transportCal']=transportCal;
    resposne['dotationCal']=dotationCal;
    resposne['senaCal']=senaCal;
    resposne['icbfCal']=icbfCal;
    resposne['totalIncome']=totalIncome;
    var htmlRes=jsonCalcToHTML(resposne);
    $("#calculatorResultsModal").find(".modal-body").html(htmlRes);

    $("#totalExpensesVal").text(totalExpenses.toFixed(0));

}