
/**
 * Created by gabrielsamoma on 11/11/15.
 */
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
var prima;
function loadConstrains() {
    var constraints = null;
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
                prima =  parseFloat(constraints['prima']);
                calculator();
            }
        }
    });

}

function calculatorCalculate(){
    addListenersCalc();
    loadConstrains();
}
function calculator(){
    var type=$("input[name='form[tipo]']:checked").val();
    var salaryM=parseFloat(accounting.unformat($("#form_salarioM").val()));
    var salaryD=parseFloat(accounting.unformat($("#form_salarioD").val()));

    if (salaryD == "") {
        salaryD = 0;
    }if (salaryM == "") {
        salaryM = 0;
    }

    var numberOfDays=$("#form_numberOfDays").val();
    var arlProf = 0;
    var arlChoose = $("#form_position").val();

    if( arlChoose == 1 ){ //empleada
        arlProf = 0.00522;
    }
    else if (arlChoose == 2) { //conductor
        arlProf = 0.02436;
    }
    else if (arlChoose == 3) { //ninero
        arlProf = 0.00522;
    }
    else if (arlChoose == 4) { //enfermero
        arlProf = 0.01044;
    }
    else if (arlChoose == 5) { //mayordomo
        arlProf = 0.01044;
    }

    var aportaPens = $("input[name='form[pension]']:checked").val();
    var lPensEmployer = PensEmployer;
    var lPensEmployee = PensEmployee;

    if(aportaPens == "-1"){
        lPensEmployer = 0;
        lPensEmployee = 0;
    }

    var aid = 0;
    var aidD = 0;

    var sisben = null;

    var transport = $("input[name='form[transporte]']:checked").val();
    if (type == "days") {
        sisben = 1;
    } else {
        $("#diasTrabajadosMod").text("");
        type = "complete";
    }

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
    var primaCal = 0;
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
        var PensEmployeeCal2 = 0;
        var salaryD2 = 0;

        var base2=smmlv;
        if (numberOfDays <= 7) {
            PensEmployeeCal2 = lPensEmployee * base2 / 4;
            salaryD2 = (salaryD - transportAidDaily)+(PensEmployeeCal2/numberOfDays);
        } else if (numberOfDays <= 14) {
            PensEmployeeCal2 = lPensEmployee * base2 / 2;
            salaryD2 = (salaryD - transportAidDaily)+(PensEmployeeCal2/numberOfDays);
        } else if (numberOfDays <= 21) {
            PensEmployeeCal2 = lPensEmployee * base2 * 3 / 4;
            salaryD2 = (salaryD - transportAidDaily)+(PensEmployeeCal2/numberOfDays);
        } else {
            PensEmployeeCal2 = lPensEmployee * base2;
            salaryD2 = (salaryD - transportAidDaily)+(PensEmployeeCal2/numberOfDays);
        }

        var displayError = false;
        if (salaryD2  > smmlv/numberOfDays || sisben == -1) {
            displayError = true;
            if (((salaryD + transportAidDaily + aidD) * numberOfDays) > smmlv) {
                base = (salaryD + aidD) * numberOfDays;
            } else {
                base = smmlv;
            }
            transportCal = transportAidDaily * numberOfDays;
            var localEPS = smmlv / 30 / numberOfDays;;
            var localPens =smmlv / 30 / numberOfDays;
            if(aportaPens == "-1"){
                localPens = 0;
            }
            salaryD = salaryD - transportAidDaily + localEPS + localPens;
            //salaryD = (salaryD - transportAidDaily)/(1-(lPensEmployee + EPSEmployee));
            totalExpenses = ((salaryD + aidD + transportAidDaily + dotationDaily) * numberOfDays) + ((EPSEmployer +
                lPensEmployer + arlProf + caja + sena + icbf) * base) + (vacations30D * numberOfDays * salaryD) +
                ((taxCes + ces) * (((salaryD + aidD) * numberOfDays) + transportAidDaily*numberOfDays));
            EPSEmployerCal = EPSEmployer * base;
            EPSEmployeeCal = smmlv / 30;
            PensEmployerCal = lPensEmployer * base;
            PensEmployeeCal = smmlv / 30;
            if(aportaPens == "-1"){
                PensEmployeeCal = 0;
            }
            arlCal = arlProf * base;
            //cesCal = ((ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            cesCal = ((ces) * (((salaryD + aidD) * numberOfDays ) + transportAidDaily*numberOfDays));
            primaCal = ((prima) * (((salaryD + aidD) * numberOfDays ) + transportAidDaily*numberOfDays));
            //taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays) + transportAidDaily*numberOfDays));
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
                PensEmployerCal = lPensEmployer * base / 4;
                PensEmployeeCal = lPensEmployee * base / 4;
                cajaCal = caja * base / 4;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);

            } else if (numberOfDays <= 14) {
                PensEmployerCal = lPensEmployer * base / 2;
                PensEmployeeCal = lPensEmployee * base / 2;
                cajaCal = caja * base / 2;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);
            } else if (numberOfDays <= 21) {
                PensEmployerCal = lPensEmployer * base * 3 / 4;
                PensEmployeeCal = lPensEmployee * base * 3 / 4;
                cajaCal = caja * base * 3 / 4;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);
            } else {
                PensEmployerCal = lPensEmployer * base;
                PensEmployeeCal = lPensEmployee * base;
                cajaCal = caja * base;
                salaryD = (salaryD - transportAidDaily)+(PensEmployeeCal/numberOfDays);
            }
            //then calculate arl ces and the rest
            totalExpenses = ((salaryD + aidD + transportAidDaily + dotationDaily) * numberOfDays) + ((EPSEmployee2 + arlProf
                + sena + icbf) * base) + (vacations30D * numberOfDays * salaryD) + ((taxCes + ces) * (((salaryD + aidD)
                * numberOfDays) + transportAidDaily*numberOfDays)) + PensEmployeeCal + cajaCal + PensEmployerCal;
            EPSEmployerCal = EPSEmployer2 * base;
            EPSEmployeeCal = EPSEmployer2 * base;
            arlCal = arlProf * base;
            //cesCal = ((ces) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            cesCal = ((ces) * (((salaryD + aidD) * numberOfDays ) + transportAidDaily*numberOfDays));
            primaCal = ((prima) * (((salaryD + aidD) * numberOfDays ) + transportAidDaily*numberOfDays));
            //taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays * 30 / 28) + transportAid));
            taxCesCal = ((taxCes) * (((salaryD + aidD) * numberOfDays) + transportAidDaily*numberOfDays));
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

        salaryM2 = (salaryM - transportAid2)/(1-(EPSEmployee+lPensEmployee));
        totalExpenses = salaryM + aidD + transportAid2 + dotation + ((EPSEmployer + lPensEmployer + arlProf + caja +
            vacations30D + sena + icbf) * (salaryM + aidD)) + ((taxCes + ces) * (salaryM + aidD + transportAid2));
        EPSEmployerCal = EPSEmployer * (salaryM + aidD);
        EPSEmployeeCal = EPSEmployee * (salaryM + aidD);
        PensEmployerCal = lPensEmployer * (salaryM + aidD);
        PensEmployeeCal = lPensEmployee * (salaryM + aidD);
        arlCal = arlProf * (salaryM + aidD);
        cesCal = ces * (salaryM + aidD + transportAid2);
        primaCal = prima * (salaryM + aidD + transportAid2);
        taxCesCal = taxCes * (salaryM + aidD + transportAid2);
        cajaCal = caja * (salaryM + aidD);
        vacationsCal = vacations30D * (salaryM + aidD);
        transportCal = transportAid2;
        dotationCal = dotation;
        senaCal = sena * (salaryM + aidD);
        icbfCal = icbf * (salaryM + aidD);
        totalIncome = (salaryM + transportCal - EPSEmployerCal - PensEmployerCal);
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
        resposne['primaCal'] = 0;
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
        resposne['primaCal'] = primaCal;

    }

    changeValues(resposne);
}
function addListenersCalc(){
    $(".all").hide();
    $("input[name='form[tipo]']").change(function(){
        var selected=$("input[name='form[tipo]']:checked").val();
        if(selected=="days"){
            $(".all").show();
            $(".days").show();
            $(".complete").hide();

        }else{
            $(".all").show();
            $(".complete").show();
            $(".days").hide();
        }
        $("common").show();
    });

    $("[name='form']").on("submit", function (e) {
        calculator();
        $("#calculatorResultsModal").modal('toggle');
        return false;//window.location.href = "/calculadora";
    });
    /*$("input[name='form[auxilio]']").change(function(){
        var selected=$("input[name='form[auxilio]']:checked").val();
        if(selected==1){
            $(".aid").show();
        }else{
            $(".aid").hide();
        }
    })*/

}

