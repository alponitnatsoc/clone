
/**
 * Created by gabrielsamoma on 11/11/15.
 */
function startEmployerEdit() {
    var validator;
    $.getScript("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("form[name='edit_employer']").validate({
            rules: {
                "edit_employer[person][documentType]": "required",
                "edit_employer[person][document]": "required",
                "edit_employer[person][names]": "required",
                "edit_employer[person][lastName1]": "required",
                "edit_employer[person][mainAddress]": "required",
                "edit_employer[person][department]": "required",
                "edit_employer[person][city]": "required",
                "edit_employer[workplaces]": "required"
            },
            messages: {
                "edit_employer[person][documentType]": "Por favor selecciona el tipo de documento",
                "edit_employer[person][document]": "Por favor ingresa tu documento",
                "edit_employer[person][names]": "Por favor ingresa tu nombre",
                "edit_employer[person][lastName1]": "Por favor ingresa tu primer apellido",
                "edit_employer[person][mainAddress]": "Por favor ingrese una dirección",
                "edit_employer[person][department]": "Por favor selecciona un departamento",
                "edit_employer[person][city]": "Por favor selecciona una ciudad",
                "edit_employer[workplaces]": "Por favor ingresa un nombre para tu lugar de trabajo"
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
        $("ul.workplaces input[name*='mainAddress']").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "Por favor ingresa una dirección"
                }
            });
        });
        $("ul.workplaces select").each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "Por favor seleciona una opción"
                }
            });
        });
    });
    var $collectionHolderPhones;
    var $addPhoneLink = $('<a href="#" class="col-md-5 col-xs-8 add_phone_link" style="padding-top:2px !important;padding:10px;color:#00cdcc;text-decoration: none;"><i class="fa fa-plus-circle" style="color:#00cdcc;"></i> Adicionar nuevo lugar de trabajo</a>');
    var $newLinkLi = $('<li class="col-md-12"></li>').append($addPhoneLink);
    var $collectionHolder;
    $collectionHolderPhones = $('ul.phones');
    $collectionHolder = $('ul.workplaces');

    $collectionHolder.find('li').each(function () {
        addTagFormDeleteLink($(this), "lugar de trabajo");
    });
    $collectionHolder.append($newLinkLi);
    $collectionHolder.data('index', ($collectionHolder.find(':input').length) / 5);

    $addPhoneLink.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        addPhoneForm($collectionHolder, $newLinkLi);
    });

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    //el cambio de tabs entre el formulario de registro
    $('.btnPrevious').click(function () {
        $('.nav-tabs > .active').prev('li').find('a').trigger('click');
    });

    $('#btn-1').click(function (e) {
        e.preventDefault();
        var form = $("form");
        var documentType = $(form).find("select[name='edit_employer[person][documentType]']");
        var document = $(form).find("input[name='edit_employer[person][document]']");
        var names = $(form).find("input[name='edit_employer[person][names]']");
        var lastName1 = $(form).find("input[name='edit_employer[person][lastName1]']");
        var lastName2 = $(form).find("input[name='edit_employer[person][lastName2]']");
        if (!form.valid()) {
            return;
        }
        //if (!(validator.element(documentType) && validator.element(document) && validator.element(names) && validator.element(lastName1))) {
        //    alert("Llenaste algunos campos incorrectamente");
        //    return;
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
                year: $(form).find("select[name='edit_employer[person][birthDate][year]']").val(),
                month: $(form).find("select[name='edit_employer[person][birthDate][month]']").val(),
                day: $(form).find("select[name='edit_employer[person][birthDate][day]']").val(),
            }
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
        var idsPhones = [], phones = [];
        var mainAddress = $(form).find("input[name='edit_employer[person][mainAddress]']");
        var department = $(form).find("select[name='edit_employer[person][department]']");
        var city = $(form).find("select[name='edit_employer[person][city]']");
        if (!form.valid()) {
            return;
        }
        //if (!(validator.element(mainAddress) && validator.element(department) && validator.element(city))) {
        //    alert("Llenaste algunos campos incorrectamente");
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
            alert("Llenaste algunos campos incorrectamente");
            return;
        }
        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                mainAddress: mainAddress.val(),
                neighborhood: "",
                phonesIds: idsPhones,
                phones: phones,
                department: department.val(),
                city: city.val(),
            }
        }).done(function (data) {
            /*
            var selected = $("input[name='edit_employer[sameWorkHouse]']:checked").val();
            if (selected == "1") {
                //$('ul.workplaces').hide();
                var select = $("#edit_employer_workplaces_0_id");
                $("#edit_employer_workplaces_0_name").val("Dirección Principal");
                $("#edit_employer_workplaces_0_mainAddress").val($("#edit_employer_person_mainAddress").val());
                $("#edit_employer_workplaces_0_department").val($("#edit_employer_person_department").val());
                $("#edit_employer_workplaces_0_city").val($("#edit_employer_person_city").val());

            }*/
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if(jqXHR==errorHandleTry(jqXHR)){
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    //funcion que agrega un listener a cada department
    addListeners();

    //colocar el select en el valor del tamaño del arreglo
    var dropDownWork = $collectionHolder.data('index');
    if (dropDownWork == 1 && $("input[name='edit_employer[sameWorkHouse]']:checked").val() != '0') {
        //$('ul.workplaces').hide();
    }
    /* $('#edit_employer_numberOfWorkplaces').val($dropDownWork);
     //listener para el que agrega workplaces
     $('#edit_employer_numberOfWorkplaces').change(function() {
     // get the numberof Workplaces that the user wants
     console.log($collectionHolder.data('index'));
     var workplacesCount= $(this).val();
     // get the new index the numer of workplaces that the user has on screen
     var index = parseInt($collectionHolder.data('index'));
     var diference=workplacesCount-index;
     if (diference<0) {
     //remove the diference of workplaces;
     diference=diference+1;
     for (var i = diference; i <= 0; i++) {
     $('#workSpace_'+(index)).remove();
     console.log("Index Dele:" + index );
     index=parseInt(index)-1;
     };
     $collectionHolder.data('index', workplacesCount);


     }
     else{
     //add the diference of workplaces
     for (var i = 0; i < diference; i++) {
     // Get the data-prototype explained earlier
     var prototype = $collectionHolder.data('prototype');
     // Replace '__name__' in the prototype's HTML to
     // instead be a number based on how many items we have
     var newForm = prototype.replace(/__name__/g, index);
     // increase the index with one for the next item
     $collectionHolder.data('index', index + 1);
     index=parseInt(index)+1;
     console.log("Index New:" + index );
     // Display the form in the page in an li, before the "Add a tag" link li
     var $newFormLi = $('<li id="workSpace_'+ index +'" class="workSpaceLi"></li>').append('<div class="col-sm-12 col-xs-12" style="border-bottom: 1px solid rgba(0, 0, 0, 0);"><div class="col-sm-12 col-xs-12">'+newForm+'</div></div>');
     $collectionHolder.append($newFormLi);
     }
     //add the corresponding listeners
     addListeners();
     }
     });*/
    var redirUri = "";
    $("form").on("submit", function (e) {
        e.preventDefault();

        var form = $("form");
        var names = [], addresses = [], citys = [], departments = [], ids = [];
        var i = 0;
        $(form).find("ul.workplaces input[name*='id']").each(function () {
            ids[i++] = $(this).val();
        });
        i = 0;
        var flagValid = true;
        var modal = document.getElementById('exceptionModal');
        $(form).find("ul.workplaces input[name*='mainAddress']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
            addresses[i++] = $(this).val();
        });
        if (!flagValid) {
            //modal.style.display = "block";
            return;
        }
        i = 0;
        $(form).find("ul.workplaces input[name*='name']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
            names[i++] = $(this).val();
        });
        if (!flagValid) {
            //modal.style.display = "block";
            return;
        }
        i = 0;
        $(form).find("ul.workplaces select[name*='city']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
            citys[i++] = $(this).val();
        });

        if (!flagValid) {
            //modal.style.display = "block";
            return;
        }
        i = 0;
        $(form).find("ul.workplaces select[name*='department']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
            departments[i++] = $(this).val();
        });

        if (!flagValid) {
            //modal.style.display = "block";
            return;
        }
        i = 0;

        var sameWorkHouse = $(form).find("input[name='edit_employer[sameWorkHouse]']");

        $('#editedModal').modal('toggle');
        $.ajax({
            url: form.attr('action'),
            type: $(form).attr('method'),
            data: {
                workId: ids,
                workName: names,
                workMainAddress: addresses,
                workCity: citys,
                workDepartment: departments
            },
            statusCode: {
                200: function (data) {
                    console.log(data);
                    if (data["url"] != null) {
                        console.log(data["url"]);
                        redirUri = "/dashboard/employer";
                    } else {
                        $('#main').replaceWith(
                                // ... with the returned one from the AJAX response.
                                $(data).find('#main'));
                        addClick();
                        if (!jsLoader(url)) {
                            addSumbit();
                        }
                    }
                }

            }
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if(jqXHR==errorHandleTry(jqXHR)){
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });
    });
    $("#employerDismiss").on('click', function () {
        sendAjax(redirUri);
        history.pushState({}, '', redirUri);
    });
}


