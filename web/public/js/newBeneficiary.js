
function addBeneficiary(){
    $('.editBeneficiary').click(function(e){
        e.preventDefault();
         $.ajax({
                url: $(this).attr('href'),
                type: 'GET',

            }).done(function (data) {                            
                var names = data['names'];
                var lastName1 = data['lastName1'];
                var lastName2 = data['lastName2'];
                var documentType = data['documentType'];
                var document = data['document'];
                var civilStatus = data['civilStatus'];
                var year = data['birthDate']['year'];
                var month = data['birthDate']['month'];
                var day = data['birthDate']['day'];
                var mainAddress = data['mainAddress'];
                var department = data['department'];
                var city = data['city'];
                var disability = data['disability'];
                var relation = data['relation'];
                var beneficiary = data['beneficiary'];

                $(form).find("input[name='register_beneficiary[names]']").val(names);
                $(form).find("input[name='register_beneficiary[lastName1]']").val(lastName1);
                $(form).find("input[name='register_beneficiary[lastName2]']").val(lastName2);
                $(form).find("select[name='register_beneficiary[documentType]']").val(documentType);
                $(form).find("input[name='register_beneficiary[document]']").val(document);
                $(form).find("select[name='register_beneficiary[civilStatus]']").val(civilStatus);
                $(form).find("select[name='register_beneficiary[birthDate][year]']").val(year);
                $(form).find("select[name='register_beneficiary[birthDate][month]']").val(month);
                $(form).find("select[name='register_beneficiary[birthDate][day]']").val(day);
                $(form).find("input[name='register_beneficiary[mainAddress]']").val(mainAddress);
                $(form).find("select[name='register_beneficiary[department]']").val(department);
                $(form).find("select[name='register_beneficiary[city]']").val(city);
                $("select[name='relation']").val(relation);
                $("input[name='idBeneficiary']").val(beneficiary);
                if (disability=0) {
                    $('input[name="disability"]').attr("checked",false);
                }else{
                    $('input[name="disability"]').attr("checked",true);
                }
            });
            showEditForm();        
    });
    $('.submit').click(function (e) {
            e.preventDefault();
            var form = $("form");
            var idEmployee = $(form).find("input[name='idEmployee']");
            var documentType = $(form).find("select[name='register_beneficiary[documentType]']");
            var document = $(form).find("input[name='register_beneficiary[document]']");
            var names = $(form).find("input[name='register_beneficiary[names]']");
            var lastName1 = $(form).find("input[name='register_beneficiary[lastName1]']");
            var lastName2 = $(form).find("input[name='register_beneficiary[lastName2]']");
            var year = $(form).find("select[name='register_beneficiary[birthDate][year]']");
            var month = $(form).find("select[name='register_beneficiary[birthDate][month]']");
            var day = $(form).find("select[name='register_beneficiary[birthDate][day]']");
            var relation = $("select[name='relation']");
            var cc = $('input[name="idCC"]');
            var eps = $('input[name="idEPS"]');
            var disability = $('input[name="disability"]:checked');
            var mainAddress = $(form).find("input[name='register_beneficiary[mainAddress]']");
            var department = $(form).find("select[name='register_beneficiary[department]']");
            var city = $(form).find("select[name='register_beneficiary[city]']");
            var civilStatus = $(form).find("select[name='register_beneficiary[civilStatus]']");
            if (!form.valid()) {
                return;
            }

            $.ajax({

                url: $(this).attr('href'),
                type: 'POST',
                data: {
                    documentType: documentType.val(),
                    document: document.val(),
                    names: names.val(),
                    lastName1: lastName1.val(),
                    lastName2: lastName2.val(),
                    year: year.val(),
                    month: month.val(),
                    day: day.val(),
                    mainAddress: mainAddress.val(),
                    department: department.val(),
                    city: city.val(),
                    civilStatus: civilStatus.val(),
                    mainAddress: mainAddress.val(),
                    idEmployee: idEmployee.val(), 
                    disability: disability.val(),
                    relation: relation.val(),
                    eps: eps.val(),
                    cc: cc.val(),

                }
            }).done(function (data) {                  
                clearForm();
                hideForm();                
                alert("funciono");
            }).fail(function (jqXHR, textStatus, errorThrown) {
                alert("hizo algo mal");
            });
        });
        $('.btn-edit').click(function (e) {
            e.preventDefault();
            var form = $("form");
            var idEmployee = $(form).find("input[name='idEmployee']");
            var documentType = $(form).find("select[name='register_beneficiary[documentType]']");
            var document = $(form).find("input[name='register_beneficiary[document]']");
            var names = $(form).find("input[name='register_beneficiary[names]']");
            var lastName1 = $(form).find("input[name='register_beneficiary[lastName1]']");
            var lastName2 = $(form).find("input[name='register_beneficiary[lastName2]']");
            var year = $(form).find("select[name='register_beneficiary[birthDate][year]']");
            var month = $(form).find("select[name='register_beneficiary[birthDate][month]']");
            var day = $(form).find("select[name='register_beneficiary[birthDate][day]']");
            var relation = $("select[name='relation']");
            var cc = $('input[name="idCC"]');
            var eps = $('input[name="idEPS"]');
            var disability = $('input[name="disability"]:checked');
            var mainAddress = $(form).find("input[name='register_beneficiary[mainAddress]']");
            var department = $(form).find("select[name='register_beneficiary[department]']");
            var city = $(form).find("select[name='register_beneficiary[city]']");
            var civilStatus = $(form).find("select[name='register_beneficiary[civilStatus]']");
            var beneficiary = $('input[name="idBeneficiary"]');

            if (!form.valid()) {
                return;
            }

            $.ajax({

                url: $(this).attr('href'),
                type: 'POST',
                data: {
                    documentType: documentType.val(),
                    document: document.val(),
                    names: names.val(),
                    lastName1: lastName1.val(),
                    lastName2: lastName2.val(),
                    year: year.val(),
                    month: month.val(),
                    day: day.val(),
                    mainAddress: mainAddress.val(),
                    department: department.val(),
                    city: city.val(),
                    civilStatus: civilStatus.val(),
                    mainAddress: mainAddress.val(),
                    idEmployee: idEmployee.val(), 
                    disability: disability.val(),
                    relation: relation.val(),
                    beneficiary: beneficiary.val()
                }
            }).done(function (data) {                  
                clearForm();
                hideForm();                
                alert("funciono");
            }).fail(function (jqXHR, textStatus, errorThrown) {
                alert("hizo algo mal");
            });
        });
}

function clearForm(){
    $('input[name="disability"]:checked').attr("checked",false);
    //document.getElementById("benefType").reset();
    document.getElementById("form").reset();
}
function showEditForm(){
    $("#form-beneficiary").fadeIn("slow");
    document.getElementById("btn-edit").style.display = 'block';
    document.getElementById("btn-create").style.display = 'none';
}   
function showCreateForm() {
    clearForm();
    $("#form-beneficiary").fadeIn("slow");
    document.getElementById("btn-create").style.display = 'block';
    document.getElementById("btn-edit").style.display = 'none';
}
function hideForm(){
    $("#form-beneficiary").fadeOut("slow");
}
function getPersonInfo(){

}


