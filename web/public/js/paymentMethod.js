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

    });
}

