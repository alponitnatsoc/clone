/**
 * Created by gabrielsamoma on 3/16/16.
 */
function startDaviplata(){
    $("#step2").hide();
    $("#step3").hide();
    /*$("#form_create").on("click", function (e) {
        e.preventDefault();
        var win = window.open('https://goo.gl/njHWev', '_blank');
        if(win){
            //Browser has allowed it to be opened
            win.focus();
        }else{
            //Broswer has blocked it
            alert('Please allow popups for this site');
        }
        $("#step2").show();
        $("#step1").hide();
    });*/
    $("#btn-video").on("click", function (e) {
        e.preventDefault();
        $("#step2").show();
        $("#step1").hide();
        $("#default-buttons").hide();
        $(".activarCuenta").show();
    });
    $("#form_save").on("click", function (e) {
        e.preventDefault();
        var form =$("#formFields").parent();
        $.ajax({
            url:  $(form).attr('action'),
            type: $(form).attr('method'),
            data: $(form).serializeArray(),
            statusCode: {
                500: function () {
                    //$("#errorModal").modal("show");
                }
            }
        }).done(function (data) {
            window.location.href = "/dashboard/employer";
        });
    });
}