/**
 * Created by gabrielsamoma on 2/8/16.
 */
function addNoveltySelectListener() {
    var modBody=$("#noveltyModal").find(".cuerpoNovelty");
    var form=modBody.find("form");
    if($("#novelty_fields_noveltyType").find("option:selected").text()=="Vacaciones"){
        $("#novelty_fields_date_start").on("change", function(){
            var $dateStart=$(this);
            var $dateEnd=$("#novelty_fields_date_end");
            if(checkDates($dateStart)&&checkDates($dateEnd)){
                if(!($("#daysAmountText").length)){
                    var p = "<p id='daysAmountText'>Número de días a tomar entre las fechas escogidas: <div id='daysAmount'></div> </p>";
                    $("#novelty_fields").append(p);
                }
                $.ajax( {
                    type: "GET",
                    url: "/api/public/v1/valids/"+$dateStart.find("select[name*='year']").val()+"-"
                    +$dateStart.find("select[name*='month']").val()+"-"+$dateStart.find("select[name*='day']").val()+
                    "/vacations/"+$dateEnd.find("select[name*='year']").val()+"-"
                    +$dateEnd.find("select[name*='month']").val()+"-"+$dateEnd.find("select[name*='day']").val()+
                    "/days/-1/contracts/"+$(form).find("#novelty_fields_idPayroll").val(),
                }).done(function(data){
                    $("#daysAmount").html(data["days"]);
                }).fail(function(x,y,z){

                });
            }
        });
        $("#novelty_fields_date_end").on("change", function(){
            var $dateEnd=$(this);
            var $dateStart=$("#novelty_fields_date_start");
            if(checkDates($dateStart)&&checkDates($dateEnd)){
                if(!($("#daysAmountText").length)){
                    var p = "<p id='daysAmountText'>Número de días a tomar entre las fechas escogidas: <div id='daysAmount'></div> </p>";
                    $("#novelty_fields").append(p);
                }
                $.ajax( {
                    type: "GET",
                    url: "/api/public/v1/valids/"+$dateStart.find("select[name*='year']").val()+"-"
                    +$dateStart.find("select[name*='month']").val()+"-"+$dateStart.find("select[name*='day']").val()+
                    "/vacations/"+$dateEnd.find("select[name*='year']").val()+"-"
                    +$dateEnd.find("select[name*='month']").val()+"-"+$dateEnd.find("select[name*='day']").val()+
                    "/days/-1/contracts/"+$(form).find("#novelty_fields_idPayroll").val(),
                }).done(function(data){
                    $("#daysAmount").html(data["days"]);
                }).fail(function(x,y,z){

                });
            }
        });
    }
    $("#novelty_fields_date_start").on("change", function(){

    });
    $("#novelty_fields_date_end").on("change", function(){

    });
    form.submit(function (e) {
        e.preventDefault();
        var value=form.find("input[name='form[noveltyType]']:checked").val();
        if($("#novelty_fields_noveltyType").val() !=null){
            value="";
        }
        $.ajax( {
            type: "POST",
            url: form.attr( 'action' )+value,
            data: form.serialize()
        }).done(function(data){
            var innerForm=$(data).find("#formForm")
            if(innerForm.find("form").length==0){
                modBody.html(
                    "<p><h3>Novedad Creada Exitosamente</h3></p>"
                );
            }else{
                var error=$(data).find("#error");
                if(error.length!=0){
                    alert(error.html());
                }
                modBody.html(
                    innerForm
                );
                addNoveltySelectListener();
            }
        });
    });
}
function checkDates(date){
    var year=$(date).find("select[name*='year']");
    var month=$(date).find("select[name*='month']");
    var day=$(date).find("select[name*='day']");
    if(year.val()==""||month.val()==""||day.val()==""){
        return false;
    }
    return true;
}
function startNovelty(){
    hideAll();
    $("input[name='form[noveltyTypeGroup]']").change(function(){
        var selectedVal = $("input[name='form[noveltyTypeGroup]']:checked");
        hideAll();
        var group=$("."+selectedVal.val());
        if(group.length==1){
            group.each(function(){
                $(this).show();
                $(this).find("input").prop( "checked", true );
            });
        }else{
            group.each(function(){
                $(this).show();
            });
        }
    })
    addNoveltySelectListener();
}
function hideAll(){
    $("input[name='form[noveltyTypeGroup]']").each(function(){

        $("."+$(this).val()).each(function(){
            $(this).hide();
        });
    })
}