
/**
 * Created by gabrielsamoma on 11/11/15.
 */
function calculatorCalculate(){
    addListenersCalc();
    $("form").on("submit",function(e){
        e.preventDefault();
        var form =$("form");
        $.ajax({
            url : form.attr('action'),
            type: $(form).attr('method'),
            data: {
                type:           $(form).find("input[name='form[tipo]']:checked").val(),
                salaryM:        $(form).find("input[name='form[salarioM]']").val(),
                salaryD:        $(form).find("input[name='form[salarioD]']").val(),
                numberOfDays:   $(form).find("select[name='form[numberOfDays]']").val(),
                transport:      $(form).find("input[name='form[transporte]']:checked").val(),
                aid:            $(form).find("input[name='form[auxilio]']:checked").val(),
                aidD:           $(form).find("input[name='form[auxilioD]']").val(),
                sisben:         $(form).find("input[name='form[sisben]']:checked").val(),
            },
            statusCode:{
                200: function(data){
                    console.log(data);
                    var htmls=jsonToHTML(data);
                    $("#main").html(htmls);
                },
                400 : function(data, textStatus, errorThrown){
                    alert("400 :"+errorThrown+"\n"+data.responseJSON.error.exception[0].message);
                    console.log(data);
                    console.log(textStatus);
                    console.log(errorThrown);
                }

            }
        });
    });
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

function jsonToHTML(data) {
    var htmls="<h2 class='text-center'>Si su empleado tiene estas características debe pagar:</h2>" +
        "<ul class='list-group'>";
    htmls+="<li class='list-group-item'>Costo total para el empleador: "+data.totalExpenses+"</li>";
    htmls+="<li class='list-group-item'>Ingreso neto para el empleado: "+data.totalIncome+"</li>";
    htmls+="<li class='list-group-item'>Diario Gastos: "+data.totalExpenses/30+"</li>";
    htmls+="<li class='list-group-item'>Diario Ingreso: "+data.totalIncome/30+"</li>";
    htmls+="</ul>";
    htmls+="<h2 class='text-center'>Detalles:</h2>" +
        "<ul class='list-group'>";
    htmls+="<li class='list-group-item'>Gastos Empleador EPS: "+data.EPSEmployerCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleador Pensión: "+data.PensEmployerCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado ARL: "+data.arlCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Cesantias: "+data.cesCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Intereses/cesantias: "+data.taxCesCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Caja Comp: "+data.cajaCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Vacaciones: "+data.vacationsCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Auxilio de Trasnporte: "+data.transportCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Dotacion/150000 pesos trimestre: "+data.dotationCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos SENA: "+data.senaCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos ICBF: "+data.icbfCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado EPS: "+data.EPSEmployeeCal+"</li>";
    htmls+="<li class='list-group-item'>Gastos Empleado Pensión: "+data.PensEmployeeCal+"</li>";
    htmls+="</ul>";
    return htmls;
}
