var signedContract = -1;
var ssSuscribed = -1;

function prepareLegal() {

  hideOptionsAndLoadOldSettings();

  //CONTINUAR CONTRATO ---------
  $("#continuarContratoConsultaLegal").click(function () {
    showModal(7);
  });

  $("#continuarContratoNext").click( function () {
    var notGo = false;
    if( !validateBeforeNext(1) ){
        notGo = true;
        showModal(9);
    }

    if(!notGo){
      var send = prepareUrlString();
      if($("#checkboxContinueContinuar").is(':checked')){
        window.location.href = "/store_config/" + send +"/change-flag/1";
      }
    }
  });
  // END CONTINUAR

  //NUEVO CONTRATO ---------
  $("#nuevoContratoConsultaLegal").click(function () {
    showModal(7);
  });

  $("#nuevoContratoNext").click( function () {
    var notGo = false;
    if( !validateBeforeNext(2) ){
        notGo = true;
        showModal(9);
    }

    if(!notGo){
      var send = prepareUrlString();
      if($("#checkboxContinueNuevo").is(':checked')){
        window.location.href = "/store_config/" + send +"/change-flag/0";
      }
    }

  });
  // END NUEVO

  //CONSULTAR ABOGADO  ----------
  $("#abogadoModal").click(function () {
    var send = prepareUrlString();
    window.location.href = "/store_config/" + send +"/contact/90";
  });

  $("#salirAbogadoModal").click(function () {
    var notGo = false;
    if( !validateBeforeNext(3) ){
        notGo = true;
        showModal(9);
    }

    if(!notGo)
      showModal(8);
  });

  $("#salirConsultoria").click(function () {
    var send = prepareUrlString();
    window.location.href = "/store_config/" + send +"/show_dashboard";
  });
  $("#continuarAbogado").click(function () {
    var send = prepareUrlString();
    window.location.href = "/store_config/" + send +"/contact/90";
  });
  //END ABOGADO --------

  //VALIDATION CHECKBOX
  if( !$("#checkboxContinueContinuar").is(':checked') ){
    $("#continuarContratoNext").attr("disabled","true");
    $("#continuarContratoNextSpan").attr("title","Debes marcar la casilla de aceptación de responsabilidad para poder continuar");
  }
  else{
    $("#continuarContratoNext").removeAttr("disabled");
    $("#continuarContratoNextSpan").attr("title","");
  }

  $("#checkboxContinueContinuar").click(function () {
    if(!$(this).is(':checked')){
           $("#continuarContratoNext").attr("disabled","true");
           $("#continuarContratoNextSpan").attr("title","Debes marcar la casilla de aceptación de responsabilidad para poder continuar");
      } else {
           $("#continuarContratoNext").removeAttr("disabled");
           $("#continuarContratoNextSpan").attr("title","");
      }
  });

  if( !$("#checkboxContinueNuevo").is(':checked') ){
    $("#nuevoContratoNext").attr("disabled","true");
    $("#nuevoContratoNextSpan").attr("title","Debes marcar la casilla de aceptación de responsabilidad para poder continuar");
  }
  else{
    $("#nuevoContratoNext").removeAttr("disabled");
    $("#nuevoContratoNextSpan").attr("title","");
  }

  $("#checkboxContinueNuevo").click(function () {
    if(!$(this).is(':checked')){
           $("#nuevoContratoNext").attr("disabled","true");
           $("#nuevoContratoNextSpan").attr("title","Debes marcar la casilla de aceptación de responsabilidad para poder continuar");
      } else {
           $("#nuevoContratoNext").removeAttr("disabled");
           $("#nuevoContratoNextSpan").attr("title","");
      }
  });


}

function hideOptionsAndLoadOldSettings() {

  $("#caso1").hide();
  $("#caso2").hide();
  $("#caso3").hide();

  var hop1 = $("#hOp1").val();
  var hop2 = $("#hOp2").val();
  var hop3 = $("#hOp3").val();
  var hop4 = $("#hOp4").val();
  var hop5 = $("#hOp5").val();
  var hop6 = $("#hOp6").val();
  var hop7 = $("#hOp7").val();

  if( hop1 == 1 ){
    $("#op1").attr('checked', 'checked');
  }
  if( hop2 == 1 ){
    $("#op2").attr('checked', 'checked');
  }
  if( hop3 == 1 ){
    $("#op3").attr('checked', 'checked');
  }
  if( hop4 == 1 ){
    $("#op4").attr('checked', 'checked');
  }
  if( hop5 == 1 ){
    $("#op5").attr('checked', 'checked');
  }
  if( hop6 == 1 ){
    $("#op6").attr('checked', 'checked');
  }
  if( hop7 == 1 ){
    $("#op7").attr('checked', 'checked');
  }

}

function evaluateOptions() {


  var isB1On = $("#op1").is(':checked');
  var isB2On = $("#op2").is(':checked');
  var isB3On = $("#op3").is(':checked');
  var isB4On = $("#op4").is(':checked');
  var isB5On = $("#op5").is(':checked');
  var isB6On = $("#op6").is(':checked');
  var isB7On = $("#op7").is(':checked');

  if( isB1On && isB2On && !isB3On && isB4On && !isB7On){
    $("#caso1").show();
    $("#caso2").hide();
    $("#caso3").hide();
  }
  else if (isB1On && !isB2On && !isB6On && !isB7On) {
    $("#caso1").hide();
    $("#caso2").show();
    $("#caso3").hide();
  }
  else{
    $("#caso1").hide();
    $("#caso2").hide();
    $("#caso3").show();
  }

}