function jsonCalcToHTML(data) {
    var htmls="<h2 class='modal-title'>Si su empleado tiene estas características debe pagar:</h2>" +
        "<ul class='lista_listo clearfix'>";
    htmls+="<li class='col-sm-6'><span class='titulo'><strong>Costo total</strong><br/>para el empleador</span> <span class='cifra'>"+getPrice(Math.floor(data.totalExpenses))+"</span></li>";
    htmls+="<li class='col-sm-6'><span class='titulo'><strong>Ingreso neto</strong><br />para el empleado</span> <span class='cifra'>"+getPrice(Math.floor(data.totalIncome))+"</span></li>";
    htmls+="<li class='col-sm-6'><span class='cifra'>"+getPrice(Math.floor(data.dailyExpenses))+"</span></li>";
    htmls+="<li class='col-sm-6'><span class='cifra'>"+getPrice(Math.floor(data.dailyIncome))+"</span></li>";
    htmls+="</ul>";
    htmls+="<h2 class='modal-title'>Detalles:</h2>" +
        "<ul class='lista_listo_detalle'>";
    htmls+="<li>Gastos Empleador EPS: "+getPrice(Math.floor(data.EPSEmployerCal))+"</li>";
    htmls+="<li>Gastos Empleador Pensión: "+getPrice(Math.floor(data.PensEmployerCal))+"</li>";
    htmls+="<li>Gastos Empleado ARL: "+getPrice(Math.floor(data.arlCal))+"</li>";
    htmls+="<li>Gastos Empleado Cesantias: "+getPrice(Math.floor(data.cesCal))+"</li>";
    htmls+="<li>Gastos Empleado Intereses/cesantias: "+getPrice(Math.floor(data.taxCesCal))+"</li>";
    htmls+="<li>Gastos Empleado Caja Comp: "+getPrice(Math.floor(data.cajaCal))+"</li>";
    htmls+="<li>Gastos Empleado Vacaciones: "+getPrice(Math.floor(data.vacationsCal))+"</li>";
    htmls+="<li>Gastos Auxilio de Trasnporte: "+getPrice(Math.floor(data.transportCal))+"</li>";
    htmls+="<li>Gastos Dotacion/150000 pesos trimestre: "+getPrice(Math.floor(data.dotationCal))+"</li>";
    htmls+="<li>Gastos Empleado EPS: "+getPrice(Math.floor(data.EPSEmployeeCal))+"</li>";
    htmls+="<li>Gastos Empleado Pensión: "+getPrice(Math.floor(data.PensEmployeeCal))+"</li>";
    htmls+="</ul>";
    return htmls;
}


