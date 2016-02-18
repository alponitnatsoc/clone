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
        $NoveltyMaternity->setGrupo('Licencia');
        $NoveltyMaternity->setNaturaleza('DEV');
        $manager->persist($NoveltyMaternity);

        $NoveltyUnpaid = new NoveltyType();
        $NoveltyUnpaid->setName('Licencia no remunerada');
        $NoveltyUnpaid->setPayrollCode('3120');
        $NoveltyUnpaid->setAbsenteeism('3');
        $NoveltyUnpaid->setPeriod('dia');
        $NoveltyUnpaid->setGrupo('Licencia');
        $NoveltyUnpaid->setNaturaleza('DED');
        $manager->persist($NoveltyUnpaid);

        $NoveltyPaid = new NoveltyType();
        $NoveltyPaid->setName('Licencia remunerada');
        $NoveltyPaid->setPayrollCode('23');
        $NoveltyPaid->setAbsenteeism('5');
        $NoveltyPaid->setPeriod('dia');
        $NoveltyPaid->setGrupo('Licencia');
        $NoveltyPaid->setNaturaleza('DEV');
        $manager->persist($NoveltyPaid);

        $NoveltySuspension = new NoveltyType();
        $NoveltySuspension->setName('Suspension');
        $NoveltySuspension->setPayrollCode('3125');
        $NoveltySuspension->setAbsenteeism('6');
        $NoveltySuspension->setPeriod('dia');
        $NoveltySuspension->setGrupo('Suspension');
        $NoveltySuspension->setNaturaleza('DED');
        $manager->persist($NoveltySuspension);

        $NoveltyGeneralIllness = new NoveltyType();
        $NoveltyGeneralIllness->setName('Enfermedad general');
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
        $NoveltyWorkAccident->setGrupo('Incapacidad');
        $NoveltyWorkAccident->setNaturaleza('DEV');
        $manager->persist($NoveltyWorkAccident);

        $NoveltyProfessionalIllness = new NoveltyType();
        $NoveltyProfessionalIllness->setName('Enfermedad profesional');
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
        $NoveltyPaternityLeave->setGrupo('Licencia');
        $NoveltyPaternityLeave->setNaturaleza('DEV');
        $manager->persist($NoveltyPaternityLeave);

        /* All the entries with type Novelty */
        $NoveltySalaryAdjust = new NoveltyType();
        $NoveltySalaryAdjust->setName('Ajuste sueldo');
        $NoveltySalaryAdjust->setPayrollCode('2');
        $NoveltySalaryAdjust->setPeriod('dia');
        $NoveltySalaryAdjust->setGrupo('Ajuste');
        $NoveltySalaryAdjust->setNaturaleza('DEV');
        $manager->persist($NoveltySalaryAdjust);

        $NoveltyBonus = new NoveltyType();
        $NoveltyBonus->setName('Bonificación');
        $NoveltyBonus->setPayrollCode('30');
        $NoveltyBonus->setPeriod('unidad');
        $NoveltyBonus->setGrupo('Ajuste');
        $NoveltyBonus->setNaturaleza('DEV');
        $manager->persist($NoveltyBonus);

        $NoveltyNightCharge = new NoveltyType();
        $NoveltyNightCharge->setName('Recargo nocturno');
        $NoveltyNightCharge->setPayrollCode('45');
        $NoveltyNightCharge->setPeriod('hora');
        $NoveltyNightCharge->setGrupo('Tiempo');
        $NoveltyNightCharge->setNaturaleza('DEV');
        $manager->persist($NoveltyNightCharge);

        $NoveltyNightHolidayCharge = new NoveltyType();
        $NoveltyNightHolidayCharge->setName('Recargo nocturno festivo');
        $NoveltyNightHolidayCharge->setPayrollCode('52');
        $NoveltyNightHolidayCharge->setPeriod('hora');
        $NoveltyNightHolidayCharge->setGrupo('Tiempo');
        $NoveltyNightHolidayCharge->setNaturaleza('DEV');
        $manager->persist($NoveltyNightHolidayCharge);

        $NoveltyExtraHour = new NoveltyType();
        $NoveltyExtraHour->setName('Hora extra diurna');
        $NoveltyExtraHour->setPayrollCode('55');
        $NoveltyExtraHour->setPeriod('hora');
        $NoveltyExtraHour->setGrupo('Tiempo');
        $NoveltyExtraHour->setNaturaleza('DEV');
        $manager->persist($NoveltyExtraHour);

        $NoveltyExtraHourNight = new NoveltyType();
        $NoveltyExtraHourNight->setName('Hora extra nocturna');
        $NoveltyExtraHourNight->setPayrollCode('60');
        $NoveltyExtraHourNight->setPeriod('hora');
        $NoveltyExtraHourNight->setGrupo('Tiempo');
        $NoveltyExtraHourNight->setNaturaleza('DEV');
        $manager->persist($NoveltyExtraHourNight);

        $NoveltyExtraHourHoliday = new NoveltyType();
        $NoveltyExtraHourHoliday->setName('Hora extra festiva diurna');
        $NoveltyExtraHourHoliday->setPayrollCode('65');
        $NoveltyExtraHourHoliday->setPeriod('hora');
        $NoveltyExtraHourHoliday->setGrupo('Tiempo');
        $NoveltyExtraHourHoliday->setNaturaleza('DEV');
        $manager->persist($NoveltyExtraHourHoliday);

        $NoveltyHoliday = new NoveltyType();
        $NoveltyHoliday->setName('Festivo diurno');
        $NoveltyHoliday->setPayrollCode('66');
        $NoveltyHoliday->setPeriod('hora');
        $NoveltyHoliday->setGrupo('Tiempo');
        $NoveltyHoliday->setNaturaleza('DEV');
        $manager->persist($NoveltyHoliday);

        $NoveltyHolidayNightExtraHour = new NoveltyType();
        $NoveltyHolidayNightExtraHour->setName('Hora extra festiva nocturna');
        $NoveltyHolidayNightExtraHour->setPayrollCode('70');
        $NoveltyHolidayNightExtraHour->setPeriod('hora');
        $NoveltyHolidayNightExtraHour->setGrupo('Tiempo');
        $NoveltyHolidayNightExtraHour->setNaturaleza('DEV');
        $manager->persist($NoveltyHolidayNightExtraHour);

        $NoveltyTransport = new NoveltyType();
        $NoveltyTransport->setName('Subsidio de transporte');
        $NoveltyTransport->setPayrollCode('120');
        $NoveltyTransport->setPeriod('dia');
        $NoveltyTransport->setGrupo('Ajuste');
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
        $NoveltyVacationMoney->setGrupo('Vacaciones');
        $NoveltyVacationMoney->setNaturaleza('DEV');
        $manager->persist($NoveltyVacationMoney);

        $NoveltyLibertyBonus = new NoveltyType();
        $NoveltyLibertyBonus->setName('Bonificacion mera liberalidad');
        $NoveltyLibertyBonus->setPayrollCode('285');
        $NoveltyLibertyBonus->setPeriod('unidad');
        $NoveltyLibertyBonus->setGrupo('Ajuste');
        $NoveltyLibertyBonus->setNaturaleza('DEV');
        $manager->persist($NoveltyLibertyBonus);

        $NoveltyDiscountLoan = new NoveltyType();
        $NoveltyDiscountLoan->setName('Descuento prestamos');
        $NoveltyDiscountLoan->setPayrollCode('4810');
        $NoveltyDiscountLoan->setPeriod('unidad');
        $NoveltyDiscountLoan->setGrupo('Adelantos');
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
        $NoveltyPension->setName('Aporte pension');
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
        $NoveltyCompensation->setName('Indemnizacion');
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

        $llegadaTarde = new NoveltyType();
        $llegadaTarde->setName('LLegada tarde');
        $llegadaTarde->setGrupo('llamado_atencion');
        $manager->persist($llegadaTarde);

        $abandonoPuesto = new NoveltyType();
        $abandonoPuesto->setName('Abandono puesto de trabajo');
        $abandonoPuesto->setGrupo('llamado_atencion');
        $manager->persist($abandonoPuesto);

        $manager->flush();

        // Abstenteeisms.
        $this->addReference('novlety-maternity', $NoveltyMaternity);
        $this->addReference('novlety-unpaid', $NoveltyUnpaid);
        $this->addReference('novlety-paid', $NoveltyPaid);
        $this->addReference('novlety-suspension', $NoveltySuspension);
        $this->addReference('novlety-general-illness', $NoveltyGeneralIllness);
        $this->addReference('novlety-work-accident', $NoveltyWorkAccident);
        $this->addReference('novlety-professional-illness', $NoveltyProfessionalIllness);
        $this->addReference('novlety-paternity-leave', $NoveltyPaternityLeave);

        // Nolvelty.
        $this->addReference('novlety-salary-adjust', $NoveltySalaryAdjust);
        $this->addReference('novlety-bonus', $NoveltyBonus);
        $this->addReference('novlety-night-charge', $NoveltyNightCharge);
        $this->addReference('novlety-night-holiday-charge', $NoveltyNightHolidayCharge);
        $this->addReference('novlety-extra-hour', $NoveltyExtraHour);
        $this->addReference('novlety-extra-hour-night', $NoveltyExtraHourNight);
        $this->addReference('novlety-extra-hour-holiday', $NoveltyExtraHourHoliday);
        $this->addReference('novlety-holiday', $NoveltyHoliday);
        $this->addReference('novlety-holiday-night-extra-hour', $NoveltyHolidayNightExtraHour);
        $this->addReference('novlety-transport', $NoveltyTransport);
        $this->addReference('novlety-vacation', $NoveltyVacation);
        $this->addReference('novlety-vacation-money', $NoveltyVacationMoney);
        $this->addReference('novlety-liberty-bonus', $NoveltyLibertyBonus);
        $this->addReference('novlety-discount-loan', $NoveltyDiscountLoan);

        // No show.
        $this->addReference('novelty-health', $NoveltyHealth);
        $this->addReference('novelty-pension', $NoveltyPension);
        $this->addReference('novelty-legal-bounty', $NoveltyLegalBounty);
        $this->addReference('novelty-severance', $NoveltySeverance);
        $this->addReference('novelty-severance-interests', $NoveltySeveranceInterests);
        $this->addReference('novelty-compensation', $NoveltyCompensation);
        $this->addReference('novelty-salary', $NoveltySalary);

        //llamado_atencion
        $this->addReference('novlety-llegada-tarde', $llegadaTarde);
        $this->addReference('novlety-abandono-puesto', $abandonoPuesto);
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 9;
    }

}
