<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeFields;

class LoadNoveltyTypeFieldsData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

      /* All the entries with type absenteeism */

        //Licencia de Maternidad
        $NoveltyMaternityStart = new NoveltyTypeFields();
        $NoveltyMaternityStart->setName('Día de inicio');
        $NoveltyMaternityStart->setColumnName('date_start');
        $NoveltyMaternityStart->setDataType('date');
        $NoveltyMaternityStart->setNoveltyTypeNoveltyType($this->getReference('novelty-maternity'));
        $NoveltyMaternityStart->setNoveltyDataConstrain("Min/-_nM_2 Max/+_nD_0");
        $NoveltyMaternityStart->setDisplayable(true);
        $manager->persist($NoveltyMaternityStart);

        $NoveltyMaternityDays = new NoveltyTypeFields();
        $NoveltyMaternityDays->setName('Número de días');
        $NoveltyMaternityDays->setColumnName('units');
        $NoveltyMaternityDays->setDataType('text');
        $NoveltyMaternityDays->setNoveltyTypeNoveltyType($this->getReference('novelty-maternity'));
        $NoveltyMaternityDays->setNoveltyDataConstrain("Min/nD_1 Max/nD_180");
        $NoveltyMaternityDays->setDisplayable(true);
        $manager->persist($NoveltyMaternityDays);

        $NoveltyMaternityEnd = new NoveltyTypeFields();
        $NoveltyMaternityEnd->setName('Día de finalización');
        $NoveltyMaternityEnd->setColumnName('date_end');
        $NoveltyMaternityEnd->setDataType('date');
        $NoveltyMaternityEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-maternity'));
        $NoveltyMaternityEnd->setNoveltyDataConstrain("date_start+units");
        $NoveltyMaternityEnd->setDisplayable(false);
        $manager->persist($NoveltyMaternityEnd);
        //End Licencia de Maternidad

        //Licencia de Paternidad
        $NoveltyPaternityStart = new NoveltyTypeFields();
        $NoveltyPaternityStart->setName('Día de inicio');
        $NoveltyPaternityStart->setColumnName('date_start');
        $NoveltyPaternityStart->setDataType('date');
        $NoveltyPaternityStart->setNoveltyTypeNoveltyType($this->getReference('novelty-paternity-leave'));
        $NoveltyPaternityStart->setNoveltyDataConstrain("Min/-_nM_2 Max/+_nD_0");
        $NoveltyPaternityStart->setDisplayable(true);
        $manager->persist($NoveltyPaternityStart);

        $NoveltyPaternityDays = new NoveltyTypeFields();
        $NoveltyPaternityDays->setName('Número de días');
        $NoveltyPaternityDays->setColumnName('units');
        $NoveltyPaternityDays->setDataType('text');
        $NoveltyPaternityDays->setNoveltyTypeNoveltyType($this->getReference('novelty-paternity-leave'));
        $NoveltyPaternityDays->setNoveltyDataConstrain("Min/nD_1 Max/nD_30");
        $NoveltyPaternityDays->setDisplayable(true);
        $manager->persist($NoveltyPaternityDays);

        $NoveltyPaternityEnd = new NoveltyTypeFields();
        $NoveltyPaternityEnd->setName('Día de finalización');
        $NoveltyPaternityEnd->setColumnName('date_end');
        $NoveltyPaternityEnd->setDataType('date');
        $NoveltyPaternityEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-paternity-leave'));
        $NoveltyPaternityEnd->setNoveltyDataConstrain("date_start+units");
        $NoveltyPaternityEnd->setDisplayable(false);
        $manager->persist($NoveltyPaternityEnd);
        //End Licencia de Paternidad

        // Incapacidad General
        $NoveltyGeneralIllnessStart = new NoveltyTypeFields();
        $NoveltyGeneralIllnessStart->setName('Día de inicio');
        $NoveltyGeneralIllnessStart->setColumnName('date_start');
        $NoveltyGeneralIllnessStart->setDataType('date');
        $NoveltyGeneralIllnessStart->setNoveltyTypeNoveltyType($this->getReference('novelty-general-illness'));
        $NoveltyGeneralIllnessStart->setNoveltyDataConstrain("Min/-_nM_2 Max/+_nD_0");
        $NoveltyGeneralIllnessStart->setDisplayable(true);
        $manager->persist($NoveltyGeneralIllnessStart);

        $NoveltyGeneralIllnessDays = new NoveltyTypeFields();
        $NoveltyGeneralIllnessDays->setName('Número de días');
        $NoveltyGeneralIllnessDays->setColumnName('units');
        $NoveltyGeneralIllnessDays->setDataType('text');
        $NoveltyGeneralIllnessDays->setNoveltyTypeNoveltyType($this->getReference('novelty-general-illness'));
        $NoveltyGeneralIllnessDays->setNoveltyDataConstrain("Min/nD_1 Max/nD_180");
        $NoveltyGeneralIllnessDays->setDisplayable(true);
        $manager->persist($NoveltyGeneralIllnessDays);

        $NoveltyGeneralIllnessEnd = new NoveltyTypeFields();
        $NoveltyGeneralIllnessEnd->setName('Día de finalización');
        $NoveltyGeneralIllnessEnd->setColumnName('date_end');
        $NoveltyGeneralIllnessEnd->setDataType('date');
        $NoveltyGeneralIllnessEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-general-illness'));
        $NoveltyGeneralIllnessEnd->setNoveltyDataConstrain("date_start+units");
        $NoveltyGeneralIllnessEnd->setDisplayable(false);
        $manager->persist($NoveltyGeneralIllnessEnd);
        //Fin Incapacidad General

        // Incapacidad Profesional
        $NoveltyProfessionalIllnessStart = new NoveltyTypeFields();
        $NoveltyProfessionalIllnessStart->setName('Día de inicio');
        $NoveltyProfessionalIllnessStart->setColumnName('date_start');
        $NoveltyProfessionalIllnessStart->setDataType('date');
        $NoveltyProfessionalIllnessStart->setNoveltyTypeNoveltyType($this->getReference('novelty-professional-illness'));
        $NoveltyProfessionalIllnessStart->setNoveltyDataConstrain("Min/-_nP_0 Max/+_nD_0");
        $NoveltyProfessionalIllnessStart->setDisplayable(true);
        $manager->persist($NoveltyProfessionalIllnessStart);

        $NoveltyProfessionalIllnessDays = new NoveltyTypeFields();
        $NoveltyProfessionalIllnessDays->setName('Número de días');
        $NoveltyProfessionalIllnessDays->setColumnName('units');
        $NoveltyProfessionalIllnessDays->setDataType('text');
        $NoveltyProfessionalIllnessDays->setNoveltyTypeNoveltyType($this->getReference('novelty-professional-illness'));
        $NoveltyProfessionalIllnessDays->setNoveltyDataConstrain("Min/nD_1 Max/nD_180");
        $NoveltyProfessionalIllnessDays->setDisplayable(true);
        $manager->persist($NoveltyProfessionalIllnessDays);

        $NoveltyProfessionalIllnessEnd = new NoveltyTypeFields();
        $NoveltyProfessionalIllnessEnd->setName('Día de finalización');
        $NoveltyProfessionalIllnessEnd->setColumnName('date_end');
        $NoveltyProfessionalIllnessEnd->setDataType('date');
        $NoveltyProfessionalIllnessEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-professional-illness'));
        $NoveltyProfessionalIllnessEnd->setNoveltyDataConstrain("date_start+units");
        $NoveltyProfessionalIllnessEnd->setDisplayable(false);
        $manager->persist($NoveltyProfessionalIllnessEnd);
        //Fin Incapacidad Profesional

        //Licencia no remunerada
        $NoveltyUnpaidStart = new NoveltyTypeFields();
        $NoveltyUnpaidStart->setName('Día de inicio');
        $NoveltyUnpaidStart->setColumnName('date_start');
        $NoveltyUnpaidStart->setDataType('date');
        $NoveltyUnpaidStart->setNoveltyTypeNoveltyType($this->getReference('novelty-unpaid'));
        $NoveltyUnpaidStart->setNoveltyDataConstrain("Min/-_nP_0 Max/+_nCut");
        $NoveltyUnpaidStart->setDisplayable(true);
        $manager->persist($NoveltyUnpaidStart);

        $NoveltyUnpaidDays = new NoveltyTypeFields();
        $NoveltyUnpaidDays->setName('Número de días');
        $NoveltyUnpaidDays->setColumnName('units');
        $NoveltyUnpaidDays->setDataType('text');
        $NoveltyUnpaidDays->setNoveltyTypeNoveltyType($this->getReference('novelty-unpaid'));
        $NoveltyUnpaidDays->setNoveltyDataConstrain("Min/nD_1");
        $NoveltyUnpaidDays->setDisplayable(true);
        $manager->persist($NoveltyUnpaidDays);

        $NoveltyUnpaidEnd = new NoveltyTypeFields();
        $NoveltyUnpaidEnd->setName('Día de finalización');
        $NoveltyUnpaidEnd->setColumnName('date_end');
        $NoveltyUnpaidEnd->setDataType('date');
        $NoveltyUnpaidEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-unpaid'));
        $NoveltyUnpaidEnd->setNoveltyDataConstrain("date_start+units");
        $NoveltyUnpaidEnd->setDisplayable(false);
        $manager->persist($NoveltyUnpaidEnd);
        // Fin licencia no remunerada

        //Licencia remunerada
        $NoveltyPaidStart = new NoveltyTypeFields();
        $NoveltyPaidStart->setName('Día de inicio');
        $NoveltyPaidStart->setColumnName('date_start');
        $NoveltyPaidStart->setDataType('date');
        $NoveltyPaidStart->setNoveltyTypeNoveltyType($this->getReference('novelty-paid'));
        $NoveltyPaidStart->setNoveltyDataConstrain("Min/-_nP_0 Max/+_nCut");
        $NoveltyPaidStart->setDisplayable(true);
        $manager->persist($NoveltyPaidStart);

        $NoveltyPaidDays = new NoveltyTypeFields();
        $NoveltyPaidDays->setName('Número de días');
        $NoveltyPaidDays->setColumnName('units');
        $NoveltyPaidDays->setDataType('text');
        $NoveltyPaidDays->setNoveltyTypeNoveltyType($this->getReference('novelty-paid'));
        $NoveltyPaidDays->setNoveltyDataConstrain("Min/nD_1");
        $NoveltyPaidDays->setDisplayable(true);
        $manager->persist($NoveltyPaidDays);

        $NoveltyPaidEnd = new NoveltyTypeFields();
        $NoveltyPaidEnd->setName('Día de finalización');
        $NoveltyPaidEnd->setColumnName('date_end');
        $NoveltyPaidEnd->setDataType('date');
        $NoveltyPaidEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-paid'));
        $NoveltyPaidEnd->setNoveltyDataConstrain("date_start+units");
        $NoveltyPaidEnd->setDisplayable(false);
        $manager->persist($NoveltyPaidEnd);
        //Fin licencia remunerada

        //Suspension
        $NoveltySuspensionStart = new NoveltyTypeFields();
        $NoveltySuspensionStart->setName('Día de inicio');
        $NoveltySuspensionStart->setColumnName('date_start');
        $NoveltySuspensionStart->setDataType('date');
        $NoveltySuspensionStart->setNoveltyTypeNoveltyType($this->getReference('novelty-suspension'));
        $NoveltySuspensionStart->setNoveltyDataConstrain("Min/-_nP_0 Max/+_nCut");
        $NoveltySuspensionStart->setDisplayable(true);
        $manager->persist($NoveltySuspensionStart);

        $NoveltySuspensionDays = new NoveltyTypeFields();
        $NoveltySuspensionDays->setName('Número de días');
        $NoveltySuspensionDays->setColumnName('units');
        $NoveltySuspensionDays->setDataType('text');
        $NoveltySuspensionDays->setNoveltyTypeNoveltyType($this->getReference('novelty-suspension'));
        $NoveltySuspensionDays->setNoveltyDataConstrain("Min/nD_1");
        $NoveltySuspensionDays->setDisplayable(true);
        $manager->persist($NoveltySuspensionDays);

        $NoveltySuspensionEnd = new NoveltyTypeFields();
        $NoveltySuspensionEnd->setName('Día de finalización');
        $NoveltySuspensionEnd->setColumnName('date_end');
        $NoveltySuspensionEnd->setDataType('date');
        $NoveltySuspensionEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-suspension'));
        $NoveltySuspensionEnd->setNoveltyDataConstrain("date_start+units");
        $NoveltySuspensionEnd->setDisplayable(false);
        $manager->persist($NoveltySuspensionEnd);
        //Fin suspension

        //Vacaciones dias
        $NoveltyVacationStart = new NoveltyTypeFields();
        $NoveltyVacationStart->setName('Día de inicio');
        $NoveltyVacationStart->setColumnName('date_start');
        $NoveltyVacationStart->setDataType('date');
        $NoveltyVacationStart->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation'));
        $NoveltyVacationStart->setNoveltyDataConstrain("Min/-_nP_0 Max/+_nP_1");
        $NoveltyVacationStart->setDisplayable(true);
        $manager->persist($NoveltyVacationStart);

        /*$totalDiasVacas = new NoveltyTypeFields();
        $totalDiasVacas->setName('Total de días');
        $totalDiasVacas->setColumnName('units');
        $totalDiasVacas->setDataType('text');
        $totalDiasVacas->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation'));
        $totalDiasVacas->setNoveltyDataConstrain("Min/nD_1 Max/nD_45");
        $totalDiasVacas->setDisplayable(true);
        $manager->persist($totalDiasVacas);*/

        $NoveltyVacationEnd = new NoveltyTypeFields();
        $NoveltyVacationEnd->setName('Día de finalización');
        $NoveltyVacationEnd->setColumnName('date_end');
        $NoveltyVacationEnd->setDataType('date');
        $NoveltyVacationEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation'));
        //$NoveltyVacationEnd->setNoveltyDataConstrain("date_start+units");
        $NoveltyVacationEnd->setDisplayable(true);
        $manager->persist($NoveltyVacationEnd);
        // Fin vacaciones dias

        //Vacaciones dinero
        $NoveltyVacationMoneyStart = new NoveltyTypeFields();
        $NoveltyVacationMoneyStart->setName('Día de inicio');
        $NoveltyVacationMoneyStart->setColumnName('date_start');
        $NoveltyVacationMoneyStart->setDataType('date');
        $NoveltyVacationMoneyStart->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation-money'));
        $NoveltyVacationMoneyStart->setNoveltyDataConstrain("Min/-_nP_0 Max/+_nP_1");
        $NoveltyVacationMoneyStart->setDisplayable(true);
        $manager->persist($NoveltyVacationMoneyStart);

        $NoveltyVacationMoneyDays = new NoveltyTypeFields();
        $NoveltyVacationMoneyDays->setName('Días en dinero');
        $NoveltyVacationMoneyDays->setColumnName('units');
        $NoveltyVacationMoneyDays->setDataType('text');
        $NoveltyVacationMoneyDays->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation-money'));
        $NoveltyVacationMoneyDays->setNoveltyDataConstrain("Min/nD_1 Max/nD_45");
        $NoveltyVacationMoneyDays->setDisplayable(true);
        $manager->persist($NoveltyVacationMoneyDays);

        $NoveltyVacationMoneyEnd = new NoveltyTypeFields();
        $NoveltyVacationMoneyEnd->setName('Día de finalización');
        $NoveltyVacationMoneyEnd->setColumnName('date_end');
        $NoveltyVacationMoneyEnd->setDataType('date');
        $NoveltyVacationMoneyEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation-money'));
        $NoveltyVacationMoneyEnd->setNoveltyDataConstrain("date_start+units");
        $NoveltyVacationMoneyEnd->setDisplayable(false);
        $manager->persist($NoveltyVacationMoneyEnd);
        //Fin vacaciones dinero

      //Fin Ausentismos

        //Bonificacion
		    $NoveltyLibertyBonusAmount = new NoveltyTypeFields();
		    $NoveltyLibertyBonusAmount->setName('Valor');
		    $NoveltyLibertyBonusAmount->setColumnName('amount');
		    $NoveltyLibertyBonusAmount->setDataType('text');
		    $NoveltyLibertyBonusAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-liberty-bonus'));
	      $NoveltyLibertyBonusAmount->setNoveltyDataConstrain("Min/mo_1");
	      $NoveltyLibertyBonusAmount->setDisplayable(true);
		    $manager->persist($NoveltyLibertyBonusAmount);
        //Fin bonificacion

        //Hora extra diurna
        $NoveltyExtraHourStart = new NoveltyTypeFields();
        $NoveltyExtraHourStart->setName('Día de inicio');
        $NoveltyExtraHourStart->setColumnName('date_start');
        $NoveltyExtraHourStart->setDataType('date');
        $NoveltyExtraHourStart->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour'));
        $NoveltyExtraHourStart->setNoveltyDataConstrain("sP");
        $NoveltyExtraHourStart->setDisplayable(false);
        $manager->persist($NoveltyExtraHourStart);

        $NoveltyExtraHourEnd = new NoveltyTypeFields();
        $NoveltyExtraHourEnd->setName('Día de finalización');
        $NoveltyExtraHourEnd->setColumnName('date_end');
        $NoveltyExtraHourEnd->setDataType('date');
        $NoveltyExtraHourEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour'));
        $NoveltyExtraHourEnd->setNoveltyDataConstrain("eP");
        $NoveltyExtraHourEnd->setDisplayable(false);
        $manager->persist($NoveltyExtraHourEnd);

        $NoveltyExtraHourUnits = new NoveltyTypeFields();
        $NoveltyExtraHourUnits->setName('Número horas');
        $NoveltyExtraHourUnits->setColumnName('units');
        $NoveltyExtraHourUnits->setDataType('text');
        $NoveltyExtraHourUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour'));
        $NoveltyExtraHourUnits->setNoveltyDataConstrain("Min/nH_1 Max/nH_40");
        $NoveltyExtraHourUnits->setDisplayable(true);
        $manager->persist($NoveltyExtraHourUnits);
        //Fin hora extra diurna

        //Hora extra nocturna
        $NoveltyExtraHourNightStart = new NoveltyTypeFields();
        $NoveltyExtraHourNightStart->setName('Día de inicio');
        $NoveltyExtraHourNightStart->setColumnName('date_start');
        $NoveltyExtraHourNightStart->setDataType('date');
        $NoveltyExtraHourNightStart->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-night'));
        $NoveltyExtraHourNightStart->setNoveltyDataConstrain("sP");
        $NoveltyExtraHourNightStart->setDisplayable(false);
        $manager->persist($NoveltyExtraHourNightStart);

        $NoveltyExtraHourNightEnd = new NoveltyTypeFields();
        $NoveltyExtraHourNightEnd->setName('Día de finalización');
        $NoveltyExtraHourNightEnd->setColumnName('date_end');
        $NoveltyExtraHourNightEnd->setDataType('date');
        $NoveltyExtraHourNightEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-night'));
        $NoveltyExtraHourNightEnd->setNoveltyDataConstrain("eP");
        $NoveltyExtraHourNightEnd->setDisplayable(false);
        $manager->persist($NoveltyExtraHourNightEnd);

        $NoveltyExtraHourNightUnits = new NoveltyTypeFields();
        $NoveltyExtraHourNightUnits->setName('Número horas');
        $NoveltyExtraHourNightUnits->setColumnName('units');
        $NoveltyExtraHourNightUnits->setDataType('text');
        $NoveltyExtraHourNightUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-night'));
        $NoveltyExtraHourNightUnits->setNoveltyDataConstrain("Min/nH_1 Max/nH_40");
        $NoveltyExtraHourNightUnits->setDisplayable(true);
        $manager->persist($NoveltyExtraHourNightUnits);
        //Fin hora extra nocturna

        //Hora extra diurna festiva
        $NoveltyExtraHourHolidayStart = new NoveltyTypeFields();
        $NoveltyExtraHourHolidayStart->setName('Día de inicio');
        $NoveltyExtraHourHolidayStart->setColumnName('date_start');
        $NoveltyExtraHourHolidayStart->setDataType('date');
        $NoveltyExtraHourHolidayStart->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-holiday'));
        $NoveltyExtraHourHolidayStart->setNoveltyDataConstrain("sP");
        $NoveltyExtraHourHolidayStart->setDisplayable(false);
        $manager->persist($NoveltyExtraHourHolidayStart);

        $NoveltyExtraHourHolidayEnd = new NoveltyTypeFields();
        $NoveltyExtraHourHolidayEnd->setName('Día de finalización');
        $NoveltyExtraHourHolidayEnd->setColumnName('date_end');
        $NoveltyExtraHourHolidayEnd->setDataType('date');
        $NoveltyExtraHourHolidayEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-holiday'));
        $NoveltyExtraHourHolidayEnd->setNoveltyDataConstrain("eP");
        $NoveltyExtraHourHolidayEnd->setDisplayable(false);
        $manager->persist($NoveltyExtraHourHolidayEnd);

        $NoveltyExtraHourHolidayUnits = new NoveltyTypeFields();
        $NoveltyExtraHourHolidayUnits->setName('Número horas');
        $NoveltyExtraHourHolidayUnits->setColumnName('units');
        $NoveltyExtraHourHolidayUnits->setDataType('text');
        $NoveltyExtraHourHolidayUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-holiday'));
        $NoveltyExtraHourHolidayUnits->setNoveltyDataConstrain("Min/nH_1 Max/nH_40");
        $NoveltyExtraHourHolidayUnits->setDisplayable(true);
        $manager->persist($NoveltyExtraHourHolidayUnits);
        //Fin hora extra diurna festiva

        //Hora extra nocturna festiva
        $NoveltyHolidayNightExtraHourStart = new NoveltyTypeFields();
        $NoveltyHolidayNightExtraHourStart->setName('Día de inicio');
        $NoveltyHolidayNightExtraHourStart->setColumnName('date_start');
        $NoveltyHolidayNightExtraHourStart->setDataType('date');
        $NoveltyHolidayNightExtraHourStart->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday-night-extra-hour'));
        $NoveltyHolidayNightExtraHourStart->setNoveltyDataConstrain("sP");
        $NoveltyHolidayNightExtraHourStart->setDisplayable(false);
        $manager->persist($NoveltyHolidayNightExtraHourStart);

        $NoveltyHolidayNightExtraHourEnd = new NoveltyTypeFields();
        $NoveltyHolidayNightExtraHourEnd->setName('Día de finalización');
        $NoveltyHolidayNightExtraHourEnd->setColumnName('date_end');
        $NoveltyHolidayNightExtraHourEnd->setDataType('date');
        $NoveltyHolidayNightExtraHourEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday-night-extra-hour'));
        $NoveltyHolidayNightExtraHourEnd->setNoveltyDataConstrain("eP");
        $NoveltyHolidayNightExtraHourEnd->setDisplayable(false);
        $manager->persist($NoveltyHolidayNightExtraHourEnd);

        $NoveltyHolidayNightExtraHourUnits = new NoveltyTypeFields();
        $NoveltyHolidayNightExtraHourUnits->setName('Número horas');
        $NoveltyHolidayNightExtraHourUnits->setColumnName('units');
        $NoveltyHolidayNightExtraHourUnits->setDataType('text');
        $NoveltyHolidayNightExtraHourUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday-night-extra-hour'));
        $NoveltyHolidayNightExtraHourUnits->setNoveltyDataConstrain("Min/nH_1 Max/nH_40");
        $NoveltyHolidayNightExtraHourUnits->setDisplayable(true);
        $manager->persist($NoveltyHolidayNightExtraHourUnits);
        //Fin hora extra nocturna festiva

        //Prestamo y anticipo
        $NoveltyDiscountLoanAmount = new NoveltyTypeFields();
        $NoveltyDiscountLoanAmount->setName('Valor');
        $NoveltyDiscountLoanAmount->setColumnName('amount');
        $NoveltyDiscountLoanAmount->setDataType('text');
        $NoveltyDiscountLoanAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-discount-loan'));
        $NoveltyDiscountLoanAmount->setNoveltyDataConstrain("Max/pe_50_Sal");
        $NoveltyDiscountLoanAmount->setDisplayable(true);
        $manager->persist($NoveltyDiscountLoanAmount);

        $desPrestamoCuotas = new NoveltyTypeFields();
        $desPrestamoCuotas->setName('Número de cuotas');
        $desPrestamoCuotas->setColumnName('units');
        $desPrestamoCuotas->setDataType('text');
        $desPrestamoCuotas->setNoveltyTypeNoveltyType($this->getReference('novelty-discount-loan'));
        $desPrestamoCuotas->setNoveltyDataConstrain("Min/nC_1");
        $desPrestamoCuotas->setDisplayable(true);
        $manager->persist($desPrestamoCuotas);

        $desPrestamoMotivo = new NoveltyTypeFields();
        $desPrestamoMotivo->setName('Motivo');
        $desPrestamoMotivo->setColumnName('description');
        $desPrestamoMotivo->setDataType('text');
        $desPrestamoMotivo->setNoveltyTypeNoveltyType($this->getReference('novelty-discount-loan'));
        $desPrestamoMotivo->setDisplayable(true);
        $manager->persist($desPrestamoMotivo);

        $desPrestamoStart = new NoveltyTypeFields();
        $desPrestamoStart->setName('Fecha inicio descuento');
        $desPrestamoStart->setColumnName('date_start');
        $desPrestamoStart->setDataType('date');
        $desPrestamoStart->setNoveltyTypeNoveltyType($this->getReference('novelty-discount-loan'));
        $desPrestamoStart->setNoveltyDataConstrain("today");
        $desPrestamoStart->setDisplayable(false);
        $manager->persist($desPrestamoStart);
        //Fin prestamo y anticipo


        //Not used by now
        $NoveltyWorkAccidentStart = new NoveltyTypeFields();
        $NoveltyWorkAccidentStart->setName('Día accidente');
        $NoveltyWorkAccidentStart->setColumnName('date_start');
        $NoveltyWorkAccidentStart->setDataType('date');
        $NoveltyWorkAccidentStart->setNoveltyTypeNoveltyType($this->getReference('novelty-work-accident'));
        $NoveltyWorkAccidentStart->setDisplayable(false);
        $manager->persist($NoveltyWorkAccidentStart);

        $NoveltySalaryAdjustAmount = new NoveltyTypeFields();
        $NoveltySalaryAdjustAmount->setName('Valor');
        $NoveltySalaryAdjustAmount->setColumnName('amount');
        $NoveltySalaryAdjustAmount->setDataType('text');
        $NoveltySalaryAdjustAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-salary-adjust'));
        $NoveltySalaryAdjustAmount->setDisplayable(false);
        $manager->persist($NoveltySalaryAdjustAmount);

        $NoveltyNightChargeStart = new NoveltyTypeFields();
        $NoveltyNightChargeStart->setName('Día de inicio');
        $NoveltyNightChargeStart->setColumnName('date_start');
        $NoveltyNightChargeStart->setDataType('date');
        $NoveltyNightChargeStart->setNoveltyTypeNoveltyType($this->getReference('novelty-night-charge'));
        $NoveltyNightChargeStart->setDisplayable(false);
        $manager->persist($NoveltyNightChargeStart);

        $NoveltyNightChargeEnd = new NoveltyTypeFields();
        $NoveltyNightChargeEnd->setName('Día de finalización');
        $NoveltyNightChargeEnd->setColumnName('date_end');
        $NoveltyNightChargeEnd->setDataType('date');
        $NoveltyNightChargeEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-night-charge'));
        $NoveltyNightChargeEnd->setDisplayable(false);
        $manager->persist($NoveltyNightChargeEnd);

        $NoveltyNightChargeUnits = new NoveltyTypeFields();
        $NoveltyNightChargeUnits->setName('Número horas');
        $NoveltyNightChargeUnits->setColumnName('units');
        $NoveltyNightChargeUnits->setDataType('text');
        $NoveltyNightChargeUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-night-charge'));
        $NoveltyNightChargeUnits->setDisplayable(false);
        $manager->persist($NoveltyNightChargeUnits);

        $NoveltyNightHolidayChargeStart = new NoveltyTypeFields();
        $NoveltyNightHolidayChargeStart->setName('Día de inicio');
        $NoveltyNightHolidayChargeStart->setColumnName('date_start');
        $NoveltyNightHolidayChargeStart->setDataType('date');
        $NoveltyNightHolidayChargeStart->setNoveltyTypeNoveltyType($this->getReference('novelty-night-holiday-charge'));
        $NoveltyNightHolidayChargeStart->setDisplayable(false);
        $manager->persist($NoveltyNightHolidayChargeStart);

        $NoveltyNightHolidayChargeEnd = new NoveltyTypeFields();
        $NoveltyNightHolidayChargeEnd->setName('Día de finalización');
        $NoveltyNightHolidayChargeEnd->setColumnName('date_end');
        $NoveltyNightHolidayChargeEnd->setDataType('date');
        $NoveltyNightHolidayChargeEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-night-holiday-charge'));
        $NoveltyNightHolidayChargeEnd->setDisplayable(false);
        $manager->persist($NoveltyNightHolidayChargeEnd);

        $NoveltyNightHolidayChargeUnits = new NoveltyTypeFields();
        $NoveltyNightHolidayChargeUnits->setName('Número horas');
        $NoveltyNightHolidayChargeUnits->setColumnName('units');
        $NoveltyNightHolidayChargeUnits->setDataType('text');
        $NoveltyNightHolidayChargeUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-night-holiday-charge'));
        $NoveltyNightHolidayChargeUnits->setDisplayable(false);
        $manager->persist($NoveltyNightHolidayChargeUnits);

        $NoveltyHolidayStart = new NoveltyTypeFields();
        $NoveltyHolidayStart->setName('Día de inicio');
        $NoveltyHolidayStart->setColumnName('date_start');
        $NoveltyHolidayStart->setDataType('date');
        $NoveltyHolidayStart->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday'));
        $NoveltyHolidayStart->setDisplayable(false);
        $manager->persist($NoveltyHolidayStart);

        $NoveltyHolidayEnd = new NoveltyTypeFields();
        $NoveltyHolidayEnd->setName('Día de finalización');
        $NoveltyHolidayEnd->setColumnName('date_end');
        $NoveltyHolidayEnd->setDataType('date');
        $NoveltyHolidayEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday'));
        $NoveltyHolidayEnd->setDisplayable(false);
        $manager->persist($NoveltyHolidayEnd);

        $NoveltyHolidayUnits = new NoveltyTypeFields();
        $NoveltyHolidayUnits->setName('Número horas');
        $NoveltyHolidayUnits->setColumnName('units');
        $NoveltyHolidayUnits->setDataType('text');
        $NoveltyHolidayUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday'));
        $NoveltyHolidayUnits->setDisplayable(false);
        $manager->persist($NoveltyHolidayUnits);

        $NoveltyTransportAmount = new NoveltyTypeFields();
        $NoveltyTransportAmount->setName('Valor');
        $NoveltyTransportAmount->setColumnName('amount');
        $NoveltyTransportAmount->setDataType('text');
        $NoveltyTransportAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-transport'));
        $NoveltyTransportAmount->setDisplayable(false);
        $manager->persist($NoveltyTransportAmount);
	
		    $NoveltyBonusAmount = new NoveltyTypeFields();
		    $NoveltyBonusAmount->setName('Valor');
		    $NoveltyBonusAmount->setColumnName('amount');
		    $NoveltyBonusAmount->setDataType('text');
		    $NoveltyBonusAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-bonus'));
	      $NoveltyBonusAmount->setDisplayable(false);
		    $manager->persist($NoveltyBonusAmount);

        $llegadaTardeStart = new NoveltyTypeFields();
        $llegadaTardeStart->setName('Día de inicio');
        $llegadaTardeStart->setColumnName('date_start');
        $llegadaTardeStart->setDataType('date');
        $llegadaTardeStart->setNoveltyTypeNoveltyType($this->getReference('novelty-llegada-tarde'));
        $llegadaTardeStart->setDisplayable(false);
        $manager->persist($llegadaTardeStart);

        $abandonoPuesto = new NoveltyTypeFields();
        $abandonoPuesto->setName('Día de inicio');
        $abandonoPuesto->setColumnName('date_start');
        $abandonoPuesto->setDataType('date');
        $abandonoPuesto->setNoveltyTypeNoveltyType($this->getReference('novelty-abandono-puesto'));
        $abandonoPuesto->setDisplayable(false);
        $manager->persist($abandonoPuesto);
	    
        $descargosStart = new NoveltyTypeFields();
        $descargosStart->setName('Día de ocurrencia');
        $descargosStart->setColumnName('date_start');
        $descargosStart->setDataType('date');
        $descargosStart->setNoveltyTypeNoveltyType($this->getReference('novelty-descargos'));
        $descargosStart->setDisplayable(false);
        $manager->persist($descargosStart);

        $dotacionStart = new NoveltyTypeFields();
        $dotacionStart->setName('Fecha inicio');
        $dotacionStart->setColumnName('date_start');
        $dotacionStart->setDataType('date');
        $dotacionStart->setNoveltyTypeNoveltyType($this->getReference('novelty-dotacion'));
        $dotacionStart->setDisplayable(false);
        $manager->persist($dotacionStart);

        $dotacionEnd = new NoveltyTypeFields();
        $dotacionEnd->setName('Fecha fin');
        $dotacionEnd->setColumnName('date_end');
        $dotacionEnd->setDataType('date');
        $dotacionEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-dotacion'));
        $dotacionEnd->setDisplayable(false);
        $manager->persist($dotacionEnd);

        /*$noveltyUnpaidDays = new NoveltyTypeFields();
        $noveltyUnpaidDays->setName('Total de días');
        $noveltyUnpaidDays->setColumnName('units');
        $noveltyUnpaidDays->setDataType('text');
        $noveltyUnpaidDays->setNoveltyTypeNoveltyType($this->getReference('novelty-unpaid'));
        $noveltyUnpaidDays->setDisplayable(false);
        $manager->persist($noveltyUnpaidDays);*/
	
        $despido = new NoveltyTypeFields();
        $despido->setName('Fin de contrato');
        $despido->setColumnName('date_end');
        $despido->setDataType('date');
        $despido->setNoveltyTypeNoveltyType($this->getReference('novelty-despido'));
        $despido->setDisplayable(false);
        $manager->persist($despido);

        $manager->flush();

        //Ref licencia de maternidad
        $this->addReference('novelty-maternity-start', $NoveltyMaternityStart);
        $this->addReference('novelty-maternity-days', $NoveltyMaternityDays);
        $this->addReference('novelty-maternity-end', $NoveltyMaternityEnd);

        //Ref licencia de paternidad
        $this->addReference('novelty-paternity-start', $NoveltyPaternityStart);
        $this->addReference('novelty-paternity-days', $NoveltyPaternityDays);
        $this->addReference('novelty-paternity-end', $NoveltyPaternityEnd);

        //Ref Incapacidad general
        $this->addReference('novelty-general-illness-start', $NoveltyGeneralIllnessStart);
        $this->addReference('novelty-general-illness-days', $NoveltyGeneralIllnessDays);
        $this->addReference('novelty-general-illness-end', $NoveltyGeneralIllnessEnd);

        //Ref Incapacidad profesional
        $this->addReference('novelty-professional-illness-start', $NoveltyProfessionalIllnessStart);
        $this->addReference('novelty-professional-illness-days',$NoveltyProfessionalIllnessDays);
        $this->addReference('novelty-professional-illness-end', $NoveltyProfessionalIllnessEnd);

        //Ref Licencia no remunerada
        $this->addReference('novelty-unpaid-start', $NoveltyUnpaidStart);
        $this->addReference('novelty-unpaid-days', $NoveltyUnpaidDays);
        $this->addReference('novelty-unpaid-end', $NoveltyUnpaidEnd);

        //Ref Licencia remunerada
        $this->addReference('novelty-paid-start', $NoveltyPaidStart);
        $this->addReference('novelty-paid-days', $NoveltyPaidDays);
        $this->addReference('novelty-paid-end', $NoveltyPaidEnd);

        //Ref Suspension
        $this->addReference('novelty-suspension-start', $NoveltySuspensionStart);
        $this->addReference('novelty-suspension-days', $NoveltySuspensionDays);
        $this->addReference('novelty-suspension-end', $NoveltySuspensionEnd);

        //Ref Vacaciones dias
        $this->addReference('novelty-vacation-start', $NoveltyVacationStart);
        //$this->addReference('total-dias-vacas', $totalDiasVacas);
        $this->addReference('novelty-vacation-end', $NoveltyVacationEnd);

        //Ref Vacaciones dinero
        $this->addReference('novelty-vacation-money-start', $NoveltyVacationMoneyStart);
        $this->addReference('novelty-vacation-money-days', $NoveltyVacationMoneyDays);
        $this->addReference('novelty-vacation-money-end', $NoveltyVacationMoneyEnd);

        //Ref Bonificacion
        $this->addReference('novelty-liberty-bonus-amount', $NoveltyLibertyBonusAmount);

        //Ref Hora extra diurna
        $this->addReference('novelty-extra-hour-start', $NoveltyExtraHourStart);
        $this->addReference('novelty-extra-hour-end', $NoveltyExtraHourEnd);
        $this->addReference('novelty-extra-hour-units', $NoveltyExtraHourUnits);

        //Ref Hora extra nocturna
        $this->addReference('novelty-extra-hour-night-start', $NoveltyExtraHourNightStart);
        $this->addReference('novelty-extra-hour-night-end', $NoveltyExtraHourNightEnd);
        $this->addReference('novelty-extra-hour-night-units', $NoveltyExtraHourNightUnits);

        //Ref hora extra diurna festiva
        $this->addReference('novelty-extra-hour-holiday-start', $NoveltyExtraHourHolidayStart);
        $this->addReference('novelty-extra-hour-holiday-end', $NoveltyExtraHourHolidayEnd);
        $this->addReference('novelty-extra-hour-holiday-units', $NoveltyExtraHourHolidayUnits);

        //Ref hora extra nocturna festiva
        $this->addReference('novelty-holiday-night-extra-hour-start', $NoveltyHolidayNightExtraHourStart);
        $this->addReference('novelty-holiday-night-extra-hour-end', $NoveltyHolidayNightExtraHourEnd);
        $this->addReference('novelty-holiday-night-extra-hour-units', $NoveltyHolidayNightExtraHourUnits);

        //Ref prestamo y anticipo
        $this->addReference('novelty-discount-loan-amount', $NoveltyDiscountLoanAmount);
        $this->addReference('des-prestamo-cuotas', $desPrestamoCuotas);
        $this->addReference('des-prestamo-motivo', $desPrestamoMotivo);
        $this->addReference('des-prestamo-start', $desPrestamoStart);

        //Ref no usadas por ahora
        $this->addReference('novelty-work-accident-start', $NoveltyWorkAccidentStart);
        $this->addReference('novelty-salary-adjust-amount', $NoveltySalaryAdjustAmount);
        $this->addReference('novelty-night-charge-start', $NoveltyNightChargeStart);
        $this->addReference('novelty-night-charge-end', $NoveltyNightChargeEnd);
        $this->addReference('novelty-night-charge-units', $NoveltyNightChargeUnits);
        $this->addReference('novelty-night-holiday-charge-start', $NoveltyNightHolidayChargeStart);
        $this->addReference('novelty-night-holiday-charge-end', $NoveltyNightHolidayChargeEnd);
        $this->addReference('novelty-night-holiday-charge-units', $NoveltyNightHolidayChargeUnits);
        $this->addReference('novelty-holiday-start', $NoveltyHolidayStart);
        $this->addReference('novelty-holiday-end', $NoveltyHolidayEnd);
        $this->addReference('novelty-holiday-units', $NoveltyHolidayUnits);
        $this->addReference('novelty-transport-amount', $NoveltyTransportAmount);
		    $this->addReference('novelty-bonus-amount', $NoveltyBonusAmount);
		    $this->addReference('novelty-llegada-tarde-start', $llegadaTardeStart);
        $this->addReference('novelty-abandono-puesto-start', $abandonoPuesto);
        $this->addReference('novelty-descargos-start', $descargosStart);
        $this->addReference('novelty-dotacion-start', $dotacionStart);
        $this->addReference('novelty-dotacion-end', $dotacionEnd);
        $this->addReference('novelty-novelty-unpaid-days', $noveltyUnpaidDays);
        $this->addReference('novelty-despido-end', $despido);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
