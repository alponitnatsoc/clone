/**
 * Created by gabrielsamoma on 2/15/16.
 */
function startMyAccount(){
    hideAccountStuff();
    addListeners();
}
function hideAccountStuff(){
    $("#form_save").hide();
    $("#form_email").hide();
    $("#dataSuccessEmail").hide();
    $("#dataFailEmail").hide();

}
function addListeners(){
    $("#form_modify").on("click", function(e){
        e.preventDefault();
        $("#form_save").show();
        $("#form_email").show();
        $("#emailValue").hide();
        $("#dataSuccessEmail").hide();
        $("#dataFailEmail").hide();
        $(this).hide();
    });
    //TODO BORRADA EXITOSAMENTE
    $(".DeleteCC").each(function () {
        $(this).on("click", function (e) {
            e.preventDefault();

            $.ajax({
                url: $(this).attr("href"),
                type: "GET",
            }).done(function (data) {
                alert("tarjeta borrada exitosamente");
                $(this).parent().parent().remove();
            }).fail(function (jqXHR, textStatus, errorThrown) {
                alert("No se pudo Borrar la tarjeta");
            });
        })
    });
    $("#form_save").on("click", function(e){
        e.preventDefault();
        $.ajax({
            url:  $(form).attr( 'action' ),
            type: $(form).attr('method'),
            data: $(form).serializeArray(),
            statusCode: {
                500: function () {
                    alert("Lo sentimos pero estamos teniendo problemas con esta funcionalidad, por favor intenta de nuevo más tarde.");
                }
            }
        }).done(function (data) {
            $("#form_modify").show();
            $("#form_save").hide();
            if($("#form_email").val()!=$("#emailValue").html()){
                $("#dataSuccessEmail").show();
            }
            $("#emailValue").html($("#form_email").val());
            $("#emailValue").show();
            $("#form_email").hide();
            $(this).hide();
        }).fail(function(){
            $("#dataFailEmail").show();
        });

    });

}
