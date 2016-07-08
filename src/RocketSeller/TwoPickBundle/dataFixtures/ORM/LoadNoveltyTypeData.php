<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\NoveltyType;

class LoadNoveltyTypeData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {

        /* All the entries with type absenteeism */
        $NoveltyMaternity = new NoveltyType();
        $NoveltyMaternity->setName('Licencia de maternidad');
        $NoveltyMaternity->setPayrollCode('25');
        $NoveltyMaternity->setAbsenteeism('1');
        $NoveltyMaternity->setPeriod('dia');
        $NoveltyMaternity->setGrupo('Licencias-Permisos');
        $NoveltyMaternity->setNaturaleza('DEV');
        $manager->persist($NoveltyMaternity);

        $NoveltyUnpaid = new NoveltyType();
        $NoveltyUnpaid->setName('Licencia no remunerada');
        $NoveltyUnpaid->setPayrollCode('3120');
        $NoveltyUnpaid->setAbsenteeism('3');
        $NoveltyUnpaid->setPeriod('dia');
        $NoveltyUnpaid->setGrupo('Licencias-Permisos');
        $NoveltyUnpaid->setNaturaleza('DED');
        $manager->persist($NoveltyUnpaid);

        $NoveltyPaid = new NoveltyType();
        $NoveltyPaid->setName('Licencia remunerada');
        $NoveltyPaid->setPayrollCode('23');
        $NoveltyPaid->setAbsenteeism('5');
        $NoveltyPaid->setPeriod('dia');
        $NoveltyPaid->setGrupo('no_show');
        $NoveltyPaid->setNaturaleza('DEV');
        $manager->persist($NoveltyPaid);

        $NoveltySuspension = new NoveltyType();
        $NoveltySuspension->setName('Suspensión');
        $NoveltySuspension->setPayrollCode('3125');
        $NoveltySuspension->setAbsenteeism('6');
        $NoveltySuspension->setPeriod('dia');
        $NoveltySuspension->setGrupo('Suspensión');
        $NoveltySuspension->setNaturaleza('DED');
        $manager->persist($NoveltySuspension);

        $NoveltyGeneralIllness = new NoveltyType();
        $NoveltyGeneralIllness->setName('Incapacidad general');
        $NoveltyGeneralIllness->setPayrollCode('15');
        $NoveltyGeneralIllness->setAbsenteeism('7');
        $NoveltyGeneralIllness->setPeriod('dia');
        $NoveltyGeneralIllness->setGrupo('Incapacidad');
        $NoveltyGeneralIllness->setNaturaleza('DEV');
        $manager->persist($NoveltyGeneralIllness);

        $NoveltyWorkAccident = new NoveltyType();
        $NoveltyWorkAccident->setName('Accidente de trabajo');
        $NoveltyWorkAccident->setPayrollCode('27');
        $NoveltyWorkAccident->setAbsenteeism('8');
        $NoveltyWorkAccident->setPeriod('dia');
        $NoveltyWorkAccident->setGrupo('no_show');
        $NoveltyWorkAccident->setNaturaleza('DEV');
        $manager->persist($NoveltyWorkAccident);

        $NoveltyProfessionalIllness = new NoveltyType();
        $NoveltyProfessionalIllness->setName('Incapacidad profesional');
        $NoveltyProfessionalIllness->setPayrollCode('28');
        $NoveltyProfessionalIllness->setAbsenteeism('9');
        $NoveltyProfessionalIllness->setPeriod('dia');
        $NoveltyProfessionalIllness->setGrupo('Incapacidad');
        $NoveltyProfessionalIllness->setNaturaleza('DEV');
        $manager->persist($NoveltyProfessionalIllness);

        $NoveltyPaternityLeave = new NoveltyType();
        $NoveltyPaternityLeave->setName('Licencia de paternidad');
        $NoveltyPaternityLeave->setPayrollCode('26');
        $NoveltyPaternityLeave->setAbsenteeism('10');
        $NoveltyPaternityLeave->setPeriod('dia');
        $NoveltyPaternityLeave->setGrupo('Licencias-Permisos');
        $NoveltyPaternityLeave->setNaturaleza('DEV');
        $manager->persist($NoveltyPaternityLeave);

        /* All the entries with type Novelty */
        $NoveltySalaryAdjust = new NoveltyType();
        $NoveltySalaryAdjust->setName('Aumento de sueldo');
        $NoveltySalaryAdjust->setPayrollCode('2');
        $NoveltySalaryAdjust->setPeriod('dia');
        $NoveltySalaryAdjust->setGrupo('Ajuste Salarial');
        $NoveltySalaryAdjust->setNaturaleza('DEV');
        $manager->persist($NoveltySalaryAdjust);

