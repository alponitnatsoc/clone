function startEndContract(){
    $('[data-toggle="tooltip"]').tooltip();
    $("#show_end_contract").on('click',function (e) {
        $("#select_action_content").hide();
        $("#continue_contract_content").hide();
        $("#end_contract_content").show();
    });
    $("#show_continue_contract").on('click',function (e) {
        $("#select_action_content").hide();
        $("#end_contract_content").hide();
        $("#continue_contract_content").show();
    });
    $("#cancel_button1").on("click",function (e) {
        $("#select_action_content").show();
        $("#end_contract_content").hide();
        $("#continue_contract_content").hide();
    });
    $("#cancel_button2").on("click",function (e) {
        $("#select_action_content").show();
        $("#end_contract_content").hide();
        $("#continue_contract_content").hide();
    });
    $("#accept_contract_end_conditions").on("change",function (e) {
        $("#error_contract_end_terms_conditions").hide();
        $("#end_contract_terms_conditions").attr('style',"display: inline-flex;margin-bottom: 10px");
    });
    $("#end_contract_form_submit").on("click",function (e) {
        e.preventDefault();
        if($("#accept_contract_end_conditions").prop('checked') == false){
            var style = $("#end_contract_terms_conditions").attr('style');
            style = style + ";border: 1px solid red;border-radius:5px;padding:5px 10px";
            $("#end_contract_terms_conditions").attr('style',style);
            $("#error_contract_end_terms_conditions").show();
        }else{
            $("form[name='end_contract_form']").prop('valid',true);
            $("form[name='end_contract_form']").submit();
            // $("#close_modal").click();
        }
    });
}