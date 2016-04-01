function addBeneficiary(){
    $('.submit').click(function (e) {
            e.preventDefault();
            var form = $("form");
            var idEmployee = $(form).find("input[name='idEmployee']");
            var documentType = $(form).find("input[name='register_beneficiary[documentType]']");
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
                alert("se guardo");
            }).fail(function (jqXHR, textStatus, errorThrown) {
                alert("hizo algo mal");
            });
        });
}