        $NoveltyBonus = new NoveltyType();
        $NoveltyBonus->setName('Bonificación Hidden');
        $NoveltyBonus->setPayrollCode('30');
        $NoveltyBonus->setPeriod('unidad');
        $NoveltyBonus->setGrupo('no_show');
        $NoveltyBonus->setNaturaleza('DEV');
        $manager->persist($NoveltyBonus);

        $NoveltyNightCharge = new NoveltyType();
        $NoveltyNightCharge->setName('Recargo nocturno');
        $NoveltyNightCharge->setPayrollCode('45');
        $NoveltyNightCharge->setPeriod('hora');
        $NoveltyNightCharge->setGrupo('no_show');
        $NoveltyNightCharge->setNaturaleza('DEV');
        $manager->persist($NoveltyNightCharge);

        $NoveltyNightHolidayCharge = new NoveltyType();
        $NoveltyNightHolidayCharge->setName('Recargo nocturno festivo');
        $NoveltyNightHolidayCharge->setPayrollCode('52');
        $NoveltyNightHolidayCharge->setPeriod('hora');
        $NoveltyNightHolidayCharge->setGrupo('no_show');
        $NoveltyNightHolidayCharge->setNaturaleza('DEV');
        $manager->persist($NoveltyNightHolidayCharge);

        $NoveltyExtraHour = new NoveltyType();
        $NoveltyExtraHour->setName('Hora extra diurna');
        $NoveltyExtraHour->setPayrollCode('55');
        $NoveltyExtraHour->setPeriod('hora');
        $NoveltyExtraHour->setGrupo('Horas extras');
        $NoveltyExtraHour->setNaturaleza('DEV');
        $manager->persist($NoveltyExtraHour);

        $NoveltyExtraHourNight = new NoveltyType();
        $NoveltyExtraHourNight->setName('Hora extra nocturna');
        $NoveltyExtraHourNight->setPayrollCode('60');
        $NoveltyExtraHourNight->setPeriod('hora');
        $NoveltyExtraHourNight->setGrupo('Horas extras');
        $NoveltyExtraHourNight->setNaturaleza('DEV');
        $manager->persist($NoveltyExtraHourNight);

        $NoveltyExtraHourHoliday = new NoveltyType();
        $NoveltyExtraHourHoliday->setName('Hora extra festiva diurna');
        $NoveltyExtraHourHoliday->setPayrollCode('65');
        $NoveltyExtraHourHoliday->setPeriod('hora');
        $NoveltyExtraHourHoliday->setGrupo('Horas extras');
        $NoveltyExtraHourHoliday->setNaturaleza('DEV');
        $manager->persist($NoveltyExtraHourHoliday);

        $NoveltyHoliday = new NoveltyType();
        $NoveltyHoliday->setName('Festivo diurno');
        $NoveltyHoliday->setPayrollCode('66');
        $NoveltyHoliday->setPeriod('hora');
        $NoveltyHoliday->setGrupo('no_show');
        $NoveltyHoliday->setNaturaleza('DEV');
        $manager->persist($NoveltyHoliday);

        $NoveltyHolidayNightExtraHour = new NoveltyType();
        $NoveltyHolidayNightExtraHour->setName('Hora extra festiva nocturna');
        $NoveltyHolidayNightExtraHour->setPayrollCode('70');
        $NoveltyHolidayNightExtraHour->setPeriod('hora');
        $NoveltyHolidayNightExtraHour->setGrupo('Horas extras');
        $NoveltyHolidayNightExtraHour->setNaturaleza('DEV');
        $manager->persist($NoveltyHolidayNightExtraHour);

        $NoveltyTransport = new NoveltyType();
        $NoveltyTransport->setName('Subsidio de transporte');
        $NoveltyTransport->setPayrollCode('120');
        $NoveltyTransport->setPeriod('dia');
        $NoveltyTransport->setGrupo('no_show');
        $NoveltyTransport->setNaturaleza('DEV');
        $manager->persist($NoveltyTransport);

        $NoveltyVacation = new NoveltyType();
        $NoveltyVacation->setName('Vacaciones');
        $NoveltyVacation->setPayrollCode('145');
        $NoveltyVacation->setPeriod('dia');
        $NoveltyVacation->setGrupo('Vacaciones');
        $NoveltyVacation->setNaturaleza('DEV');
        $manager->persist($NoveltyVacation);

        $NoveltyVacationMoney = new NoveltyType();
        $NoveltyVacationMoney->setName('Vacaciones en dinero');
        $NoveltyVacationMoney->setPayrollCode('150');
        $NoveltyVacationMoney->setPeriod('dia');
        $NoveltyVacationMoney->setGrupo('no_show');
        $NoveltyVacationMoney->setNaturaleza('DEV');
        $manager->persist($NoveltyVacationMoney);

