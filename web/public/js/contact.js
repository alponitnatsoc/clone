
/**
 * Created by Andres on 09/06/16.
 */
function startContact() {
    var validator;
    
    $.getScript("http://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
        validator = $("form[name='contact']").validate({
            rules: {
                "contact[name]": "required",
                "contact[email]":"required",
                "contact[subject]":"required",
                "contact[message]":"required"
            },
            messages: {
                "contact[name]": "Por favor digita tu nombre",
                "contact[email]":"Por favor digita tu email",
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

