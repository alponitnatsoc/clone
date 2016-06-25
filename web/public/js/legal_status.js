function prepareLegal() {

  hideOptionsAndLoadOldSettings();

  //CONTINUAR CONTRATO ---------
  $("#continuarContratoConsultaLegal").click(function () {
    var notGo = false;
    if( !validateBeforeNext(1) ){
        notGo = true;
        showModal(9);
    }

    if(!notGo)
      window.location.href = "/legal-payment";
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
    var notGo = false;
    if( !validateBeforeNext(2) ){
        notGo = true;
        showModal(9);
    }

    if(!notGo)
      window.location.href = "/legal-payment";
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
    var notGo = false;
    if( !validateBeforeNext(3) ){
        notGo = true;
        showModal(9);
    }

    if(!notGo)
      showModal(7);
  });

  $("#continuarAbogado").click(function () {
    window.location.href = "/legal-payment";
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
  //END ABOGADO --------

}

function hideOptionsAndLoadOldSettings() {

  $("#caso1").hide();
  $("#caso2").hide();
  $("#caso3").hide();

  //TODO Load Stored Settings

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
