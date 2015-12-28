
/**
 * Created by gabrielsamoma on 11/11/15.
 */
function startEmployer(){
    var $collectionHolderPhones;
    var $addPhoneLink = $('<a href="#" class="add_phone_link">Add Phone</a>');
    var $newLinkLi = $('<li></li>').append($addPhoneLink);
    var $collectionHolder;
    $collectionHolderPhones = $('ul.phones');
    $collectionHolder = $('ul.workplaces');
    $collectionHolderPhones.find('li').each(function() {
        addTagFormDeleteLink($(this));
    });
    $collectionHolderPhones.append($newLinkLi);
    $collectionHolderPhones.data('index', $collectionHolderPhones.find(':input').length);
    $addPhoneLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        addPhoneForm($collectionHolderPhones, $newLinkLi);
    });
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', ($collectionHolder.find(':input').length)/4);
    //el cambio de tabs entre el formulario de registro
    $('.btnPrevious').click(function(){
        $('.nav-tabs > .active').prev('li').find('a').trigger('click');
    });
    $('#btn-1').click(function(e){
        e.preventDefault();
        var form =$("form");
        $.ajax({
            url : $(this).attr('href'),
            type: 'POST',
            data: {
                youAre: 		$(form).find("input[name='register_employer[youAre]']:checked").val(),
                documentType: 	$(form).find("select[name='register_employer[person][documentType]']").val(),
                document: 		$(form).find("input[name='register_employer[person][document]']").val(),
                names:			$(form).find("input[name='register_employer[person][names]']").val(),
                lastName1: 		$(form).find("input[name='register_employer[person][lastName1]']").val(),
                lastName2: 		$(form).find("input[name='register_employer[person][lastName2]']").val(),
                year: 			$(form).find("select[name='register_employer[person][birthDate][year]']").val(),
                month: 			$(form).find("select[name='register_employer[person][birthDate][month]']").val(),
                day: 			$(form).find("select[name='register_employer[person][birthDate][day]']").val(),
            }
        }).done(function(data) {
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        }).fail(function( jqXHR, textStatus, errorThrown ) {
            alert(jqXHR+"Server might not handle That yet" + textStatus+" " + errorThrown);
        });
    });
    $('#btn-2').click(function(e){
        e.preventDefault();
        var form =$("form");
        var idsPhones=[],phones=[];
        var i =0;
        $(form).find("ul.phones input[name*='id']").each(function(){
            idsPhones[i++]=$(this).val();
        });
        i =0;
        $(form).find("ul.phones input[name*='phoneNumber']").each(function(){
            phones[i++]=$(this).val();
        });
        $.ajax({
            url : $(this).attr('href'),
            type: 'POST',
            data: {
                mainAddress: 	$(form).find("input[name='register_employer[person][mainAddress]']").val(),
                neighborhood: 	$(form).find("input[name='register_employer[person][neighborhood]']").val(),
                phonesIds:      idsPhones,
                phones:         phones,
                department: 	$(form).find("select[name='register_employer[person][department]']").val(),
                city: 			$(form).find("select[name='register_employer[person][city]']").val(),
            }
        }).done(function(data) {
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        }).fail(function( jqXHR, textStatus, errorThrown ) {
            alert(jqXHR+"Server might not handle That yet" + textStatus+" " + errorThrown);
        });
    });
    //funcion que agrega un listener a cada department
    addListeners();

    //colocar el select en el valor del tamaño del arreglo
    var $dropDownWork = $collectionHolder.data('index');
    $('#register_employer_numberOfWorkplaces').val($dropDownWork);
    //listener para el que agrega workplaces
    $('#register_employer_numberOfWorkplaces').change(function() {

        // get the numberof Workplaces that the user wants
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
                var $newFormLi = $('<li id="workSpace_'+ index +'" class="workSpaceLi"></li>').append(newForm);
                $collectionHolder.append($newFormLi);
            }
            //add the corresponding listeners
            addListeners();
        }
    });
    $("form").on("submit",function(e){
        e.preventDefault();
        var form =$("form");
        var addresses =[],citys=[],departments=[],ids=[];
        var i =0;
        $(form).find("ul.workplaces input[name*='id']").each(function(){
            ids[i++]=$(this).val();
        });
        i=0;
        $(form).find("ul.workplaces input[name*='mainAddress']").each(function(){
            addresses[i++]=$(this).val();
        });
        i=0;
        $(form).find("ul.workplaces select[name*='city']").each(function(){
            citys[i++]=$(this).val();
        });
        i=0;
        $(form).find("ul.workplaces select[name*='department']").each(function(){
            departments[i++]=$(this).val();
        });
        $.ajax({
            url : form.attr('action'),
            type: $(form).attr('method'),
            data: {
                sameWorkHouse: 	$(form).find("input[name='register_employer[sameWorkHouse]']:checked").val(),
                workId:         ids,
                workMainAddress:addresses,
                workCity:       citys,
                workDepartment: departments
            },
            statusCode:{
                200: function(data){
                    if(data["url"]!=null){
                        console.log(data["url"]);
                        sendAjax(data["url"]);
                    }else{
                        $('#main').replaceWith(
                            // ... with the returned one from the AJAX response.
                            $(data).find('#main'));
                        addClick();
                        if (!jsLoader(url)) {
                            addSumbit();
                        }
                    }
                },
                400 : function(data, textStatus, errorThrown){
                    alert("400 :"+errorThrown+"\n"+data.responseJSON.error.exception[0].message);
                    console.log(data);
                    console.log(textStatus);
                    console.log(errorThrown);
                }

            }
        });
    });
}


function jsonToHTML(data) {
    var htmls="<option value=''></option>";
    for(var i=0;i<data.length;i++){
        htmls+="<option value='"+data[i].id_city+"'>"+data[i].name+"</option>";
    }
    return htmls;
}
function addListeners() {
    $('select').filter(function() {
        return this.id.match(/department/);
    }).change(function() {
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
            data: { department: $department.val()}
        }).done(function(data){
            $('#'+citySelectId).html(
            // ... with the returned one from the AJAX response.
            jsonToHTML(data)
        );});

    });
    $("input[name='register_employer[sameWorkHouse]']").change(function(){
        var selected=$("input[name='register_employer[sameWorkHouse]']:checked").val();
        if(selected=="1"){
            var select= $("#register_employer_workplaces_0_id");
            if(select.val()==""){
                $("#register_employer_workplaces_0_mainAddress").val($("#register_employer_person_mainAddress").val());
                $("#register_employer_workplaces_0_department").val($("#register_employer_person_department").val());
                $("#register_employer_workplaces_0_city").val($("#register_employer_person_city").val());
            }
        }else{
            if($("#register_employer_workplaces_0_mainAddress").val()==$("#register_employer_person_mainAddress").val()){
                $("#register_employer_workplaces_0_id").val("");
                $("#register_employer_workplaces_0_mainAddress").val("");
                $("#register_employer_workplaces_0_department").val("");
                $("#register_employer_workplaces_0_city").val("");
            }
        }
    })
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
function addTagFormDeleteLink($tagFormLi) {
    var $removeFormA = $('<a href="#">delete this tag</a>');
    $tagFormLi.append($removeFormA);

    $removeFormA.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        // remove the li for the tag form
        $tagFormLi.remove();
    });
}