/**
 * Created by gabrielsamoma on 3/13/16.
 */
function hideInfo() {
    $(".bottom-box").fadeOut('slow');

}
function showEmployeeForm() {
    $(".bottom-box-edit").attr("hidden", false);
    $(".bottom-box-edit").fadeOut('slow');
    $(".bottom-box-edit").fadeIn('slow');

}
function editEmployee() {
    $('.editEmployee').on("click", function (e) {
        hideInfo();
        showEmployeeForm();
        $(".editEmployee").hide();
        e.preventDefault();

        $.ajax({
            url: $(this).attr('href'),
            type: 'GET',
        }).done(function (data) {
            var civilStatus = data['civilStatus'];
            var year = data['birthDate']['year'];
            var month = data['birthDate']['month'];
            var day = data['birthDate']['day'];
            var birthCountry = data['birthCountry'];
            var birthDepartment = data['birthDepartment'];
            var birthCity = data['birthCity'];
            var mainAddress = data['mainAddress'];
            var department = data['department'];
            var city = data['city'];
            var phone = data['phone'];
            var email = data['email'];

            $("select[name='form[civilStatus]']").val(civilStatus);
            $("select[name='form[birthDate][year]']").val(year);
            $("select[name='form[birthDate][month]']").val(month);
            $("select[name='form[birthDate][day]']").val(day);
            $("input[name='form[mainAddress]']").val(mainAddress);
            $("input[name='form[phone]']").val(phone);
            $("input[name='form[email]']").val(email);
            $("select[name='form[department]']").val(department);
            $("select[name='form[city]']").val(city);
            $("select[name='form[birthDepartment]']").val(birthDepartment);
            $("select[name='form[birthCity]']").val(birthCity);
            $("select[name='form[birthCountry]']").val(birthCountry);
        });

    });
    $('.edit-employee-save').click(function (e) {
        e.preventDefault();
        var civilStatus = $("select[name='form[civilStatus]']");
        var year = $("select[name='form[birthDate][year]']");
        var month = $("select[name='form[birthDate][month]']");
        var day = $("select[name='form[birthDate][day]']");
        var mainAddress = $("input[name='form[mainAddress]']");
        var phone = $("input[name='form[phone]']");
        var email = $("input[name='form[email]']");
        var department = $("select[name='form[department]']");
        var city = $("select[name='form[city]']");
        var birthDepartment = $("select[name='form[birthDepartment]']");
        var birthCity = $("select[name='form[birthCity]']");
        var birthCountry = $("select[name='form[birthCountry]']");
        var idEmployee = $(form).find("input[name='idEmployee']");

        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                civilStatus: civilStatus.val(),
                year: year.val(),
                month: month.val(),
                day: day.val(),
                mainAddress: mainAddress.val(),
                phone: phone.val(),
                email: email.val(),
                department: department.val(),
                city: city.val(),
                birthCountry: birthCountry.val(),
                birthDepartment: department.val(),
                birthCity: city.val(),
                idEmployee: idEmployee.val(),
            }
        }).done(function (data) {
            location.reload();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert("hizo algo mal");
        });
    });
    $(".edit-contract").on("click", function (e) {
        e.preventDefault();
        $(".contract-info").fadeOut("slow");
        $(".contract-edit-form").fadeIn("slow");
        $(this).fadeOut("slow");
        $(".save-contract").show();

    });

    $(".save-contract").on("click", function (e) {
        e.preventDefault();
        if ($('#form_contract_workplace').val() == "") {
            alert("Seleccione un lugar de trabajo");
            return false;
        }
        if ($('#form_contract_method_type').val() == "") {
            alert("Seleccione un metodo de pago");
            return false;
        }
        if ($('#form_contract_salary').val() == "") {
            alert("Ingrese un salario");
            return false;
        }
        $.ajax({
            url: $(this).attr('href'),
            type: 'POST',
            data: {
                payTypeId: $("#form_contract_method_type").val(),
                bankId: $("#method_type_fields_bankBank").val(),
                accountTypeId: $("#method_type_fields_accountTypeAccountType").val(),
                accountNumber: $("#method_type_fields_accountNumber").val(),
                cellphone: $("#method_type_fields_cellphone").val(),
                hasIt: $("#method_type_fields_hasIt").val(),
                contractId: $("#contract-id").val(),
                form_contract_salary: $('#form_contract_salary').val(),
                form_contract_workplace: $('#form_contract_workplace').val(),
                form_contract_method_type: $('#form_contract_method_type').val(),
            }
        }).done(function (data) {
            console.log(data);
            $(".paymethod-fields").html("");
            $(".contract-info").fadeIn("slow");
            $(".contract-edit-form").fadeOut("slow");
            $(".save-contract").hide();
            $(".edit-contract").show();
            //location.reload();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        });
    });

    $("#form_contract_method_type").on("change", function () {


        var payMethod = $("select[name='contract[method_type]']");
        $.ajax({
            url: '/pay/method/fields/' + payMethod.val(),
            type: 'GET'
        }).done(function (data) {

            var formFields = $(data).find("#formFields");
            $(".paymethod-fields").html("");
            $(".paymethod-fields").append(formFields);
            $(".paymethod-fields").find("input[type=text]").addClass('form-black');
            // var $putFields = $('#putFields_' + payMethod.val()).html(
            //     // ... with the returned one from the AJAX response.
            //     $(data).find('#formFields'));
            // $('#putFields_' + payTypeChecked.val()).html("");
            // if ($(payMethod).parent().text() == " Daviplata") {
            //     payMethodListener();
            // }
            // payTypeChecked = payMethod;

        });
    });
}


function manageEmployees() {
    $(".addNovelty").each(function () {
        $(this).on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");
            loadNovelty(href);
        });
    });
    $(".removeEmployee").on("click", function (e) {
        e.preventDefault();
        $("#deleteModal").modal("show");
        $("#btn-erase").attr("href", $(this).attr("href"));
    });
    $("#btn-erase").on("click", function (e) {
        $(this).text("Borrando...");
    })

    $("#terminar_contrato_button").on("click", function (e){
        $('#terminacion_contrato_modal').modal('show');
    });

    $("#solicitar_llamada").on("click", function (e){
        var ehe = $("#ehe-id").val();
        var urlL = "/employer/liquidate/" + ehe + "/mail/";
        e.preventDefault();
        $.ajax({
            url: urlL,
            type: 'POST',
            data: {
            }
        }).done(function (data) {
            alert("Se ha enviado la solicitud, pronto serás contactado de vuelta");
            window.location.reload();

        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert("La solicitud no pudo ser generada, intentalo de nuevo más tarde");
        });
    });
}
function loadNovelty(url) {
    $.ajax({
        url: url,
        type: 'POST',
        data: {
        }
    }).done(function (data) {
        $("#agregarNovedad").html(data);
        $.getScript("/public/js/novelty.js").done(function () {
            startNovelty();
        });

        //         $('#noveltyModal').modal('show');

        $('#noveltyModal').modal({
            show: false,
            keyboard: false,
            backdrop: 'static'
        });
        $('#noveltyModal').on('hidden.bs.modal', function () {
            window.location.reload();
        })
        $('#noveltyModal').modal('show');

    }).fail(function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
    });
}
