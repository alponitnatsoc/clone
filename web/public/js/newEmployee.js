
/**
 * Created by gabrielsamoma on 11/11/15.
 */
function startEmployee(){

    $('.btnNext').click(function(){
        $('.nav-tabs > .active').next('li').find('a').trigger('click');
    });
    $('.btnPrevious').click(function(){
        $('.nav-tabs > .active').prev('li').find('a').trigger('click');
    });

    //funcion que agrega un listener a cada department
    addListeners();

    $('#register_employee_employeeHasEmployers_payMethod').on('change', function(){
        var payMethod = $(this);
        $.ajax({
            url : '/pay/method/fields/'+payMethod.val(),
            type: 'GET'
        }).done(function(data) {
            $('#payMethodFields').html(
                // ... with the returned one from the AJAX response.
                $(data).find('#formFields'));
        });
    });

    $("form").on("submit",function(e){
        e.preventDefault();
        var form =$("form");
        var idsBenef=[],idsWorkpl=[];
        var i =0;
        $(form).find("ul.benefits select[name*='benefits']").each(function(){
            idsBenef[i++]=$(this).val();
        });
        i=0;
        $(form).find("ul.workplaces select[name*='workplaces']").each(function(){
            idsWorkpl[i++]=$(this).val();
        });

        $.ajax({
            url : form.attr('action'),
            type: $(form).attr('method'),
            data: {
                documentType: 	$(form).find("select[name='register_employee[person][documentType]']").val(),
                document: 		$(form).find("input[name='register_employee[person][document]']").val(),
                names:			$(form).find("input[name='register_employee[person][names]']").val(),
                lastName1: 		$(form).find("input[name='register_employee[person][lastName1]']").val(),
                lastName2: 		$(form).find("input[name='register_employee[person][lastName2]']").val(),
                civilStatus:    $(form).find("select[name='register_employee[personExtra][civilStatus]']").val(),
                year: 			$(form).find("select[name='register_employee[person][birthDate][year]']").val(),
                month: 			$(form).find("select[name='register_employee[person][birthDate][month]']").val(),
                day: 			$(form).find("select[name='register_employee[person][birthDate][day]']").val(),
                birthCountry: 	$(form).find("select[name='register_employee[personExtra][birthCountry]']").val(),
                birthDepartment:$(form).find("select[name='register_employee[personExtra][birthDepartment]']").val(),
                birthCity: 		$(form).find("select[name='register_employee[personExtra][birthCity]']").val(),

                mainAddress: 	$(form).find("input[name='register_employee[person][mainAddress]']").val(),
                neighborhood: 	$(form).find("input[name='register_employee[person][neighborhood]']").val(),
                phone: 			$(form).find("input[name='register_employee[person][phone]']").val(),
                department: 	$(form).find("select[name='register_employee[person][department]']").val(),
                city: 			$(form).find("select[name='register_employee[person][city]']").val(),
                email:          $(form).find("input[name='register_employee[personExtra][email]']").val(),
                employeeId:     $(form).find("input[name='register_employee[idEmployee]']").val(),

                employeeType:   $(form).find("select[name='register_employee[employeeHasEmployers][employeeType]']").val(),
                contractType:   $(form).find("select[name='register_employee[employeeHasEmployers][contractType]']").val(),
                timeCommitment: $(form).find("select[name='register_employee[employeeHasEmployers][timeCommitment]']").val(),
                position:       $(form).find("select[name='register_employee[employeeHasEmployers][position]']").val(),
                salary:         $(form).find("input[name='register_employee[employeeHasEmployers][salary]']").val(),
                idsBenefits:    idsBenef,
                idsWorkplaces:  idsWorkpl,


                payTypeId:      $(form).find("select[name='register_employee[employeeHasEmployers][payMethod]']").val(),
                bankId:         $(form).find("select[name='method_type_fields[Bank]']").val(),
                accountTypeId:  $(form).find("select[name='method_type_fields[AccountType]']").val(),
                frequency:      $(form).find("input[name='method_type_fields[frecuency]']").val(),
                accountNumber:  $(form).find("input[name='method_type_fields[account_number]']").val(),
                cellphone:      $(form).find("input[name='method_type_fields[cellphone]']").val(),
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

    var $collectionHolderB;
    var $collectionHolderW;
    $("#toHide").children().hide();
    var $addBenefitLink = $('<a href="#" class="add_benefit_link">Add benefit</a>');
    var $newLinkLi = $('<li></li>').append($addBenefitLink);
    var $addWorkplaceLink = $('<a href="#" class="add_workplace_link">Add workplace</a>');
    var $newLinkLink = $('<li></li>').append($addWorkplaceLink);
    // Get the ul that holds the collection of benefits
    $collectionHolderB = $('ul.benefits');
    $collectionHolderW = $('ul.workplaces');
    //add remove links
    $collectionHolderB.find('li').each(function() {
        addTagFormDeleteLink($(this));
    });
    $collectionHolderW.find('li').each(function() {
        addTagFormDeleteLink($(this));
    });
    // add the "add a tag" anchor and li to the tags ul
    $collectionHolderB.append($newLinkLi);
    $collectionHolderW.append($newLinkLink);

    // count the current form inputs we have (e.g. 2), use that as the new
    $collectionHolderB.data('index', $collectionHolderB.find(':input').length);
    $collectionHolderW.data('index', $collectionHolderW.find(':input').length);
    $addBenefitLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        addBenefitForm($collectionHolderB, $newLinkLi);
    });
    $addWorkplaceLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();
        addWorkplaceForm($collectionHolderW, $newLinkLink);
    });


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
function addWorkplaceForm($collectionHolderB, $newLinkLink) {
    var prototype = $collectionHolderB.data('prototype');
    var index = $collectionHolderB.data('index');
    var newForm = prototype.replace(/__name__/g, index);
    $collectionHolderB.data('index', index + 1);
    var $newFormLi = $('<li></li>').append(newForm);
    addTagFormDeleteLink($newFormLi);
    $newLinkLink.before($newFormLi);
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
}