function validateBeforeNext( prevOption ) {

  var isB1On = $("#op1").is(':checked');
  var isB2On = $("#op2").is(':checked');
  var isB3On = $("#op3").is(':checked');
  var isB4On = $("#op4").is(':checked');
  var isB5On = $("#op5").is(':checked');
  var isB6On = $("#op6").is(':checked');
  var isB7On = $("#op7").is(':checked');

  var actualOption = 0;

  if( isB1On && isB2On && !isB3On && isB4On && !isB7On){
    actualOption = 1;
  }
  else if (isB1On && !isB2On && !isB6On && !isB7On) {
    actualOption = 2;
  }
  else{
    actualOption = 3;
  }

  return prevOption == actualOption;
}

function prepareUrlString (){

  var optString = "";

  var isB1On = $("#op1").is(':checked');
  var isB2On = $("#op2").is(':checked');
  var isB3On = $("#op3").is(':checked');
  var isB4On = $("#op4").is(':checked');
  var isB5On = $("#op5").is(':checked');
  var isB6On = $("#op6").is(':checked');
  var isB7On = $("#op7").is(':checked');

  if(isB1On){
    optString += "1";
  }
  else {
    optString += "0";
  }
  if(isB2On){
    optString += "1";
  }
  else {
    optString += "0";
  }
  if(isB3On){
    optString += "1";
  }
  else {
    optString += "0";
  }
  if(isB4On){
    optString += "1";
  }
  else {
    optString += "0";
  }
  if(isB5On){
    optString += "1";
  }
  else {
    optString += "0";
  }
  if(isB6On){
    optString += "1";
  }
  else {
    optString += "0";
  }
  if(isB7On){
    optString += "1";
  }
  else {
    optString += "0";
  }

  return optString;
}

function displayForm(){
  $("#pregunta3").hide();
  $("#pregunta4").hide();
  $("#pregunta5").hide();
  $("#pregunta6").hide();
  $("#pregunta7").hide();
  $("#pregunta8").hide();


  $("#p2b1").click(function (){
    $("#pregunta2").hide();
    $("#pregunta3").show();
  });
  $("#p2b2").click(function (){
    $("#pregunta2").hide();
    $("#pregunta5").show();
    ssSuscribed = 0;
  });

  $("#p3b1").click(function (){
    $("#pregunta3").hide();
    $("#pregunta4").show();
  });
  $("#p3b2").click(function (){
    $("#pregunta3").hide();
    $("#pregunta5").show();
  });

  $("#p4b1").click(function (){
    window.location.href = "/store_config/1101100/change-flag/1";
  });
  $("#p4b2").click(function (){
    $("#pregunta4").hide();
    $("#pregunta7").show();
    sendEmail("Revisar caso registro" , "El empleador es una persona natural. Si está afiliado a seguridad social. El usuario figura como su empleador en SS. Pero no ha pagado cumplidamente los aportes a SS");
  });

  $("#p5b1").click(function (){
    $("#pregunta5").hide();
    $("#pregunta6").show();
    signedContract = 1;
  });
  $("#p5b2").click(function (){
    $("#pregunta5").hide();
    $("#pregunta6").show();
    signedContract = 0;
  });

  $("#p6b1").click(function (){
    $("#pregunta6").hide();
    $("#pregunta8").show();
  });
  $("#p6b2").click(function (){
    $("#pregunta6").hide();
    $("#pregunta7").show();

    if(signedContract == 1){
      sendEmail("Revisar caso registro" , "El empleador es una persona natural. NO está afiliado a seguridad social. SI tiene un contrato firmado. Lo contrató hace más de un año");
    }else {
      sendEmail("Revisar caso registro" , "El empleador es una persona natural. NO está afiliado a seguridad social. NO tiene un contrato firmado. Lo contrató hace más de un año");
    }
  });

  $("#p7b1").click(function (){
    window.location.href = "/logout";
  });

  $("#p8b2").click(function (){

    if( $('#theCheckBox').is(":checked") ){
      $contractVal = -1;

      if(signedContract == 1){
        $contractVal = 1;
      }
      else {
        $contractVal = 0;
      }

      if(ssSuscribed == 0){
        window.location.href = "/store_config/10000" + $contractVal + "0/change-flag/1";
      }else{
        window.location.href = "/store_config/11000" + $contractVal + "0/change-flag/1";
      }
    }

  });

  $("#restart").click(function (){
    location.reload();
  });

  $("#checkBox").click(function(){
    if( $("#theCheckBox").prop('checked') == false ){
      $("#theCheckBox").prop('checked', true);
    }
    else{
      $("#theCheckBox").prop('checked', false);
    }

  });

}

function sendEmail(subject , message ){
  var name = "{{ user.personPerson.fullName }}";
  var phone = "{{ user.personPerson.phones.first.phoneNumber }}";
  var email = "{{ user.email }}";

  $.ajax( {
    type: "POST",
    url: '/api/public/v1/sends/registrations/stucks/emails',
    data: jQuery.param( { "name" : name , "phone": phone , "email" : email , "message" : message , "subject" : subject } )
  });

}
