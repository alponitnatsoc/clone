/**
 * Created by gabrielsamoma on 2/8/16.
 */
function addNoveltySelectListener() {
    var modBody=$(".modal-body");
    var form=modBody.find("form");
    form.submit(function (e) {
        e.preventDefault();
        $.ajax( {
            type: "POST",
            url: form.attr( 'action' )+form.find("input[name='form[noveltyType]']:checked").val(),
            data: form.serialize()
        }).done(function(data){
            var innerForm=$(data).find("#formForm")
            if(innerForm.find("form").length==0){
                modBody.html(
                    "<p><h3>Novedad Creada Exitosamente</h3></p>"
                );
            }else{
                modBody.html(
                    innerForm
                );
                addNoveltySelectListener();
            }
        });
    });
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