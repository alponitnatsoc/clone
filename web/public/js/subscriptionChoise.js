function startSubscriptionChoise() {
    var tiempo_parcialSlider, medio_tiempoSlider, tiempo_completoSlider;

    $(document).ready(function () {
        //loadConstrains();
        loadConstrainsL();
    });

    var url = '';
    var button = '';
    var employee_id = '';
    var total = 0;
    var subtotal = 0;
    $(".btn-change-state-contract").click(function (event) {
        button = $(this);
        if (button.html() == 'Activar') {
            ajax(button);
        } else {
            $('#modal_confirm').modal('show');
        }
    });
    $(".modal-content .close").hide();
    $('#modal_confirm').on('show.bs.modal', function (event) {
        //event.preventDefault();
        //button = $(event.relatedTarget);
        //url = button.data('href');
        if ($(".activo").length > 1 || $(button).html() == "Activar") {
            $(".btn-change-state-contract-confirm").show();
        } else {
            $(".btn-change-state-contract-confirm").hide();
        }
    });
    //$(".btn-change-state-contract").on('click', function (e) {
    //    $('#modal_confirm').modal('show');
    //});
    $(".btn-change-state-contract-confirm").on('click', function (e) {
        if ($(".activo").length > 1 || $(button).html() == "Activar") {
            ajax(button);
        } else {
            $('#modal_confirm').modal('hide');
        }
    });
    //$("#open_pricing_calc").on('click', function (e) {
    //    $('#modal_price_calculator').modal('show');
    //});
    function ajax(button) {
        url = button.data('href');
        employee_id = button.data('id');
        $.ajax({
            url: url,
            type: 'GET',
            beforeSend: function (xhr) {
                $(button).attr('disabled', true);
                $(".btn-change-state-contract-confirm").attr('disabled', true);
            }
        }).success(function (data) {
            parent = $(button).parent().parent();
            //console.log(parent);
            female = parent.find(".female").length;
            male = parent.find(".male").length;
            state = '';
            //console.log(data);
            if (data.state == 'Inactivo') {
                employee[employee_id]['state'] = 0;
                $(button).html("Activar");
                $(button).removeClass("on");
                $(button).addClass("off");
                parent.removeClass("activo");
                parent.addClass("inactivo");
                if (female > 0) {
                    state = "inactivada";
                } else if (male > 0) {
                    state = "inactivado";
                } else {
                    state = "inactivado";
                }
            } else {
                employee[employee_id]['state'] = 2;
                $(button).html("Inactivar");
                $(button).removeClass("off");
                $(button).addClass("on");
                parent.removeClass("inactivo");
                parent.addClass("activo");
                if (female > 0) {
                    state = "activada";
                } else if (male > 0) {
                    state = "activado";
                } else {
                    state = "activado";
                }
            }
            //console.log(parent);
            $('#modal_confirm').modal('hide');
            name = parent.find(".employee_name").html();
            $('.result_ajax_msg').html(name + " fue " + state + " exitosamente.");
            $('.result_ajax').show();
            setTimeout(function () {
                $('.result_ajax_msg').html("");
                $('.result_ajax').hide();
            }, 2000);
            loadConstrainsL();
        }).fail(function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
            $('#modal_confirm').modal('hide');
        }).always(function () {
            $(button).attr('disabled', false);
            $(".btn-change-state-contract-confirm").attr('disabled', false);
            location.reload();
        });
    }

    function calculatePrice(contenedor) {
        var Tiempo_Completo = 0, Medio_tiempo = 0, Trabajo_por_días = 0, total = 0, subtotal = 0, count_employee = 0, total_income=0, total_seguridad_social=0;
        if (contenedor == '_calc') {
            Tiempo_Completo = tiempo_completoSlider ? parseInt(tiempo_completoSlider.noUiSlider.get()) : 0;
            Medio_tiempo = medio_tiempoSlider ? parseInt(medio_tiempoSlider.noUiSlider.get()) : 0;
            Trabajo_por_días = tiempo_parcialSlider ? parseInt(tiempo_parcialSlider.noUiSlider.get()) : 0;
        } else {
            Tiempo_Completo = $(".activo .tiempo_completo").length;
            Medio_tiempo = $(".activo .medio_tiempo").length;
            Trabajo_por_días = $(".activo .trabajo_por_dias").length;
        }
        //console.log("Tiempo_Completo:" + Tiempo_Completo);
        //console.log("Medio_tiempo:" + Medio_tiempo);
        //console.log("Trabajo_por_días:" + Trabajo_por_días);
        if (Tiempo_Completo > 0) {
            PS3 = parseFloat($("#PS3").val());
            PS3_IVA = 1 + parseFloat($("#PS3_IVA").val());
            //subtotal = Math.ceil(subtotal + (Tiempo_Completo * (PS3 * PS3_IVA)));
            subtotal = subtotal + (Tiempo_Completo * producto['PS3']);
        }
        if (Medio_tiempo > 0) {
            PS2 = parseFloat($("#PS2").val());
            PS2_IVA = 1 + parseFloat($("#PS2_IVA").val());
            //subtotal = Math.ceil(subtotal + (Medio_tiempo * (PS2 * PS2_IVA)));
            subtotal = subtotal + (Medio_tiempo * producto['PS2']);
        }
        if (Trabajo_por_días > 0) {
            PS1 = parseFloat($("#PS1").val());
            PS1_IVA = 1 + parseFloat($("#PS1_IVA").val());
            //subtotal = Math.ceil(subtotal + (Trabajo_por_días * (PS1 * PS1_IVA)));
            subtotal = subtotal + (Trabajo_por_días * producto['PS1']);
        }

        for (key in contrato) {
            //console.log("calculate(item, index)");
            //console.log(contrato[key]);
            if (employee[key]['state'] > 0) {
                count_employee = count_employee + 1;
                type = (contrato[key]['timeCommitment'] == 'XD' ? 'days' : 'complete');
                salaryM = contrato[key]['salary'];
                salaryD = contrato[key]['salary'] / contrato[key]['workableDaysMonth'];
                numberOfDays = contrato[key]['workableDaysMonth'];
                sisben = contrato[key]['sisben'];
                transport = contrato[key]['transportAid'];
                resultado = calculator(type, salaryM, salaryD, numberOfDays, sisben, transport);
                calculatorL(type, numberOfDays, salaryM, salaryD, sisben, transport);
                total = total + resultado['totalExpenses2'];

                $("div[data-id='"+ key +"']").each(function(){
                  if($(this).hasClass("salaryIndVal"))
                  {
                    $(this).html(getPrice(resultado['totalIncome']));
                    total_income += resultado['totalIncome'];
                    total_seguridad_social += resultado['totalDiscountsForEmployer'];
                  }
                });

                $("#totalSeguridadSocial").html(getPrice(total_seguridad_social));
                $("#sueldos").html(getPrice(total));
                $("#primerPago").html(getPrice(total));
                $("#segundoPago").html(getPrice(total + subtotal));
                $("#count_employee").html(count_employee + ' Empleados');
                $("#totalSal").html(getPrice(total_income));
            }
        }

        $("#divSubtotal").html(getPrice(subtotal));

        if (subtotal == 0) {
            $("input[type=submit]").attr('disabled', true);
        } else {
            $("input[type=submit]").attr('disabled', false);
        }

        $(".suscriptionInd").each(function( index ) {
          if (employee[$(this).data("id")]['state'] > 0) {
            var workableDays = contrato[$(this).data("id")]['workableDaysMonth'];
            var endValue = 0;
            if( workableDays >= 20){
              endValue = producto['PS3'];
            }
            else if (workableDays >= 11){
              endValue = producto['PS2'];
            }
            else {
              endValue = producto['PS1'];
            }
            $(this).html(getPrice(endValue));
          } else {
            $(this).html(getPrice(0));
          }
        });



    }

    function getPrice(valor) {
        price = parseFloat(valor.toString().replace(/,/g, ""))
            .toFixed(0)
            .toString()
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        return "$ " + price;
    }

    $("#btnRedimir").on('click', function () {
        if ($("#codigo_referido").val().length >= 6) {
            $.ajax({
                method: "POST",
                url: "/api/public/v1/validates/codes",
                data: {code: $("#codigo_referido").val()},
                beforeSend: function (xhr) {
                    $("#codigo_referido_estado").html('Validando código...');
                    $("#codigo_referido_estado").removeClass('codigo_referido_valido');
                    $("#codigo_referido_estado").removeClass('codigo_referido_invalido');
                }
            }).done(function (data) {
                    if (data == true) {
                        $("#esReferido").val(1);
                        $("#codigo_referido").attr('readonly', true);
                        $("#codigo_referido_estado").removeClass('codigo_referido_invalido');
                        $("#codigo_referido_estado").addClass('codigo_referido_valido');
                        $("#codigo_referido_estado").html('Código valido');
                        $("#codigoReferido").hide();
                        $("#btnRedimir").attr('disabled', true);
                        $("#btnRedimir").addClass('off', true);
                    } else {
                        $("#codigo_referido_estado").removeClass('codigo_referido_valido');
                        $("#codigo_referido_estado").addClass('codigo_referido_invalido');
                        $("#codigo_referido_estado").html(data);
                    }
                }
            ).fail(function (jqXHR, textStatus, errorThrown) {
                $("#codigo_referido_estado").removeClass('codigo_referido_valido');
                $("#codigo_referido_estado").addClass('codigo_referido_invalido');
                $("#codigo_referido_estado").html('No se pudo validar el código');
                console.log("FAIL codigo_referido {{ path('api_public_post_validate_code') }}:");
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
            });
        }
    });

}

