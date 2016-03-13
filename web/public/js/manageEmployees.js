/**
 * Created by gabrielsamoma on 3/13/16.
 */
function manageEmployees(){
    $(".addNovelty").each(function(){
        $(this).on("click", function(e){
            e.preventDefault();
            var href=$(this).attr("href");
            loadNovelty(href);
        });
    })
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