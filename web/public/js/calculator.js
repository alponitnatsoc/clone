
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
function loadConstrains(){
    var constraints=null;
    $.ajax({
        url : "/api/public/v1/calculator/constraints",
        type: "GET",
        statusCode:{
            200: function(data){
                //Extract Constraints
                constraints= data["response"];
                transportAid=parseFloat(constraints['auxilio transporte']);
                smmlv=parseFloat(constraints['smmlv']);
                EPSEmployer=parseFloat(constraints['eps empleador']);
                EPSEmployee=parseFloat(constraints['eps empleado']);
                PensEmployer=parseFloat(constraints['pension empleador']);
                PensEmployee=parseFloat(constraints['pension empleado']);
                arl=parseFloat(constraints['arl']);
                caja=parseFloat(constraints['caja']);
                sena=parseFloat(constraints['sena']);
                icbf=parseFloat(constraints['icbf']);
                vacations=parseFloat(constraints['vacaciones']);
                taxCes=parseFloat(constraints['intereses cesantias']);
                ces=parseFloat(constraints['cesantias']);
                dotation=parseFloat(constraints['dotacion']);
                transportAidDaily=transportAid/30;
                vacations30D=vacations/30;
                dotationDaily=dotation/30;
                calculator();
            }
        }
    });

}
function calculatorCalculate(){
    addListenersCalc();
    loadConstrains();
    $("form").on("submit",function(e){
        e.preventDefault();
        calculator();
        $("#calculatorResultsModal").modal('toggle');
    });
}
function calculator(){
    var type=$("input[name='form[tipo]']:checked").val();
    var salaryM=parseFloat(accounting.unformat($("#form_salarioM").val()));
    var salaryD=parseFloat(accounting.unformat($("#form_salarioD").val()));
    var numberOfDays=$("#form_numberOfDays").val();
    var aid=$("input[name='form[auxilio]']:checked").val();
    var aidD=parseFloat(accounting.unformat($("#form_salarioM").val()));
    var sisben=$("input[name='form[sisben]']:checked").val();
    var transport=$("input[name='form[transporte]']:checked").val();
    var totalExpenses=0;
    var totalIncome=0;
    var EPSEmployerCal=0;
    var EPSEmployeeCal=0;
    var PensEmployeeCal=0;
    var PensEmployerCal=0;
    var transportCal=0;
    var cesCal=0;
    var taxCesCal=0;
    var dotationCal=0;
    var vacationsCal=0;
    var arlCal=0;
    var cajaCal=0;
    var senaCal=0;
    var icbfCal=0;
    var base=0;
    if(aid==0){
        aidD=0;
    }else{
        aidD=aidD/12;
    }
    if(type=="days"){
        if(transport==1){
            salaryD-=transportAidDaily;
        }
        //if it overpass the SMMLV calculates as a full time job  or
        //if does not belongs to SISBEN
        if(((salaryD+transportAidDaily+aidD)*numberOfDays)>smmlv||sisben==0){
            if(((salaryD+transportAidDaily+aidD)*numberOfDays)>smmlv){
                base=(salaryD+aidD)*numberOfDays;
            }else{base=smmlv;}

            totalExpenses=((salaryD+aidD+transportAidDaily+dotationDaily)*numberOfDays)+((EPSEmployer+PensEmployer+arl+caja+sena+icbf)*base)+(vacations30D*numberOfDays*salaryD)+((taxCes+ces)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            EPSEmployerCal=EPSEmployer*base;
            EPSEmployeeCal=EPSEmployee*base;
            PensEmployerCal=PensEmployer*base;
            PensEmployeeCal=PensEmployee*base;
            arlCal=arl*base;
            cesCal=((ces)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            taxCesCal=((taxCes)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            cajaCal=caja*base;
            vacationsCal=vacations30D*numberOfDays*salaryD;
            transportCal=transportAidDaily*numberOfDays;
            dotationCal=dotationDaily*numberOfDays;
            senaCal=sena*base;
            icbfCal=icbf*base;
            totalIncome=(salaryD*numberOfDays)-EPSEmployerCal-PensEmployerCal;
        }else{
            EPSEmployee=0;
            EPSEmployer=0;
            base=smmlv;
            //calculate the caja and pens in base of worked days
            if(numberOfDays<=7){
                PensEmployerCal=PensEmployer*base/4;
                PensEmployeeCal=PensEmployee*base/4;
                cajaCal=caja*base;
            }else if(numberOfDays<=14){
                PensEmployerCal=PensEmployer*base/2;
                PensEmployeeCal=PensEmployee*base/2;
                cajaCal=caja*base/2;
            }else if(numberOfDays<=21){
                PensEmployerCal=PensEmployer*base*3/4;
                PensEmployeeCal=PensEmployee*base*3/4;
                cajaCal=caja*base*3/4;
            }else {
                PensEmployerCal=PensEmployer*base;
                PensEmployeeCal=PensEmployee*base;
                cajaCal=caja*base;
            }
            //then calculate arl ces and the rest
            totalExpenses=((salaryD+aidD+transportAidDaily+dotationDaily)*numberOfDays)+((EPSEmployer+arl+sena+icbf)*base)+(vacations30D*numberOfDays*salaryD)+((taxCes+ces)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid))+PensEmployeeCal+cajaCal+PensEmployerCal;
            EPSEmployerCal=EPSEmployer*base;
            EPSEmployeeCal=EPSEmployee*base;
            arlCal=arl*base;
            cesCal=((ces)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            taxCesCal=((taxCes)*(((salaryD+aidD)*numberOfDays*30/28)+transportAid));
            vacationsCal=vacations30D*numberOfDays*salaryD;
            transportCal=transportAidDaily*numberOfDays;
            dotationCal=dotationDaily*numberOfDays;
            senaCal=sena*base;
            icbfCal=icbf*base;
            totalIncome=((salaryD+transportAidDaily)*numberOfDays)-PensEmployeeCal;
        }

    }else{
        if(transport==1){
            salaryM-=transportAid;
        }else if(salaryM+aidD>smmlv*2){
            transportAid=0;
        }
        numberOfDays=30;
        totalExpenses=salaryM+aidD+transportAid+dotation+((EPSEmployer+PensEmployer+arl+caja+vacations30D+sena+icbf)*(salaryM+aidD))+((taxCes+ces)*(salaryM+aidD+transportAid));
        EPSEmployerCal=EPSEmployer*(salaryM+aidD);
        EPSEmployeeCal=EPSEmployee*(salaryM+aidD);
        PensEmployerCal=PensEmployer*(salaryM+aidD);
        PensEmployeeCal=PensEmployee*(salaryM+aidD);
        arlCal=arl*(salaryM+aidD);
        cesCal=ces*(salaryM+aidD+transportAid);
        taxCesCal=taxCes*(salaryM+aidD+transportAid);
        cajaCal=caja*(salaryM+aidD);
        vacationsCal=vacations30D*(salaryM+aidD);
        transportCal=transportAid;
        dotationCal=dotation;
        senaCal=sena*(salaryM+aidD);
        icbfCal=icbf*(salaryM+aidD);
        totalIncome=(salaryM+transportCal-EPSEmployerCal-PensEmployerCal);

    }

    if(salaryD==0&&salaryM==0){

        totalExpenses=0;
        totalIncome=0;
        EPSEmployerCal=0;
        EPSEmployeeCal=0;
        PensEmployeeCal=0;
        PensEmployerCal=0;
        transportCal=0;
        cesCal=0;
        taxCesCal=0;
        dotationCal=0;
        vacationsCal=0;
        arlCal=0;
        cajaCal=0;
        senaCal=0;
        icbfCal=0;
        base=0;
    }
    var resposne=[];
    resposne['totalExpenses']=totalExpenses;
    resposne['dailyExpenses']=totalExpenses/numberOfDays;
    resposne['dailyIncome']=totalIncome/numberOfDays;
    resposne['EPSEmployerCal']=EPSEmployerCal;
    resposne['EPSEmployeeCal']=EPSEmployeeCal;
    resposne['PensEmployerCal']=PensEmployerCal;
    resposne['PensEmployeeCal']=PensEmployeeCal;
    resposne['arlCal']=arlCal;
    resposne['cesCal']=cesCal;
    resposne['taxCesCal']=taxCesCal;
    resposne['cajaCal']=cajaCal;
    resposne['vacationsCal']=vacationsCal;
    resposne['transportCal']=transportCal;
    resposne['dotationCal']=dotationCal;
    resposne['senaCal']=senaCal;
    resposne['icbfCal']=icbfCal;
    resposne['totalIncome']=totalIncome;
    var htmlRes=jsonCalcToHTML(resposne);
    $("#calculatorResultsModal").find(".modal-body").html(htmlRes);

    $("#totalExpensesVal").text(totalExpenses.toFixed(0));

}
function addListenersCalc(){
    $(".all").hide();
    $(".aid").hide();
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
    })
    $("input[name='form[auxilio]']").change(function(){
        var selected=$("input[name='form[auxilio]']:checked").val();
        if(selected==1){
            $(".aid").show();
        }else{
            $(".aid").hide();
        }
    })

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
    htmls+="<li>Gastos SENA: "+getPrice(Math.floor(data.senaCal))+"</li>";
    htmls+="<li>Gastos ICBF: "+getPrice(Math.floor(data.icbfCal))+"</li>";
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