        $NoveltyLibertyBonus = new NoveltyType();
        $NoveltyLibertyBonus->setName('Bonificación');
        $NoveltyLibertyBonus->setPayrollCode('285');
        $NoveltyLibertyBonus->setPeriod('unidad');
        $NoveltyLibertyBonus->setGrupo('Bonificación');
        $NoveltyLibertyBonus->setNaturaleza('DEV');
        $manager->persist($NoveltyLibertyBonus);

        $NoveltyDiscountLoan = new NoveltyType();
        $NoveltyDiscountLoan->setName('Prestamo');
        $NoveltyDiscountLoan->setPayrollCode('4810');
        $NoveltyDiscountLoan->setPeriod('unidad');
        $NoveltyDiscountLoan->setGrupo('Prestamos-Anticipos');
        $NoveltyDiscountLoan->setNaturaleza('DED');
        $manager->persist($NoveltyDiscountLoan);

        // Entities that shouldn't be inserted, only for query.
        $NoveltyHealth = new NoveltyType();
        $NoveltyHealth->setName('Aporte salud');
        $NoveltyHealth->setPayrollCode('3010');
        $NoveltyHealth->setPeriod('%');
        $NoveltyHealth->setGrupo('no_show');
        $NoveltyHealth->setNaturaleza('DED');
        $manager->persist($NoveltyHealth);

        $NoveltyPension = new NoveltyType();
        $NoveltyPension->setName('Aporte pensión');
        $NoveltyPension->setPayrollCode('3020');
        $NoveltyPension->setPeriod('%');
        $NoveltyPension->setGrupo('no_show');
        $NoveltyPension->setNaturaleza('DED');
        $manager->persist($NoveltyPension);

        $NoveltyLegalBounty = new NoveltyType();
        $NoveltyLegalBounty->setName('Prima legal');
        $NoveltyLegalBounty->setPayrollCode('130');
        $NoveltyLegalBounty->setPeriod('dia');
        $NoveltyLegalBounty->setGrupo('no_show');
        $NoveltyLegalBounty->setNaturaleza('DEV');
        $manager->persist($NoveltyLegalBounty);

        $NoveltySeverance = new NoveltyType();
        $NoveltySeverance->setName('Cesantias definitivas');
        $NoveltySeverance->setPayrollCode('185');
        $NoveltySeverance->setPeriod('dia');
        $NoveltySeverance->setGrupo('no_show');
        $NoveltySeverance->setNaturaleza('DEV');
        $manager->persist($NoveltySeverance);

        $NoveltySeveranceInterests = new NoveltyType();
        $NoveltySeveranceInterests->setName('Intereses sobre cesantias');
        $NoveltySeveranceInterests->setPayrollCode('190');
        $NoveltySeveranceInterests->setPeriod('dia');
        $NoveltySeveranceInterests->setGrupo('no_show');
        $NoveltySeveranceInterests->setNaturaleza('DEV');
        $manager->persist($NoveltySeveranceInterests);

        $NoveltyCompensation = new NoveltyType();
        $NoveltyCompensation->setName('Indemnización');
        $NoveltyCompensation->setPayrollCode('195');
        $NoveltyCompensation->setPeriod('dia');
        $NoveltyCompensation->setGrupo('no_show');
        $NoveltyCompensation->setNaturaleza('DEV');
        $manager->persist($NoveltyCompensation);

        $NoveltySalary = new NoveltyType();
        $NoveltySalary->setName('Sueldo');
        $NoveltySalary->setPayrollCode('1');
        $NoveltySalary->setPeriod('dia');
        $NoveltySalary->setGrupo('no_show');
        $NoveltySalary->setNaturaleza('DEV');
        $manager->persist($NoveltySalary);

        $NoveltyInabilitySpending = new NoveltyType();
        $NoveltyInabilitySpending->setName('Gasto de incapacidad');
        $NoveltyInabilitySpending->setPayrollCode('20');
        $NoveltyInabilitySpending->setPeriod('dia');
        $NoveltyInabilitySpending->setGrupo('no_show');
        $NoveltyInabilitySpending->setNaturaleza('DEV');
        $manager->persist($NoveltyInabilitySpending);

        $NoveltyInabilityAdjust = new NoveltyType();
        $NoveltyInabilityAdjust->setName('Ajuste de incapacidad');
        $NoveltyInabilityAdjust->setPayrollCode('21');
        $NoveltyInabilityAdjust->setPeriod('dia');
        $NoveltyInabilityAdjust->setGrupo('no_show');
        $NoveltyInabilityAdjust->setNaturaleza('DEV');
        $manager->persist($NoveltyInabilityAdjust);

