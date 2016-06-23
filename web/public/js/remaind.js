
/**
 * Created by Andres on 09/06/16.
 */
function startReminder() {
    // Get the modal
    var modal = document.getElementById('mimoda');

    // Get the button that opens the modal
    var btn = document.getElementById("mibotn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks the button, open the modal
    btn.onclick = function () {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function () {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    $.getScript("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("#reminderMail").validate({
            rules:{
                "emailToSendHelp" : { required: true, email: true, minlength:5}},
            messages:{
                "emailToSendHelp":{
                required: "Por favor digita tu email",
                email: "No es un E-mail valido",minlength: "El E-mail es muy corto."
            }}
        });
    });

    $(function () {
        $('.modal').modal({
            show: false,
            keyboard: false,
            backdrop: 'static'
        });
    });

    $("#emailToSendHelpButton").on("click", function (e) {

        e.preventDefault();

        if (!validator.element($("#emailToSendHelp"))) {
            return;
        }

        $.ajax({
            url: $("#emailToSendHelpButton").attr("href"),
            type: "POST",
            data: {
                email: $("#emailToSendHelp").val()
            }
        }).done(function (data) {
            modal.style.display = "none";
            $("#success_email_modal").modal("show");
            console.log("yay")
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $("#fail_email_modal").modal("show");
            console.log("nei")
        });
    });


}

