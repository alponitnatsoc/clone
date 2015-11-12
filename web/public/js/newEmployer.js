
/**
 * Created by gabrielsamoma on 11/11/15.
 */
function startEmployer(){
    var $collectionHolder;
    $collectionHolder = $('ul.workplaces');
    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', ($collectionHolder.find(':input').length)/3);
    //el cambio de tabs entre el formulario de registro
    $('.btnNext').click(function(){
        $('.nav-tabs > .active').next('li').find('a').trigger('click');
    });
    $('.btnPrevious').click(function(){
        $('.nav-tabs > .active').prev('li').find('a').trigger('click');
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
        var addresses =[],citys=[],departments=[];
        var i =0;
        $(form).find("ul.workplaces input").each(function(){
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
                youAre: 		$(form).find("input[name='register_employer[youAre]']").val(),
                documentType: 	$(form).find("select[name='register_employer[person][documentType]']").val(),
                document: 		$(form).find("input[name='register_employer[person][document]']").val(),
                names:			$(form).find("input[name='register_employer[person][names]']").val(),
                lastName1: 		$(form).find("input[name='register_employer[person][lastName1]']").val(),
                lastName2: 		$(form).find("input[name='register_employer[person][lastName2]']").val(),
                year: 			$(form).find("select[name='register_employer[person][birthDate][year]']").val(),
                month: 			$(form).find("select[name='register_employer[person][birthDate][month]']").val(),
                day: 			$(form).find("select[name='register_employer[person][birthDate][day]']").val(),
                mainAddress: 	$(form).find("input[name='register_employer[person][mainAddress]']").val(),
                neighborhood: 	$(form).find("input[name='register_employer[person][neighborhood]']").val(),
                phone: 			$(form).find("input[name='register_employer[person][phone]']").val(),
                department: 	$(form).find("select[name='register_employer[person][department]']").val(),
                city: 			$(form).find("select[name='register_employer[person][city]']").val(),
                workMainAddress:addresses,
                workCity:       citys,
                workDepartment: departments
            },
            statusCode:{
                200: function(data){
                    console.log(data);
                    sendAjax(data);
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
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            success: function(html) {
                // Replace current position field ...
                $('#'+citySelectId).replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html).find('#'+citySelectId)
                );
                // Position field now displays the appropriate positions.
            }
        });
    });
}