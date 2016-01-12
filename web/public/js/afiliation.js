/**
 * Created by gabrielsamoma on 1/12/16.
 */
function startAfiliation(){
    $('.btnPrevious').click(function(){
        $('.nav-tabs > .active').prev('li').find('a').trigger('click');
    });
    $('#btn-1').click(function(e){
        e.preventDefault();
        var form =$("form");
        var idEmployees=[],beneficiaries=[],pension=[],wealth=[];
        var i =0;
        $(form).find("input[name*='[idEmployerHasEmployee]']").each(function(){
            idEmployees[i++]=$(this).val();
        });
        i =0;
        $(form).find("select[name*='[wealth]']").each(function(){
            wealth[i++]=$(this).val();
        });
        i =0;
        $(form).find("select[name*='[pension]']").each(function(){
            pension[i++]=$(this).val();
        });
        i =0;
        $(form).find("input[name*='[beneficiaries]']:checked").each(function(){
            beneficiaries[i++]=$(this).val();
        });

        $.ajax({
            url : $(this).attr('href'),
            type: 'POST',
            data: {
                idEmployerHasEmployee:	idEmployees,
                beneficiaries: 			beneficiaries,
                pension:				pension,
                wealth: 				wealth,
                idEmployer: 			$(form).find("input[name='register_social_security[idEmployer]']").val(),
            }
        }).done(function(data) {
            console.log(data);
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        }).fail(function( jqXHR, textStatus, errorThrown ) {
            alert(jqXHR+"Server might not handle That yet" + textStatus+" " + errorThrown);
            console.log(jqXHR);
        });
    });
    $('#btn-2').click(function(e){
        e.preventDefault();
        var form =$("form");

        $.ajax({
            url : $(this).attr('href'),
            type: 'POST',
            data: {
                idEmployer: 			$(form).find("input[name='register_social_security[idEmployer]']").val(),
                severances: 			$(form).find("select[name='register_social_security[severances]']").val(),
                arl: 					$(form).find("select[name='register_social_security[arl]']").val(),
                economicalActivity: 	$(form).find("input[name='register_social_security[economicalActivity]']").val(),
            }
        }).done(function(data) {
            console.log(data);
            $('.nav-tabs > .active').next('li').find('a').trigger('click');
        }).fail(function( jqXHR, textStatus, errorThrown ) {
            alert(jqXHR+"Server might not handle That yet" + textStatus+" " + errorThrown);
            console.log(jqXHR);
        });
    });
}