//Extract Constraints
var transportAid;
var smmlv;
var EPSEmployer;
var EPSEmployee;
var PensEmployer;
var PensEmployee;
var arl;
var caja;
var sena;
var icbf;
var vacations;
var taxCes;
var ces;
var dotation;
var transportAidDaily;
var vacations30D;
var dotationDaily;
function loadConstrainsL() {
    $.ajax({
        url: "/api/public/v1/calculator/constraints",
        type: "GET",
        statusCode: {
            200: function (data) {
                //Extract Constraints
                constraints = data["response"];
                transportAid = parseFloat(constraints['auxilio transporte']);
                smmlv = parseFloat(constraints['smmlv']);
                EPSEmployer = parseFloat(constraints['eps empleador']);
                EPSEmployee = parseFloat(constraints['eps empleado']);
                PensEmployer = parseFloat(constraints['pension empleador']);
                PensEmployee = parseFloat(constraints['pension empleado']);
                arl = parseFloat(constraints['arl']);
                caja = parseFloat(constraints['caja']);
                sena = parseFloat(constraints['sena']);
                icbf = parseFloat(constraints['icbf']);
                vacations = parseFloat(constraints['vacaciones']);
                taxCes = parseFloat(constraints['intereses cesantias']);
                ces = parseFloat(constraints['cesantias']);
                dotation = parseFloat(constraints['dotacion']);
                transportAidDaily = transportAid / 30;
                vacations30D = vacations / 30;
                dotationDaily = dotation / 30;

                fillTable();
            }
        }
    });



}

