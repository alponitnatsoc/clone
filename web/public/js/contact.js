
/**
 * Created by Andres on 09/06/16.
 */
function startContact() {
    var validator;
    
    $.getScript("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("form[name='contact']").validate({
            rules: {
                "contact[name]": "required",
                "contact[email]": {required:true, email:true, minlength:5 },
                "contact[phone]": {required:true, number:true, minlength:7 },
                "contact[subject]":"required",
                "contact[message]":"required"
            },
            messages: {
                "contact[name]": "Por favor digita tu nombre",
                "contact[email]": {minlength: "El E-mail es muy corto.", required: "Por favor digita tu email", email: "No es un E-mail valido" },
                "contact[phone]": {minlength: "El numero digitado es muy corto, debe tener al menos 7 digitos.", required: "Por favor Digita un numero de contacto", number: "Este campo solo admite numeros" },
                "contact[message]":"Por favor escribe el mensaje"
            }
        });
    });
    
    $("form").on("submit", function (e) {
        var form = $("form");
        var flagValid = true;

        $(form).find("input[name*='contact[name]']").not("[type='hidden']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;

            }
        });
        $(form).find("select[name*='contact[email]']").not("[type='hidden']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
            }
        });
        $(form).find("select[name*='contact[phone]']").not("[type='hidden']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
            }
        });

        $(form).find("select[name*='contact[message]']").not("[type='hidden']").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
            }
        });
        if(!flagValid){
            e.preventDefault();
            return;
        }
        
        $.ajax({
            url: form.attr('action'),
            type: $(form).attr('method'),
            data: {
                name: $(form).find("input[name='contact[name]']:checked").val(),
                email: $(form).find("input[name='contact[email]']:checked").val(),
                phone: $(form).find("input[name='contact[phone]']:checked").val(),
                subject: $(form).find("input[name='contact[subject]']:checked").val(),
                message: $(form).find("input[name='contact[message]']:checked").val()
            }
        }).done(function (data) {
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if(jqXHR==errorHandleTry(jqXHR)){
                alert(jqXHR + "Server might not handle That yet" + textStatus + " " + errorThrown);
            }
        });

        
    });

}

