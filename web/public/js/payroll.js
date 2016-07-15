$(document).ready(function () {
    $(".addNovelty").each(function () {
        $(this).on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");
            loadNovelty(href);
        });
    });

    $(".workedDays").each(function () {
        $(this).on("click", function (e) {
            e.preventDefault();
            var href = $(this).attr("href");
            loadWorkedDays(href);
        });
    });
    $(".showDetails").each(function (){
        $(this).on("click", function (e){
            e.preventDefault();
            var href = $(this).attr("href");
            loadShowDetails(href);
        });
    });

    $('.employee .pay').on('change', function (e) {
        var i = 0;
        $('.employee .pay').each(function (index, element) {
            if ($(element)[0].checked) {
                i++;
            }
        });
        $('#btnCalculate').prop('disabled', i == 0);
    });
    $("#btnCalculate").on("click", function () {
        var arrayToPay=[];
        $(".toPayArray").each(function () {
            arrayToPay.push($(this).val());
        })
    });
    $('.btn-add-novelty').on('click', function (event) {
        event.preventDefault();
        //button = $(event.relatedTarget);
        //employeeName = button.data('data-employee-name');
        employeeName = $(this).attr('data-employee-name');
        url = $(this).attr('href');
        $.ajax({
            url: url,
            //data: data,
            //success: success,
            //dataType: dataType,
            beforeSend: function (xhr) {
                $('#modal_loader').modal('show');
            }
        }).done(function (data) {
            $('#modal_add_novelty_test').html(data);
            $.getScript("/public/js/novelty.js").done(function () {
                startNovelty();
            });

            //$('#modal_body_add_novelty').replaceWith($(data).find('#main')); // ... with the returned one from the AJAX response.
            //$('#modal-title_add_novelty').html("Reporte aquí cada hecho que esté asociado a " + employeeName + " para que sea considerado en el cálculo de su pago.");

            $('#noveltyModal').modal({
                show: false,
                keyboard: false,
                backdrop: 'static'
            });
            $('#noveltyModal').on('hidden.bs.modal', function () {
                window.location.reload();
            })
            $('#noveltyModal').modal('show');

            //$('.result_ajax').html(name + " fue " + state + " exitosamente.").show(1000);
            //setTimeout(function () {
            //    $('.result_ajax').html("").hide(1000);
            //}, 2000);
        }).fail(function (data) {
            //$("#modal_body_add_novelty").html(data);
        }).always(function () {
            $('#modal_loader').modal('hide');
        });
        //document.location = "/novelty/select/" + payroll;
    });

    $('.view_detail').on('click', function (event) {
        event.preventDefault();
        url = $(this).attr('href');
        employeeName = $(this).attr('data-employee-name');
        $.ajax({
            url: url,
            //data: data,
            //success: success,
            //dataType: dataType,
            beforeSend: function (xhr) {
                $('#modal_loader').modal('show');
            }
        }).done(function (data) {
            //$('#modal_body_add_novelty').html($(data).find('#main'));
            $('#modal_body_payroll_detail').html($(data).find('#main')); // ... with the returned one from the AJAX response.
            $('#modal-title_payroll_detail').html("Detalle de la liquidación: " + employeeName);
            $('#modal_payroll_detail').modal('show');

            //$('.result_ajax').html(name + " fue " + state + " exitosamente.").show(1000);
            //setTimeout(function () {
            //    $('.result_ajax').html("").hide(1000);
            //}, 2000);
        }).fail(function (data) {
            //$("#modal_body_add_novelty").html(data);
        }).always(function () {
            $('#modal_loader').modal('hide');
        });
        //document.location = "/novelty/select/" + payroll;
    });

    $('.view_detail').on('click', function (event) {

    });

    $("#btnShowDetails").on('click',function (event) {
        $("#modal_show_details").modal('show');
    });

});
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
function loadShowDetails(url){

    $.ajax({
        url: url,
        type: 'POST',
        data: {
        }
    }).done(function (data) {
        $("#SQLNovelties").html(data);
        $('#modal_show_SQLNovelties_details').modal({
            show: false,
            keyboard: false,
            backdrop: 'static'
        });

        $('#modal_show_SQLNovelties_details').on('hidden.bs.modal', function () {
            window.location.reload();
        })
        $('#modal_show_SQLNovelties_details').modal('show');

    }).fail(function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
    });
}

function loadWorkedDays(url) {
    $.ajax({
        url: url,
        type: 'POST',
        data: {
        }
    }).done(function (data) {
        $("#cambiarDias").html(data);
        $.getScript("/public/js/changeDaysWorked.js").done(function () {
            changeDays();
        });

        $('#changeDaysModal').modal({
            show: false,
            backdrop: 'static'
        });
        $('#changeDaysModal').on('hidden.bs.modal', function () {
            window.location.reload();
        })
        $('#changeDaysModal').modal('show');

    }).fail(function (jqXHR, textStatus, errorThrown) {
        alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
    });
}
