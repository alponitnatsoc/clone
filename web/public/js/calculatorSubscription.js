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
var total;
var constraints = null;

function loadConstrains() {

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

}

function calculator(type, salaryM, salaryD, numberOfDays, sisben, transport) {
    //console.log("calculator(type, salaryM, salaryD, numberOfDays, sisben, transport) ");
    var type = type;
    var salaryM = salaryM;
    var salaryD = salaryD;
    if (salaryD == "") {
        salaryD = 0;
    }
    if (salaryM == "") {
        salaryM = 0;
    }
    var numberOfDays = numberOfDays;
    var aid = 0;
    var aidD = 0;
    var sisben = sisben;
    var transport = transport;

    var totalExpenses = 0;
    var totalExpenses2 = 0;
    var totalIncome = 0;
    var totalDiscountsForEmployer = 0;
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
    var base = 0;
    if (aid == 0) {
        aidD = 0;
    }
    if (type == "days") {
        transport = 1;
        if (transport == 1) {
            salaryD -= transportAidDaily;
        }
        //if it overpass the SMMLV calculates as a full time job  or
        //if does not belongs to SISBEN
        if (((salaryD + transportAidDaily + aidD) * numberOfDays) > smmlv || sisben == -1) {
            if (((salaryD + transportAidDaily + aidD) * numberOfDays) > smmlv) {
                base = (salaryD + aidD) * numberOfDays;
            } else {
                base = smmlv;
            }

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
            transportCal = transportAidDaily * numberOfDays;
            dotationCal = dotationDaily * numberOfDays;
            senaCal = sena * base;
            icbfCal = icbf * base;
            totalIncome = (salaryD * numberOfDays) - EPSEmployerCal - PensEmployerCal;
            totalDiscountsForEmployer = EPSEmployerCal + PensEmployerCal;
            totalExpenses2 = base + EPSEmployerCal + EPSEmployeeCal + PensEmployerCal + PensEmployeeCal;
        } else {
            var EPSEmployee2 = 0;
            var EPSEmployer2 = 0;
            base = smmlv;
            //calculate the caja and pens in base of worked days
            if (numberOfDays <= 7) {
                PensEmployerCal = PensEmployer * base / 4;
                PensEmployeeCal = PensEmployee * base / 4;
                cajaCal = caja * base;
            } else if (numberOfDays <= 14) {
                PensEmployerCal = PensEmployer * base / 2;
                PensEmployeeCal = PensEmployee * base / 2;
                cajaCal = caja * base / 2;
            } else if (numberOfDays <= 21) {
                PensEmployerCal = PensEmployer * base * 3 / 4;
                PensEmployeeCal = PensEmployee * base * 3 / 4;
                cajaCal = caja * base * 3 / 4;
            } else {
                PensEmployerCal = PensEmployer * base;
                PensEmployeeCal = PensEmployee * base;
                cajaCal = caja * base;
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
            transportCal = transportAidDaily * numberOfDays;
            dotationCal = dotationDaily * numberOfDays;
            senaCal = sena * base;
            icbfCal = icbf * base;
            totalIncome = ((salaryD + transportAidDaily) * numberOfDays) - PensEmployeeCal;
            totalDiscountsForEmployer = PensEmployeeCal;
            totalExpenses2 = base + EPSEmployerCal + EPSEmployeeCal + PensEmployerCal + PensEmployeeCal;
        }

    } else {
        var transportAid2 = 0;
        if (transport == 1) {
            salaryM -= transportAid;
        } else if (salaryM + aidD > smmlv * 2) {
            transportAid2 = 0;
        } else {
            transportAid2 = transportAid;
        }
        base = (salaryM + aidD);
        totalExpenses = salaryM + aidD + transportAid2 + dotation + ((EPSEmployer + PensEmployer + arl + caja +
            vacations30D + sena + icbf) * (salaryM + aidD)) + ((taxCes + ces) * (salaryM + aidD + transportAid2));
        EPSEmployerCal = EPSEmployer * base;
        EPSEmployeeCal = EPSEmployee * base;
        PensEmployerCal = PensEmployer * base;
        PensEmployeeCal = PensEmployee * base;
        arlCal = arl * base;
        cesCal = ces * (base + transportAid2);
        taxCesCal = taxCes * (base + transportAid2);
        cajaCal = caja * base;
        vacationsCal = vacations30D * base;
        transportCal = transportAid2;
        dotationCal = dotation;
        senaCal = sena * base;
        icbfCal = icbf * base;
        totalIncome = (salaryM + transportCal - EPSEmployerCal - PensEmployerCal);
        totalDiscountsForEmployer = EPSEmployerCal + PensEmployerCal;
        totalExpenses2 = base + EPSEmployerCal + EPSEmployeeCal + PensEmployerCal + PensEmployeeCal;

    }
    var resposne = [];

    if ((type == "days" && (salaryD == 0 || numberOfDays == null || numberOfDays == 0)) || (type != "days" && (salaryM <= 0))) {
        totalExpenses = 0;
        resposne['totalExpenses'] = 0;
        resposne['totalExpenses2'] = 0;
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
        resposne['totalDiscountsForEmployer'] = 0;
    } else {
        resposne['totalExpenses'] = totalExpenses;
        resposne['totalExpenses2'] = totalExpenses2;
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
        resposne['totalDiscountsForEmployer']= totalDiscountsForEmployer;
    }
    //console.log('calculator-responce:' + resposne['totalExpenses']);
    //console.log('calculator-responce:' + getPrice(resposne['totalExpenses']));
    //$("#sueldos").html(getPrice(resposne['totalExpenses']));
    return resposne;


}

function getPrice(valor) {
    price = parseFloat(valor.toString().replace(/,/g, ""))
        .toFixed(0)
        .toString()
        .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return "$ " + price;
}
