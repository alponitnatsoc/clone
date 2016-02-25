/**
 * Created by gabrielsamoma on 2/2/16.
 */
function startPayment(){
    $.getScript( "/public/js/jquery.creditCardValidator.js").done(function(){

            $('input').validateCreditCard(function(result) {
                $('.log').html('Card type: ' + (result.card_type == null ? '-' : result.card_type.name)
                    + '<br>Valid: ' + result.valid
                    + '<br>Length valid: ' + result.length_valid
                    + '<br>Luhn valid: ' + result.luhn_valid);

            });
        addListenersPayM();
    });
}
function addListenersPayM(){
    $("#form_save").on("click", function (e) {
        e.preventDefault();
        var url="/api/public/v1/adds/credits/cards";
        $.ajax({
            url: url,
            type: "POST",
            data:{
                credit_card:$("#form_credit_card").val(),
                expiry_date_year:$("#form_expiry_date_year").val(),
                expiry_date_month:$("#form_expiry_date_month").val(),
                cvv:$("#form_cvv").val(),
                name_on_card:$("#form_name_on_card").val()
            }
        }).done(function (data) {
            alert("tarjeta agregada exitosamente");
        }).fail(function (jqXHR, textStatus, errorThrown) {
            alert("No se pudo agregar la tarjeta");
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        });

    });
}

