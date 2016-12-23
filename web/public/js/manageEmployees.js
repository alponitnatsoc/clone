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


    $("#changePaymentMethod_payMethod").on('change',function (e) {
        if($("#changePaymentMethod_payMethod").val()!=null && $("#changePaymentMethod_payMethod").val()!=''){
            $("#error_payment_change").hide();
            var validator;
            $.getScript("//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
                validator = $("form[name='changePaymentMethod']").validate({
                    rules: {
                        "method_type_fields[accountTypeAccountType]": {
                            required:true},
                        "method_type_fields[accountNumber]": {
                            required:true,
                            number:true},
                        "method_type_fields[bankBank]": {
                            required:true},
                        "method_type_fields[cellphone]":{
                            required:true,
                            number: true,
                            minlength: 10,
                            maxlength:10}
                    },
                    messages: {
                        "method_type_fields[accountTypeAccountType]": {
                            required:'Por favor selecciona una opción'},
                        "method_type_fields[accountNumber]": {
                            required:'El número de cuenta es obligatorio',
                            number:'Este campo solo admite valores numéricos'},
                        "method_type_fields[bankBank]": {
                            required:'Por favor selecciona una opción'},
                        "method_type_fields[cellphone]":{
                            required:'Este campo es obligatorio',
                            number: "Este campo solo admite valores numéricos",
                            minlength: "El número celular ingresado es muy corto",
                            maxlength:"El número celular ingresado es muy largo"}
                    }
                });
            });
            $("#submit_change_pay_method").show();
            $.ajax({
                url: '/pay/method/fields/' + $("#changePaymentMethod_payMethod").val() + '/' + $("input[id='id_contract']").val(),
                type: 'GET'
            }).done(function (data) {
                $('#change_pay_method_content').html(
                    // ... with the returned one from the AJAX response.
                    $(data).find('#formFields'));
                $("#method_type_fields_hasIt").hide();
                $("label[for='method_type_fields_hasIt']").hide();
            });
        }else{
            $("#submit_change_pay_method").hide();
            $('#change_pay_method_content').html("");
        }
    });

    $("form[name=changePaymentMethod]").on('submit',function (e) {
        e.preventDefault();
        if($("form[name=changePaymentMethod]").valid()){
            $.ajax({
                url: "/api/public/v1/changes/payments/methods",
                type: 'POST',
                data: {
                    payTypeId: $("#changePaymentMethod_payMethod").val(),
                    cellphone: $("#method_type_fields_cellphone").val(),
                    hasIt: true,
                    accountTypeId: $("#method_type_fields_accountTypeAccountType").val(),
                    accountNumber: $("#method_type_fields_accountNumber").val(),
                    bankId: $("#method_type_fields_bankBank").val(),
                    contractId: $("#id_contract").val(),
                }
            }).done(function (data) {
                console.log(data);
                $("#close_change_payment_method").click();
                $("#error_payment_change").hide();
                // location.reload();
            }).fail(function (jqXHR, textStatus, errorThrown) {
                $("#error_payment_change").show();
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            });
        }
        //
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

function disableLockedFields(){
    $("#form_birthDate_day").prop("disabled", true);
    $("#form_birthDate_month").prop("disabled", true);
    $("#form_birthDate_year").prop("disabled", true);
    $("#form_birthCountry").prop("disabled", true);
    $("#form_birthDepartment").prop("disabled", true);
    $("#form_birthCity").prop("disabled", true);
}