        $NoveltyRetention = new NoveltyType();
        $NoveltyRetention->setName('Retención en la fuente');
        $NoveltyRetention->setPayrollCode('3005');
        $NoveltyRetention->setPeriod('dia');
        $NoveltyRetention->setGrupo('no_show');
        $NoveltyRetention->setNaturaleza('DED');
        $manager->persist($NoveltyRetention);

        // This are novelties that are not in the SQL DB.

        $llegadaTarde = new NoveltyType();
        $llegadaTarde->setName('Llegada tarde');
        $llegadaTarde->setGrupo('no_show');
        $manager->persist($llegadaTarde);

        $abandonoPuesto = new NoveltyType();
        $abandonoPuesto->setName('Abandono puesto de trabajo');
        $abandonoPuesto->setGrupo('no_show');
        $manager->persist($abandonoPuesto);

        $descargos = new NoveltyType();
        $descargos->setName('Versión libre de hechos');
        $descargos->setGrupo('no_show');
        $manager->persist($descargos);

        $dotacion = new NoveltyType();
        $dotacion->setName('Dotación');
        $dotacion->setGrupo('no_show');
        $manager->persist($dotacion);

        $despido = new NoveltyType();
        $despido->setName('Terminar contrato');
        $despido->setGrupo('Terminación contrato');
        $manager->persist($despido);

        $anticip = new NoveltyType();
        $anticip->setName('Anticipo');
        $anticip->setPayrollCode('4810');
        $anticip->setPeriod('unidad');
        $anticip->setGrupo('Prestamos-Anticipos');
        $anticip->setNaturaleza('DED');
        $manager->persist($anticip);

        $manager->flush();

        // Abstenteeisms.
        $this->addReference('novelty-maternity', $NoveltyMaternity);
        $this->addReference('novelty-unpaid', $NoveltyUnpaid);
        $this->addReference('novelty-paid', $NoveltyPaid);
        $this->addReference('novelty-suspension', $NoveltySuspension);
        $this->addReference('novelty-general-illness', $NoveltyGeneralIllness);
        $this->addReference('novelty-work-accident', $NoveltyWorkAccident);
        $this->addReference('novelty-professional-illness', $NoveltyProfessionalIllness);
        $this->addReference('novelty-paternity-leave', $NoveltyPaternityLeave);

        // Nolvelty.
        $this->addReference('novelty-salary-adjust', $NoveltySalaryAdjust);
        $this->addReference('novelty-bonus', $NoveltyBonus);
        $this->addReference('novelty-night-charge', $NoveltyNightCharge);
        $this->addReference('novelty-night-holiday-charge', $NoveltyNightHolidayCharge);
        $this->addReference('novelty-extra-hour', $NoveltyExtraHour);
        $this->addReference('novelty-extra-hour-night', $NoveltyExtraHourNight);
        $this->addReference('novelty-extra-hour-holiday', $NoveltyExtraHourHoliday);
        $this->addReference('novelty-holiday', $NoveltyHoliday);
        $this->addReference('novelty-holiday-night-extra-hour', $NoveltyHolidayNightExtraHour);
        $this->addReference('novelty-transport', $NoveltyTransport);
        $this->addReference('novelty-vacation', $NoveltyVacation);
        $this->addReference('novelty-vacation-money', $NoveltyVacationMoney);
        $this->addReference('novelty-liberty-bonus', $NoveltyLibertyBonus);
        $this->addReference('novelty-discount-loan', $NoveltyDiscountLoan);

        // No show.
        $this->addReference('novelty-health', $NoveltyHealth);
        $this->addReference('novelty-pension', $NoveltyPension);
        $this->addReference('novelty-legal-bounty', $NoveltyLegalBounty);
        $this->addReference('novelty-severance', $NoveltySeverance);
        $this->addReference('novelty-severance-interests', $NoveltySeveranceInterests);
        $this->addReference('novelty-compensation', $NoveltyCompensation);
        $this->addReference('novelty-salary', $NoveltySalary);
        $this->addReference('novelty-inability-spending', $NoveltyInabilitySpending);
        $this->addReference('novelty-inability-adjust', $NoveltyInabilityAdjust);
        $this->addReference('novelty-retention', $NoveltyRetention);

        //llamado_atencion
        $this->addReference('novelty-llegada-tarde', $llegadaTarde);
        $this->addReference('novelty-abandono-puesto', $abandonoPuesto);
        $this->addReference('novelty-descargos', $descargos);
        $this->addReference('novelty-dotacion', $dotacion);
        $this->addReference('novelty-despido', $despido);
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 9;
    }

}