function getPrice(valor) {
    price = parseFloat(valor.toString().replace(/,/g, ""))
            .toFixed(0)
            .toString()
            .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return "$ " + price;
}


function changeValues(data) {
    // We call calculator to have everything fresh.
    var division = 1;

    if($("input[name='form[tipo]']:checked").val() == "days") {
        division = data.numberOfDays;
    }else {
        division = 1;
    }

    // Plain salary is what the employee should recieve.
    var salario_bruto = Math.round((data.plainSalary - data.transportCal)/0.92);
    var total_modal = data.plainSalary + data.transportCal + data.EPSEmployerCal + data.PensEmployerCal + data.cajaCal + data.arlCal;
    var pagos_netos = (Math.round(data.plainSalary) + Math.round(data.transportCal)) - (Math.round(data.EPSEmployeeCal) + Math.round(data.PensEmployeeCal));
    var total_prestaciones = Math.round(data.cesCal + data.taxCesCal + data.vacationsCal + data.primaCal);

    document.getElementById('salario_ingreso_bruto').innerHTML = getPrice(Math.round(data.plainSalary)/division);
    document.getElementById('subsidio_transporte').innerHTML = getPrice(Math.round(data.transportCal)/division);
    document.getElementById('descuento_salud').innerHTML = getPrice(Math.round(data.EPSEmployeeCal)/division);
    document.getElementById('descuento_pension').innerHTML = getPrice(Math.round(data.PensEmployeeCal)/division);
    document.getElementById('pagos_netos').innerHTML = getPrice(Math.round(pagos_netos/division));

    document.getElementById('salario_ingreso_bruto2').innerHTML = getPrice(Math.round(data.plainSalary)/division);
    document.getElementById('subsidio_transporte2').innerHTML = getPrice(Math.round(data.transportCal)/division);
    document.getElementById('salud_empleador').innerHTML = getPrice(Math.round(data.EPSEmployerCal)/division);
    document.getElementById('pension_empleador').innerHTML = getPrice(Math.round(data.PensEmployerCal)/division);
    document.getElementById('ccf_empleador').innerHTML = getPrice(Math.round(data.cajaCal)/division);
    document.getElementById('arl_empleador').innerHTML = getPrice(Math.round(data.arlCal)/division);
    document.getElementById('costo_total').innerHTML = getPrice(Math.round(total_modal)/division);

    document.getElementById('cesantias').innerHTML = getPrice(Math.round(data.cesCal)/division);
    document.getElementById('int_cesantias').innerHTML = getPrice(Math.round(data.taxCesCal)/division);
    document.getElementById('vacaciones').innerHTML = getPrice(Math.round(data.vacationsCal)/division);
    document.getElementById('prima').innerHTML = getPrice(Math.round(data.primaCal)/division);
    document.getElementById('total_prestaciones').innerHTML = getPrice(total_prestaciones/division);

    sueldo_plano = Math.round(data.plainSalary/data.numberOfDays);

    /*if( radioChange == false ){
        $("#totalExpensesVal").val(getPrice(Math.round(pagos_netos/division)));
        $("#totalExpensesVal2").val(getPrice(Math.round(total_modal)/division));
        $("#totalExpensesValD").val(getPrice(Math.round(data.plainSalary)/division));
    } else {
        radioChange = false;
    }*/


    if($("#totalExpensesVal2").val() == 'NaN')
        $("#totalExpensesVal2").val(getPrice(0));
    if($("#totalExpensesValD").val() == 'NaN')
        $("#totalExpensesValD").val(getPrice(0));
    if($("#totalExpensesVal").val() == 'NaN')
        $("#totalExpensesVal").val(getPrice(0));


}

function setMinimumTC() {
    $("#form_salarioM").val(smmlv);
}

function setMinimumXD() {
    var val = $("#form_numberOfDays").val() + 1;

    $("#form_salarioD").val((smmlv)/30 * val);
}