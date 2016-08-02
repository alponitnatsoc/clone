function changeDays(){

  $("#changeDaysButton").on("click", function(){

    var daysToReport = $("#newDays").val();
    var oldDays = $("#oldDays").text();
    var idEmp = $("#empl_id").val();
    var novId  = $("#novel_id").val();

    if(daysToReport == oldDays){
      return;
    }
    else{
      window.location.href = "/novelty/workableDays/" + idEmp +"/update/" + novId + "/" + daysToReport;
    }
  });
}