function jsonToHTML(data) {
    var htmls = "<option value=''>Seleccionar una opción</option>";
    for (var i = 0; i < data.length; i++) {
        htmls += "<option value='" + data[i].id_city + "'>" + data[i].name + "</option>";
    }
    return htmls;
}
function addListeners() {
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
    /*
    $("input[name='edit_employer[sameWorkHouse]']").change(function () {
        var selected = $("input[name='edit_employer[sameWorkHouse]']:checked").val();
        if (selected == "1") {
            //$('ul.workplaces').hide();
            var select = $("#edit_employer_workplaces_0_id");
                $("#edit_employer_workplaces_0_name").val("Dirección Principal");
                $("#edit_employer_workplaces_0_mainAddress").val($("#edit_employer_person_mainAddress").val());
                $("#edit_employer_workplaces_0_department").val($("#edit_employer_person_department").val());
                $("#edit_employer_workplaces_0_city").val($("#edit_employer_person_city").val());

        } else {
            $('ul.workplaces').show();
            if ($("#edit_employer_workplaces_0_mainAddress").val() == $("#edit_employer_person_mainAddress").val()) {
                $("#edit_employer_workplaces_0_name").val("");
                $("#edit_employer_workplaces_0_mainAddress").val("");
                $("#edit_employer_workplaces_0_department").val("");
                $("#edit_employer_workplaces_0_city").val("");
            }
        }
    })
    var selected = $("input[name='edit_employer[sameWorkHouse]']:checked").val();
    if (selected == "1") {
        //$('ul.workplaces').hide();
        var select = $("#edit_employer_workplaces_0_id");
        $("#edit_employer_workplaces_0_name").val("Dirección Principal");
        $("#edit_employer_workplaces_0_mainAddress").val($("#edit_employer_person_mainAddress").val());
        $("#edit_employer_workplaces_0_department").val($("#edit_employer_person_department").val());
        $("#edit_employer_workplaces_0_city").val($("#edit_employer_person_city").val());

    }*/

}
function addPhoneForm($collectionHolderB, $newLinkLi) {
    var prototype = $collectionHolderB.data('prototype');
    var index = $collectionHolderB.data('index');
    var newForm = prototype.replace(/__name__/g, index);
    $collectionHolderB.data('index', index + 1);
    var $newFormLi = $('<li class="col-sm-12"></li>').append(newForm);
    addTagFormDeleteLink($newFormLi, "lugar de trabajo");
    $newLinkLi.before($newFormLi);
}
function addTagFormDeleteLink($tagFormLi, $tipo) {
    var $removeFormA = $('<a href="#" class="col-sm-5 col-xs-8 remove_phone_link" style="padding:10px;color:#fd5c5c;text-decoration: none;"><i class="fa fa-minus-circle " style="color:#fd5c5c;max-width: 30px;"></i> Eliminar esta dirección de trabajo</a>');
    $tagFormLi.append($removeFormA);

    $removeFormA.on('click', function (e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // remove the li for the tag form
        $tagFormLi.remove();
    });
}