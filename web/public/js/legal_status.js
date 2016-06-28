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

    if(!notGo)
      window.location.href = "/change-flag/1";
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

    if(!notGo)
      window.location.href = "/change-flag/0";
  });
  // END NUEVO

  //CONSULTAR ABOGADO  ----------
  $("#abogadoModal").click(function () {
    window.location.href = "/contact/asistencia";
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
    window.location.href = "/dashboard";
  });
  $("#continuarAbogado").click(function () {
    window.location.href = "/contact/asistencia";
  });
  //END ABOGADO --------

}

function hideOptionsAndLoadOldSettings() {

  $("#caso1").hide();
  $("#caso2").hide();
  $("#caso3").hide();

  /*var hop1 = $("#hOp1").val();
  var hop2 = $("#hOp2").val();
  var hop3 = $("#hOp3").val();
  var hop4 = $("#hOp4").val();
  var hop5 = $("#hOp5").val();
  var hop6 = $("#hOp6").val();

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
  }*/

}
function evaluateOptions() {


  var isB1On = $("#op1").is(':checked');
  var isB2On = $("#op2").is(':checked');
  var isB3On = $("#op3").is(':checked');
  var isB4On = $("#op4").is(':checked');
  var isB5On = $("#op5").is(':checked');
  var isB6On = $("#op6").is(':checked');

  if( isB1On && isB2On && !isB3On && isB4On){
    $("#caso1").show();
    $("#caso2").hide();
    $("#caso3").hide();
  }
  else if (isB1On && !isB2On && !isB6On) {
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

  var actualOption = 0;

  if( isB1On && isB2On && !isB3On && isB4On){
    actualOption = 1;
  }
  else if (isB1On && !isB2On && !isB6On) {
    actualOption = 2;
  }
  else{
    actualOption = 3;
  }

  return prevOption == actualOption;
}
