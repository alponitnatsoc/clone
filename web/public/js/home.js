$(document).ready(function(){
    $(".btnBanner").click(function(){
        $("#campanaSlide").animate({left: '0px'}, "slow");
        $("#trigger2, #trigger3").fadeOut();
    });
    $(".cerrarSlide, #campanaBtn").click(function(){
        $("#campanaSlide").animate({left: '-2500px'}, "slow");
        $("#trigger1").fadeIn();
    });
    $("#terminosBtn").click(function(){
        $("#150kCampaign").modal("show");
    });

    $("#esfuerzoTrigg").click(function(){
        $("#trigger1").fadeOut("slow");
        $("#trigger2").delay(1000);
        $("#trigger2").fadeIn("slow");
    });
    $("#sendTrigg").click(function(){
        $("#trigger2").fadeOut("slow");
        $("#trigger3").delay(1000);
        $("#trigger3").fadeIn("slow");
    });
});

$('#pausar').click(function(){
    document.getElementById('video1').pause();
});
$('#video2').click(function(){
    document.getElementById('video1').play();
});

var myVideo = document.getElementById("video1");

function playPause() {
    if (myVideo.paused)
        myVideo.play();
    else
        myVideo.pause();
}
$.getScript("//ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.min.js").done(function () {
    validator = $("form[name='form']").validate({
        rules: {
            "firstname": "required",
            "lastname": "required",
            "cellphone": {required : true, maxlength : 10},
            "email": {required : true, email : true},
            "chktyc": "required"
        },
        messages: {
            "firstname": "&#8227; El nombre no puede estar vacio",
            "lastname": "&#8227; Los apellidos no pueden estar vacios",
            "cellphone": {required : "&#8227; El celular no puede estar vacio", maxlength : "&#8227; El celular no es válido"},
            "email": {required : "&#8227; El email no puede estar vacio", email : "&#8227; El email no es válido"},
            "chktyc": "&#8227; Acepta los Términos y Condiciones para continuar"
        },
        errorElement : 'div',
        errorLabelContainer: '.errorTxt'
    });
});


$(".ex8").slider({
    tooltip: 'always'
});
$('#formfirstname').attr('oninvalid', 'setCustomValidity("El campo no puede estar vacio")');
$('#formfirstname').attr('oninput', "setCustomValidity('')");

$('#formlastname').attr('oninvalid', 'setCustomValidity("El campo no puede estar vacio")');
$('#formlastname').attr('oninput', "setCustomValidity('')");

$('#formcellphone').attr('oninvalid', 'setCustomValidity("El campo no puede estar vacio")');
$('#formcellphone').attr('oninput', "setCustomValidity('')");

$('#formemail').attr('oninvalid', 'setCustomValidity("El correo escrito no es valido")');
$('#formemail').attr('oninput', "setCustomValidity('')");

// var counter = 0;
// var interval = setInterval(function() {
// 	counter++;
// 	if (counter == 10) {
// 		$("#150kCampaign").modal("show");
// 		clearInterval(interval);
// 	}
// }, 1000);


$("form[name='form']").on('submit',function (e) {
    if (document.getElementById('formfirstname').value!= null){
        var nombre = document.getElementById('formfirstname').value;
    }else{
        var nombre = "anonymous";
    }
    if (document.getElementById('formlastname').value != null){
        nombre +=' '+document.getElementById('formlastname').value;
    }
    if (document.getElementById('formcellphone').value != null){
        var telefono = document.getElementById('formcellphone').value;
    }else{
        var telefono = "null";
    }
    if (document.getElementById('formemail').value != null){
        var email = document.getElementById('formemail').value;
    }else{
        var email = "ninguno";
    }
    var dateNow = new Date();
    var day = dateNow.getDate();
    if(day<10)
        day='0'+day;
    var month = dateNow.getMonth()+1;
    if (month<10)
        month='0'+month;
    var year = dateNow.getFullYear();
    var hour = dateNow.getHours();
    var min = dateNow.getMinutes();
    var fecha = day+"/"+month+"/"+year+" "+hour+":"+min;
    console.log(fecha);
    fbq('track', 'Lead', {value: email});
    window.Intercom("trackEvent", "Intento Registro Landing",{
        "Fecha del intento": fecha,
        "Nombre en formulario":nombre,
        "Celular en formulario":telefono,
        "Email en formulario":email,
    });

});

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

var conta1 = 0;
var conta2 = 0;
var conta3 = 0;
var slider1 = $("#ex1Slider" ).slider({
    value: 0,
    min: 0,
    max: 5,
});
/*slider1.on("change", function() {
 conta1 = $('#ex1Slider').val();
 $("#emp1").text(conta1);
 funcion();
 if(conta1 != 1)
 $("#palabra-empleados1").text("empleados");
 else
 $("#palabra-empleados1").text("empleado");
 });*/

/*slider1.on("slide", function(slideEvt) {
 //console.log($('#ex1Slider').val());
 //console.log(conta1);
 conta1 = $('#ex1Slider').val();
 $("#emp1").text(conta1);
 funcion();
 if(conta1 != 1)
 $("#palabra-empleados1").text("empleados");
 else
 $("#palabra-empleados1").text("empleado");
 });*/
var slider2 = $("#ex2Slider" ).slider({
    value: 0,
    min: 0,
    max: 5,
});

/*slider2.on("change", function() {
 conta2 = $('#ex2Slider').val();
 $("#emp2").text(conta2);
 funcion();
 if(conta2 != 1)
 $("#palabra-empleados2").text("empleados");
 else
 $("#palabra-empleados2").text("empleado");
 });*/

/* slider2.on("slide", function(slideEvt) {
 conta2 = $('#ex2Slider').val();
 $("#emp2").text(conta2);
 funcion();
 if(conta2 != 1)
 $("#palabra-empleados2").text("empleados");
 else
 $("#palabra-empleados2").text("empleado");
 });*/
//var slider3 = new Slider("#ex3Slider");
var slider3 = $("#ex3Slider" ).slider({
    value: 0,
    min: 0,
    max: 5,
});

/*slider3.on("change", function() {
 conta3 = $('#ex3Slider').val();
 $("#emp3").text(conta3);
 funcion();
 if(conta3 != 1)
 $("#palabra-empleados3").text("empleados");
 else
 $("#palabra-empleados3").text("empleado");
 });*/

/*slider3.on("slide", function(slideEvt) {
 conta3 = $('#ex3Slider').val();
 $("#emp3").text(conta3);
 funcion();
 if(conta3 != 1)
 $("#palabra-empleados3").text("empleados");
 else
 $("#palabra-empleados3").text("empleado");
 });*/
function funcion(){
    var valor = (conta1 * 29500) + (conta2 * 22500) + (conta3 * 16500);
    if((conta1 + conta2 + conta3) >= 4 )
        valor = valor * 0.9;
    $("#total-empleados").text(conta1 + conta2 + conta3);
    $("#precio").text(" = $"+numberWithCommas(valor) + " mensuales");
    //$("#total-empleados").text(conta1 + conta2 + conta3);
    if((conta1 + conta2 + conta3) != 1)
        $("#palabra-empleados").text("empleados");
    else
        $("#palabra-empleados").text("empleado");

}

function stopVideo(){
    $('#mainVideoFrame').attr('src', $('#mainVideoFrame').attr('src'));
}