function fillTable(){

  var Tiempo_Completo = 0, Medio_tiempo = 0, Trabajo_por_días = 0, total = 0, subtotal = 0, count_employee = 0, total_income=0, total_seguridad_social=0;

  Tiempo_Completo = $(".activo .tiempo_completo").length;
  Medio_tiempo = $(".activo .medio_tiempo").length;
  Trabajo_por_días = $(".activo .trabajo_por_dias").length;

  if (Tiempo_Completo > 0) {
      PS3 = parseFloat($("#PS3").val());
      PS3_IVA = 1 + parseFloat($("#PS3_IVA").val());
      //subtotal = Math.ceil(subtotal + (Tiempo_Completo * (PS3 * PS3_IVA)));
      subtotal = subtotal + (Tiempo_Completo * producto['PS3']);
  }
  if (Medio_tiempo > 0) {
      PS2 = parseFloat($("#PS2").val());
      PS2_IVA = 1 + parseFloat($("#PS2_IVA").val());
      //subtotal = Math.ceil(subtotal + (Medio_tiempo * (PS2 * PS2_IVA)));
      subtotal = subtotal + (Medio_tiempo * producto['PS2']);
  }
  if (Trabajo_por_días > 0) {
      PS1 = parseFloat($("#PS1").val());
      PS1_IVA = 1 + parseFloat($("#PS1_IVA").val());
      //subtotal = Math.ceil(subtotal + (Trabajo_por_días * (PS1 * PS1_IVA)));
      subtotal = subtotal + (Trabajo_por_días * producto['PS1']);
  }

  var total_seguridad_social = 0;
  var total_cost_all = 7000 + subtotal;
  for (key in contrato) {

      if (employee[key]['state'] > 0) {
          count_employee = count_employee + 1;
          type = (contrato[key]['timeCommitment'] == 'XD' ? 'days' : 'complete');
          salaryM = contrato[key]['salary'];
          salaryD = contrato[key]['salary'] / contrato[key]['workableDaysMonth'];
          numberOfDays = contrato[key]['workableDaysMonth'];
          sisben = contrato[key]['sisben'];
          transport = contrato[key]['transportAid'];
          //resultado = calculator(type, salaryM, salaryD, numberOfDays, sisben, transport);
          console.log(key);
          resultado = calculatorL(type, numberOfDays, salaryM, salaryD, sisben, transport);
          total = total + resultado['totalExpenses2'];

          var salaryKey = 0;

          salaryKey = resultado['plainSalary'] + resultado['transportCal'] - resultado['EPSEmployeeCal'] - resultado['PensEmployeeCal'];
          localSecurityDiscount = resultado['EPSEmployeeCal'] + resultado ['PensEmployeeCal'] + resultado ['senaCal'] + resultado ['icbfCal'] + resultado ['EPSEmployerCal'] + resultado ['PensEmployerCal'] + resultado ['cajaCal'] + resultado ['arlCal'];
          $("div[data-id='"+ key +"']").each(function(){
            if($(this).hasClass("salaryIndVal"))
            {
              $(this).html(getPrice(salaryKey));
              total_income += salaryKey;
              total_seguridad_social += localSecurityDiscount;
            }
          });

          $("#totalSeguridadSocial").html(getPrice(total_seguridad_social));

          $("#sueldos").html(getPrice(total));
          $("#primerPago").html(getPrice(total));
          $("#segundoPago").html(getPrice(total + subtotal));
          $("#count_employee").html(count_employee + ' Empleados');
          $("#totalSal").html(getPrice(total_income));

          total_cost_all = 7000 + subtotal + total_seguridad_social + total_income;
          $("#totalCostAll").html(getPrice(total_cost_all));
      }
  }

  $("#divSubtotal").html(getPrice(subtotal));

  if (subtotal == 0) {
      $("input[type=submit]").attr('disabled', true);
  } else {
      $("input[type=submit]").attr('disabled', false);
  }

  $(".suscriptionInd").each(function( index ) {
    if (employee[$(this).data("id")]['state'] > 0) {
      var workableDays = contrato[$(this).data("id")]['workableDaysMonth'];
      var endValue = 0;
      if( workableDays >= 20){
        endValue = producto['PS3'];
      }
      else if (workableDays >= 11){
        endValue = producto['PS2'];
      }
      else {
        endValue = producto['PS1'];
      }

      if(count_employee >= 3){
        endValue = endValue * 0.9;
      }
      $(this).html(getPrice(endValue));
    } else {
      $(this).html(getPrice(0));
    }
  });


}
function calculatorL(type, numberOfDays, salaryM, salaryD, sisben, transport) {

    var aid = 0;
    var aidD = 0;

    var totalExpenses = 0;
    var totalIncome = 0;
    var plainSalary = 0;
    var EPSEmployerCal = 0;
    var EPSEmployeeCal = 0;
    var PensEmployeeCal = 0;
    var PensEmployerCal = 0;
    var transportCal = 0;
    var cesCal = 0;
    var taxCesCal = 0;
    var dotationCal = 0;
    var vacationsCal = 0;
    var arlCal = 0;
    var cajaCal = 0;
    var senaCal = 0;
    var icbfCal = 0;
    var salaryM2 = 0;
    var base = 0;
    if (aid == 0) {
        aidD = 0;
    }
    if (type == "days") {
        transport = 1;
        if (transport == 1) {
            //salaryD -= transportAidDaily;
        }
        //if it overpass the SMMLV calculates as a full time job  or
        //if does not belongs to SISBEN
        if (((salaryD + transportAidDaily + aidD) * numberOfDays) > smmlv || sisben == -1) {
            if (((salaryD + transportAidDaily + aidD) * numberOfDays) > smmlv) {
                base = (salaryD + aidD) * numberOfDays;
            } else {
                base = smmlv;
            }
            transportCal = transportAidDaily * numberOfDays;
            salaryD = (salaryD - transportAidDaily)/(1-(PensEmployee));
            totalExpenses = ((salaryD + aidD + transportAidDaily + dotationDaily) * numberOfDays) + ((EPSEmployer +
                PensEmployer + arl + caja + sena + icbf) * base) + (vacations30D * numberOfDays * salaryD) +
                ((taxCes + ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            EPSEmployerCal = EPSEmployer * base;
            EPSEmployeeCal = EPSEmployee * base;
            PensEmployerCal = PensEmployer * base;
            PensEmployeeCal = PensEmployee * base;
            arlCal = arl * base;
            cesCal = ((ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            cajaCal = caja * base;
            vacationsCal = vacations30D * numberOfDays * salaryD;
            dotationCal = dotationDaily * numberOfDays;
            senaCal = sena * base;
            icbfCal = icbf * base;
            totalIncome = (salaryD * numberOfDays) - EPSEmployerCal - PensEmployerCal;
            plainSalary = salaryD * numberOfDays;
        } else {
            transportCal = transportAidDaily * numberOfDays;
            var EPSEmployee2 = 0;
            var EPSEmployer2 = 0;
            base = smmlv;
            //calculate the caja and pens in base of worked days
            if (numberOfDays <= 7) {
                PensEmployerCal = PensEmployer * base / 4;
                PensEmployeeCal = PensEmployee * base / 4;
                cajaCal = caja * base;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);

            } else if (numberOfDays <= 14) {
                PensEmployerCal = PensEmployer * base / 2;
                PensEmployeeCal = PensEmployee * base / 2;
                cajaCal = caja * base / 2;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);
            } else if (numberOfDays <= 21) {
                PensEmployerCal = PensEmployer * base * 3 / 4;
                PensEmployeeCal = PensEmployee * base * 3 / 4;
                cajaCal = caja * base * 3 / 4;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);
            } else {
                PensEmployerCal = PensEmployer * base;
                PensEmployeeCal = PensEmployee * base;
                cajaCal = caja * base;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);
            }
            //then calculate arl ces and the rest
            totalExpenses = ((salaryD + aidD + transportAidDaily + dotationDaily) * numberOfDays) + ((EPSEmployee2 + arl
                + sena + icbf) * base) + (vacations30D * numberOfDays * salaryD) + ((taxCes + ces) * (((salaryD + aidD)
                * numberOfDays * 30 / 28) + transportAid)) + PensEmployeeCal + cajaCal + PensEmployerCal;
            EPSEmployerCal = EPSEmployer2 * base;
            EPSEmployeeCal = EPSEmployer2 * base;
            arlCal = arl * base;
            cesCal = ((ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            vacationsCal = vacations30D * numberOfDays * salaryD;
            dotationCal = dotationDaily * numberOfDays;
            senaCal = sena * base;
            icbfCal = icbf * base;
            totalIncome = ((salaryD + transportAidDaily) * numberOfDays) - PensEmployeeCal;
            plainSalary = salaryD * numberOfDays;
        }

    } else {
        var transportAid2=0;
        if (transport == 1) {
            //salaryM -= transportAid;
        } else if (salaryM + aidD > smmlv * 2) {
            transportAid2 = 0;
        }else{
            transportAid2=transportAid;
        }

        salaryM2 = (salaryM - transportAid2)/(1-(EPSEmployee+PensEmployee));
        totalExpenses = salaryM + aidD + transportAid2 + dotation + ((EPSEmployer + PensEmployer + arl + caja +
            vacations30D + sena + icbf) * (salaryM + aidD)) + ((taxCes + ces) * (salaryM + aidD + transportAid2));
        EPSEmployerCal = EPSEmployer * (salaryM + aidD);
        EPSEmployeeCal = EPSEmployee * (salaryM + aidD);
        PensEmployerCal = PensEmployer * (salaryM + aidD);
        PensEmployeeCal = PensEmployee * (salaryM + aidD);
        arlCal = arl * (salaryM + aidD);
        cesCal = ces * (salaryM + aidD + transportAid2);
        taxCesCal = taxCes * (salaryM + aidD + transportAid2);
        cajaCal = caja * (salaryM + aidD);
        vacationsCal = vacations30D * (salaryM + aidD);
        transportCal = transportAid2;
        dotationCal = dotation;
        senaCal = sena * (salaryM + aidD);
        icbfCal = icbf * (salaryM + aidD);
        /*console.log("salaryM " + salaryM);
        console.log("transportCal " + transportCal);
        console.log("EPSEmployerCal " + EPSEmployerCal);
        console.log("PensEmployerCal " + PensEmployerCal);*/

        totalIncome = (salaryM + transportCal - EPSEmployerCal - PensEmployerCal);
        console.log("TI " + totalIncome);
        console.log("SM " + salaryM);
        plainSalary = salaryM;
    }
    var resposne = [];

    if ((type == "days"&&(salaryD <= 0 || numberOfDays == null || numberOfDays == 0))||(type != "days"&&(salaryM<=0))) {
        totalExpenses = 0;
        resposne['totalExpenses'] = 0;
        resposne['dailyExpenses'] = 0;
        resposne['dailyIncome'] = 0;
        resposne['EPSEmployerCal'] = 0;
        resposne['EPSEmployeeCal'] = 0;
        resposne['PensEmployerCal'] = 0;
        resposne['PensEmployeeCal'] = 0;
        resposne['arlCal'] = 0;
        resposne['cesCal'] = 0;
        resposne['taxCesCal'] = 0;
        resposne['cajaCal'] = 0;
        resposne['vacationsCal'] = 0;
        resposne['transportCal'] = 0;
        resposne['dotationCal'] = 0;
        resposne['senaCal'] = 0;
        resposne['icbfCal'] = 0;
        resposne['totalIncome'] = 0;
    } else {
        resposne['totalExpenses'] = totalExpenses;
        resposne['dailyExpenses'] = totalExpenses / numberOfDays;
        resposne['dailyIncome'] = totalIncome / numberOfDays;
        resposne['EPSEmployerCal'] = EPSEmployerCal;
        resposne['EPSEmployeeCal'] = EPSEmployeeCal;
        resposne['PensEmployerCal'] = PensEmployerCal;
        resposne['PensEmployeeCal'] = PensEmployeeCal;
        resposne['arlCal'] = arlCal;
        resposne['cesCal'] = cesCal;
        resposne['taxCesCal'] = taxCesCal;
        resposne['cajaCal'] = cajaCal;
        resposne['vacationsCal'] = vacationsCal;
        resposne['transportCal'] = transportCal;
        resposne['dotationCal'] = dotationCal;
        resposne['senaCal'] = senaCal;
        resposne['icbfCal'] = icbfCal;
        resposne['totalIncome'] = totalIncome;
        resposne['plainSalary'] = plainSalary;
        resposne['numberOfDays'] = numberOfDays;
        resposne['salaryM2'] = salaryM2;

    }

    return resposne;
}
