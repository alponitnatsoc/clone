function payMethodLoad() {

  choosePayMethod();
  setupValidation();

  $( "#radioButtonPayMethod" ).click(function( event ) {
      choosePayMethod();
  });

  $("#pay_method").find(".radio").each(function (){
    $(this).css({"display":"inline-block", "margin-right":"10px"});
  })

  $("#expiry_dates").find(":input").each(function (){
    $(this).css({"float":"left", "width":"40%", "margin-right":"10px"});
  })

  $('#add_pay_method_form').on('submit', function(e){
    e.preventDefault();
    startValidation();
  });

  $("#pay_method_0").attr("disabled","true");
  var flag = false;
  $(".radio").has("label").each( function () {
    $(this).find("input").each( function () {
      if( $(this).attr("disabled") == "disabled" ){
        flag = true;
        return false;
      }
    });
    if ( flag == true ){
      $(this).css("color","#ccc");
      return false;
    }
  });

}

function choosePayMethod(){
  var payMethod = "";
  var radioPayMethod = $("#radioButtonPayMethod input[type='radio']:checked");
  payMethod = radioPayMethod.val();

  if(payMethod=="Cuenta Bancaria"){
        $("#tarjetaCredito").hide();
        $("#cuentaBancaria").show();
  }else if(payMethod=="Tarjeta de Crédito"){
        $("#tarjetaCredito").show();
        $("#cuentaBancaria").hide();
  }
}

function startValidation(){

  var form = $("#add_pay_method_form");
  var validator = form.validate();
  var flagValid = true;
  var radioPayMethod = $("#radioButtonPayMethod input[type='radio']:checked");
  var payMethod = radioPayMethod.val();

  if(payMethod=="Tarjeta de Crédito"){

    $(form).find("#name_on_card").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });

    $(form).find("#credit_card").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });

    $(form).find("#expiry_date_month").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });

    $(form).find("#expiry_date_year").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });

    $(form).find("#cvv").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });

  }
  else {

    $(form).find("#accountTypeId").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });

    $(form).find("#bankId").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });

    $(form).find("#accountNumber").each(function () {
            if (!validator.element($(this))) {
                flagValid = false;
                return;
            }
        });
  }

  if (!flagValid) {
      return false;
  }
  else {
      ajaxCall(form, payMethod);
  }
}

function setupValidation(){
  $.getScript("/public/js/jquery.creditCardValidator.js").done(function () {
      $('#credit_card').validateCreditCard(function (result) {
          $(this).removeClass();
          $(this).addClass('form-control');
          if (result.valid) {
              $(this).addClass(result.card_type.name);
              return $(this).addClass('valid');
          } else {
              return $(this).removeClass('valid');
          }
      });
  });

var minYear = new Date().getFullYear();
var form = $("#add_pay_method_form");
var validator;
  $.getScript("//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
    validator = form.validate({
        rules: {
            "name_on_card": "required" ,
            "credit_card": "required",
            "expiry_date_month": {required: true, maxlength: 4, minlength: 2, max: 12, min: 1},
            "expiry_date_year": {required: true, maxlength: 4, minlength: 4, max: 9999, min: minYear},
            "cvv": {required: true, maxlength: 3, minlength: 3, max: 999},
            "accountTypeId": "required" ,
            "bankId": "required",
            "accountNumber": {required: true, minlength: 1}
          },
        messages: {
            "name_on_card": "Por favor ingrese el nombre, exactamente igual a como sale en la tarjeta",
            "credit_card": "Por favor ingrese los dígitos correspondientes al número de su tarjeta",
            "expiry_date_month": {
                required: "Por favor ingrese el mes de vencimiento de su tarjeta, tal y como sale en ella",
                maxlength: "El mes debe ingresarse con 2 dígitos",
                minlength: "El mes debe ingresarse con 2 dígitos",
                max: "El mes no puede ser mayor de 12",
                min: "El mes no puede ser menor de 1"
            },
            "expiry_date_year": {
                required: "Por favor ingrese el año de vencimiento de su tarjeta, tal y como sale en ella",
                maxlength: "El año debe ingresarse con 4 dígitos",
                minlength: "El año debe ingresarse con 4 dígitos",
                max: "El año no puede ser mayor de 9999",
                min: "El año de vencimiento no puede ser previo al actual"
            },
            "cvv": {
                required: "Por favor ingrese los tres digitos de verificación de su tarjeta",
                minlength: "El código no puede tener menos de tres dígitos",
                maxlength: "El código no puede tener más de tres dígitos",
                max: "El código no puede ser mayor de 999"
            },
            "accountTypeId": "Por favor seleccione el tipo de cuenta del listado",
            "bankId": "Por favor seleccione su banco del listado",
            "accountNumber": {
                required: "Por favor ingrese el número de cuenta",
                minlength: "El número de cuenta no puede estar vacio",
                min: "El número de cuenta no puede ser 0"
            }
          }
        });
      });
}

function ajaxCall(form, payMethod){
  $.ajax({
      url: form.attr('action'),
      type: 'POST',
      data: {
          pay_method: payMethod,
          userId: $("#userId").val(),
          accountNumber: $("#accountNumber").val(),
          bankId: $("#bankId").val(),
          accountTypeId: $("#accountTypeId").val(),
          name_on_card: $("#name_on_card").val(),
          credit_card: $("#credit_card").val(),
          expiry_date_year:  $("#expiry_date_year").val(),
          expiry_date_month:  $("#expiry_date_month").val(),
          cvv:  $("#cvv").val()
      }
  }).done(function (data) {
      $("#addGenericPayMethodModal-modal-body").html("<h4>Agregado Exitosamente</h4>");
      location.reload();
  }).fail(function (jqXHR, textStatus, errorThrown) {
      $("#addGenericPayMethodModal-modal-body").html("<h4>Falló al agregar</h4>");
      location.reload();
  });
}
