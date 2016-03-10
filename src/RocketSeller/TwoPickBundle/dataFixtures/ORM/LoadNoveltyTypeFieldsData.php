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
        $NoveltyMaternityStart = new NoveltyTypeFields();
        $NoveltyMaternityStart->setName('Dia de inicio');
        $NoveltyMaternityStart->setColumnName('date_start');
        $NoveltyMaternityStart->setDataType('date');
        $NoveltyMaternityStart->setNoveltyTypeNoveltyType($this->getReference('novelty-maternity'));
        $manager->persist($NoveltyMaternityStart);

        $NoveltyUnpaidStart = new NoveltyTypeFields();
        $NoveltyUnpaidStart->setName('Día de inicio');
        $NoveltyUnpaidStart->setColumnName('date_start');
        $NoveltyUnpaidStart->setDataType('date');
        $NoveltyUnpaidStart->setNoveltyTypeNoveltyType($this->getReference('novelty-unpaid'));
        $manager->persist($NoveltyUnpaidStart);

        $NoveltyUnpaidEnd = new NoveltyTypeFields();
        $NoveltyUnpaidEnd->setName('Día de finalización');
        $NoveltyUnpaidEnd->setColumnName('date_end');
        $NoveltyUnpaidEnd->setDataType('date');
        $NoveltyUnpaidEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-unpaid'));
        $manager->persist($NoveltyUnpaidEnd);

        $NoveltyPaidStart = new NoveltyTypeFields();
        $NoveltyPaidStart->setName('Día de inicio');
        $NoveltyPaidStart->setColumnName('date_start');
        $NoveltyPaidStart->setDataType('date');
        $NoveltyPaidStart->setNoveltyTypeNoveltyType($this->getReference('novelty-paid'));
        $manager->persist($NoveltyPaidStart);

        $NoveltyPaidEnd = new NoveltyTypeFields();
        $NoveltyPaidEnd->setName('Día de finalización');
        $NoveltyPaidEnd->setColumnName('date_end');
        $NoveltyPaidEnd->setDataType('date');
        $NoveltyPaidEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-paid'));
        $manager->persist($NoveltyPaidEnd);

        $NoveltySuspensionStart = new NoveltyTypeFields();
        $NoveltySuspensionStart->setName('Día de inicio');
        $NoveltySuspensionStart->setColumnName('date_start');
        $NoveltySuspensionStart->setDataType('date');
        $NoveltySuspensionStart->setNoveltyTypeNoveltyType($this->getReference('novelty-suspension'));
        $manager->persist($NoveltySuspensionStart);

        $NoveltySuspensionEnd = new NoveltyTypeFields();
        $NoveltySuspensionEnd->setName('Día de finalización');
        $NoveltySuspensionEnd->setColumnName('date_end');
        $NoveltySuspensionEnd->setDataType('date');
        $NoveltySuspensionEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-suspension'));
        $manager->persist($NoveltySuspensionEnd);

        $NoveltyGeneralIllnessStart = new NoveltyTypeFields();
        $NoveltyGeneralIllnessStart->setName('Día de inicio');
        $NoveltyGeneralIllnessStart->setColumnName('date_start');
        $NoveltyGeneralIllnessStart->setDataType('date');
        $NoveltyGeneralIllnessStart->setNoveltyTypeNoveltyType($this->getReference('novelty-general-illness'));
        $manager->persist($NoveltyGeneralIllnessStart);

        $NoveltyGeneralIllnessEnd = new NoveltyTypeFields();
        $NoveltyGeneralIllnessEnd->setName('Día de finalización');
        $NoveltyGeneralIllnessEnd->setColumnName('date_end');
        $NoveltyGeneralIllnessEnd->setDataType('date');
        $NoveltyGeneralIllnessEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-general-illness'));
        $manager->persist($NoveltyGeneralIllnessEnd);

        $NoveltyWorkAccidentStart = new NoveltyTypeFields();
        $NoveltyWorkAccidentStart->setName('Día accidente');
        $NoveltyWorkAccidentStart->setColumnName('date_start');
        $NoveltyWorkAccidentStart->setDataType('date');
        $NoveltyWorkAccidentStart->setNoveltyTypeNoveltyType($this->getReference('novelty-work-accident'));
        $manager->persist($NoveltyWorkAccidentStart);

        $NoveltyProfessionalIllnessStart = new NoveltyTypeFields();
        $NoveltyProfessionalIllnessStart->setName('Día de inicio');
        $NoveltyProfessionalIllnessStart->setColumnName('date_start');
        $NoveltyProfessionalIllnessStart->setDataType('date');
        $NoveltyProfessionalIllnessStart->setNoveltyTypeNoveltyType($this->getReference('novelty-professional-illness'));
        $manager->persist($NoveltyProfessionalIllnessStart);

        $NoveltyProfessionalIllnessEnd = new NoveltyTypeFields();
        $NoveltyProfessionalIllnessEnd->setName('Día de finalización');
        $NoveltyProfessionalIllnessEnd->setColumnName('date_end');
        $NoveltyProfessionalIllnessEnd->setDataType('date');
        $NoveltyProfessionalIllnessEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-professional-illness'));
        $manager->persist($NoveltyProfessionalIllnessEnd);

        $NoveltyPaternityLeaveStart = new NoveltyTypeFields();
        $NoveltyPaternityLeaveStart->setName('Día de inicio');
        $NoveltyPaternityLeaveStart->setColumnName('date_start');
        $NoveltyPaternityLeaveStart->setDataType('date');
        $NoveltyPaternityLeaveStart->setNoveltyTypeNoveltyType($this->getReference('novelty-paternity-leave'));
        $manager->persist($NoveltyPaternityLeaveStart);

        $NoveltySalaryAdjustAmount = new NoveltyTypeFields();
        $NoveltySalaryAdjustAmount->setName('Valor');
        $NoveltySalaryAdjustAmount->setColumnName('amount');
        $NoveltySalaryAdjustAmount->setDataType('text');
        $NoveltySalaryAdjustAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-salary-adjust'));
        $manager->persist($NoveltySalaryAdjustAmount);

        $NoveltyBonusAmount = new NoveltyTypeFields();
        $NoveltyBonusAmount->setName('Valor');
        $NoveltyBonusAmount->setColumnName('amount');
        $NoveltyBonusAmount->setDataType('text');
        $NoveltyBonusAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-bonus'));
        $manager->persist($NoveltyBonusAmount);

        $NoveltyNightChargeStart = new NoveltyTypeFields();
        $NoveltyNightChargeStart->setName('Día de inicio');
        $NoveltyNightChargeStart->setColumnName('date_start');
        $NoveltyNightChargeStart->setDataType('date');
        $NoveltyNightChargeStart->setNoveltyTypeNoveltyType($this->getReference('novelty-night-charge'));
        $manager->persist($NoveltyNightChargeStart);

        $NoveltyNightChargeEnd = new NoveltyTypeFields();
        $NoveltyNightChargeEnd->setName('Día de finalización');
        $NoveltyNightChargeEnd->setColumnName('date_end');
        $NoveltyNightChargeEnd->setDataType('date');
        $NoveltyNightChargeEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-night-charge'));
        $manager->persist($NoveltyNightChargeEnd);

        $NoveltyNightChargeUnits = new NoveltyTypeFields();
        $NoveltyNightChargeUnits->setName('Número horas');
        $NoveltyNightChargeUnits->setColumnName('units');
        $NoveltyNightChargeUnits->setDataType('text');
        $NoveltyNightChargeUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-night-charge'));
        $manager->persist($NoveltyNightChargeUnits);

        $NoveltyNightHolidayChargeStart = new NoveltyTypeFields();
        $NoveltyNightHolidayChargeStart->setName('Día de inicio');
        $NoveltyNightHolidayChargeStart->setColumnName('date_start');
        $NoveltyNightHolidayChargeStart->setDataType('date');
        $NoveltyNightHolidayChargeStart->setNoveltyTypeNoveltyType($this->getReference('novelty-night-holiday-charge'));
        $manager->persist($NoveltyNightHolidayChargeStart);

        $NoveltyNightHolidayChargeEnd = new NoveltyTypeFields();
        $NoveltyNightHolidayChargeEnd->setName('Día de finalización');
        $NoveltyNightHolidayChargeEnd->setColumnName('date_end');
        $NoveltyNightHolidayChargeEnd->setDataType('date');
        $NoveltyNightHolidayChargeEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-night-holiday-charge'));
        $manager->persist($NoveltyNightHolidayChargeEnd);

        $NoveltyNightHolidayChargeUnits = new NoveltyTypeFields();
        $NoveltyNightHolidayChargeUnits->setName('Número horas');
        $NoveltyNightHolidayChargeUnits->setColumnName('units');
        $NoveltyNightHolidayChargeUnits->setDataType('text');
        $NoveltyNightHolidayChargeUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-night-holiday-charge'));
        $manager->persist($NoveltyNightHolidayChargeUnits);

        $NoveltyExtraHourStart = new NoveltyTypeFields();
        $NoveltyExtraHourStart->setName('Día de inicio');
        $NoveltyExtraHourStart->setColumnName('date_start');
        $NoveltyExtraHourStart->setDataType('date');
        $NoveltyExtraHourStart->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour'));
        $manager->persist($NoveltyExtraHourStart);

        $NoveltyExtraHourEnd = new NoveltyTypeFields();
        $NoveltyExtraHourEnd->setName('Día de finalización');
        $NoveltyExtraHourEnd->setColumnName('date_end');
        $NoveltyExtraHourEnd->setDataType('date');
        $NoveltyExtraHourEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour'));
        $manager->persist($NoveltyExtraHourEnd);

        $NoveltyExtraHourUnits = new NoveltyTypeFields();
        $NoveltyExtraHourUnits->setName('Número horas');
        $NoveltyExtraHourUnits->setColumnName('units');
        $NoveltyExtraHourUnits->setDataType('text');
        $NoveltyExtraHourUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour'));
        $manager->persist($NoveltyExtraHourUnits);

        $NoveltyExtraHourNightStart = new NoveltyTypeFields();
        $NoveltyExtraHourNightStart->setName('Día de inicio');
        $NoveltyExtraHourNightStart->setColumnName('date_start');
        $NoveltyExtraHourNightStart->setDataType('date');
        $NoveltyExtraHourNightStart->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-night'));
        $manager->persist($NoveltyExtraHourNightStart);

        $NoveltyExtraHourNightEnd = new NoveltyTypeFields();
        $NoveltyExtraHourNightEnd->setName('Día de finalización');
        $NoveltyExtraHourNightEnd->setColumnName('date_end');
        $NoveltyExtraHourNightEnd->setDataType('date');
        $NoveltyExtraHourNightEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-night'));
        $manager->persist($NoveltyExtraHourNightEnd);

        $NoveltyExtraHourNightUnits = new NoveltyTypeFields();
        $NoveltyExtraHourNightUnits->setName('Número horas');
        $NoveltyExtraHourNightUnits->setColumnName('units');
        $NoveltyExtraHourNightUnits->setDataType('text');
        $NoveltyExtraHourNightUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-night'));
        $manager->persist($NoveltyExtraHourNightUnits);

        $NoveltyExtraHourHolidayStart = new NoveltyTypeFields();
        $NoveltyExtraHourHolidayStart->setName('Día de inicio');
        $NoveltyExtraHourHolidayStart->setColumnName('date_start');
        $NoveltyExtraHourHolidayStart->setDataType('date');
        $NoveltyExtraHourHolidayStart->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-holiday'));
        $manager->persist($NoveltyExtraHourHolidayStart);

        $NoveltyExtraHourHolidayEnd = new NoveltyTypeFields();
        $NoveltyExtraHourHolidayEnd->setName('Día de finalización');
        $NoveltyExtraHourHolidayEnd->setColumnName('date_end');
        $NoveltyExtraHourHolidayEnd->setDataType('date');
        $NoveltyExtraHourHolidayEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-holiday'));
        $manager->persist($NoveltyExtraHourHolidayEnd);

        $NoveltyExtraHourHolidayUnits = new NoveltyTypeFields();
        $NoveltyExtraHourHolidayUnits->setName('Número horas');
        $NoveltyExtraHourHolidayUnits->setColumnName('units');
        $NoveltyExtraHourHolidayUnits->setDataType('text');
        $NoveltyExtraHourHolidayUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-extra-hour-holiday'));
        $manager->persist($NoveltyExtraHourHolidayUnits);

        $NoveltyHolidayStart = new NoveltyTypeFields();
        $NoveltyHolidayStart->setName('Día de inicio');
        $NoveltyHolidayStart->setColumnName('date_start');
        $NoveltyHolidayStart->setDataType('date');
        $NoveltyHolidayStart->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday'));
        $manager->persist($NoveltyHolidayStart);

        $NoveltyHolidayEnd = new NoveltyTypeFields();
        $NoveltyHolidayEnd->setName('Día de finalización');
        $NoveltyHolidayEnd->setColumnName('date_end');
        $NoveltyHolidayEnd->setDataType('date');
        $NoveltyHolidayEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday'));
        $manager->persist($NoveltyHolidayEnd);

        $NoveltyHolidayUnits = new NoveltyTypeFields();
        $NoveltyHolidayUnits->setName('Número horas');
        $NoveltyHolidayUnits->setColumnName('units');
        $NoveltyHolidayUnits->setDataType('text');
        $NoveltyHolidayUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday'));
        $manager->persist($NoveltyHolidayUnits);

        $NoveltyHolidayNightExtraHourStart = new NoveltyTypeFields();
        $NoveltyHolidayNightExtraHourStart->setName('Día de inicio');
        $NoveltyHolidayNightExtraHourStart->setColumnName('date_start');
        $NoveltyHolidayNightExtraHourStart->setDataType('date');
        $NoveltyHolidayNightExtraHourStart->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday-night-extra-hour'));
        $manager->persist($NoveltyHolidayNightExtraHourStart);

        $NoveltyHolidayNightExtraHourEnd = new NoveltyTypeFields();
        $NoveltyHolidayNightExtraHourEnd->setName('Día de finalización');
        $NoveltyHolidayNightExtraHourEnd->setColumnName('date_end');
        $NoveltyHolidayNightExtraHourEnd->setDataType('date');
        $NoveltyHolidayNightExtraHourEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday-night-extra-hour'));
        $manager->persist($NoveltyHolidayNightExtraHourEnd);

        $NoveltyHolidayNightExtraHourUnits = new NoveltyTypeFields();
        $NoveltyHolidayNightExtraHourUnits->setName('Número horas');
        $NoveltyHolidayNightExtraHourUnits->setColumnName('units');
        $NoveltyHolidayNightExtraHourUnits->setDataType('text');
        $NoveltyHolidayNightExtraHourUnits->setNoveltyTypeNoveltyType($this->getReference('novelty-holiday-night-extra-hour'));
        $manager->persist($NoveltyHolidayNightExtraHourUnits);

        $NoveltyTransportAmount = new NoveltyTypeFields();
        $NoveltyTransportAmount->setName('Valor');
        $NoveltyTransportAmount->setColumnName('amount');
        $NoveltyTransportAmount->setDataType('text');
        $NoveltyTransportAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-transport'));
        $manager->persist($NoveltyTransportAmount);

        $NoveltyVacationStart = new NoveltyTypeFields();
        $NoveltyVacationStart->setName('Día de inicio');
        $NoveltyVacationStart->setColumnName('date_start');
        $NoveltyVacationStart->setDataType('date');
        $NoveltyVacationStart->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation'));
        $manager->persist($NoveltyVacationStart);

        $NoveltyVacationEnd = new NoveltyTypeFields();
        $NoveltyVacationEnd->setName('Día de finalización');
        $NoveltyVacationEnd->setColumnName('date_end');
        $NoveltyVacationEnd->setDataType('date');
        $NoveltyVacationEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation'));
        $manager->persist($NoveltyVacationEnd);

        $NoveltyVacationEnd = new NoveltyTypeFields();
        $NoveltyVacationEnd->setName('Dias en dinero');
        $NoveltyVacationEnd->setColumnName('amount');
        $NoveltyVacationEnd->setDataType('integer');
        $NoveltyVacationEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation'));
        $manager->persist($NoveltyVacationEnd);

        $NoveltyVacationMoneyStart = new NoveltyTypeFields();
        $NoveltyVacationMoneyStart->setName('Día de inicio');
        $NoveltyVacationMoneyStart->setColumnName('date_start');
        $NoveltyVacationMoneyStart->setDataType('date');
        $NoveltyVacationMoneyStart->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation-money'));
        $manager->persist($NoveltyVacationMoneyStart);

        $NoveltyVacationMoneyEnd = new NoveltyTypeFields();
        $NoveltyVacationMoneyEnd->setName('Día de finalización');
        $NoveltyVacationMoneyEnd->setColumnName('date_end');
        $NoveltyVacationMoneyEnd->setDataType('date');
        $NoveltyVacationMoneyEnd->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation-money'));
        $manager->persist($NoveltyVacationMoneyEnd);

        $NoveltyLibertyBonusAmount = new NoveltyTypeFields();
        $NoveltyLibertyBonusAmount->setName('Valor');
        $NoveltyLibertyBonusAmount->setColumnName('amount');
        $NoveltyLibertyBonusAmount->setDataType('text');
        $NoveltyLibertyBonusAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-liberty-bonus'));
        $manager->persist($NoveltyLibertyBonusAmount);

        $NoveltyDiscountLoanAmount = new NoveltyTypeFields();
        $NoveltyDiscountLoanAmount->setName('Valor');
        $NoveltyDiscountLoanAmount->setColumnName('amount');
        $NoveltyDiscountLoanAmount->setDataType('text');
        $NoveltyDiscountLoanAmount->setNoveltyTypeNoveltyType($this->getReference('novelty-discount-loan'));
        $manager->persist($NoveltyDiscountLoanAmount);

        $llegadaTardeStart = new NoveltyTypeFields();
        $llegadaTardeStart->setName('Día de inicio');
        $llegadaTardeStart->setColumnName('date_start');
        $llegadaTardeStart->setDataType('date');
        $llegadaTardeStart->setNoveltyTypeNoveltyType($this->getReference('novelty-llegada-tarde'));
        $manager->persist($llegadaTardeStart);

        $bandonoPuesto = new NoveltyTypeFields();
        $bandonoPuesto->setName('Día de inicio');
        $bandonoPuesto->setColumnName('date_start');
        $bandonoPuesto->setDataType('date');
        $bandonoPuesto->setNoveltyTypeNoveltyType($this->getReference('novelty-abandono-puesto'));
        $manager->persist($bandonoPuesto);

        $manager->flush();

        $this->addReference('novelty-maternity-start', $NoveltyMaternityStart);
        $this->addReference('novelty-unpaid-start', $NoveltyUnpaidStart);
        $this->addReference('novelty-unpaid-end', $NoveltyUnpaidEnd);
        $this->addReference('novelty-paid-start', $NoveltyPaidStart);
        $this->addReference('novelty-paid-end', $NoveltyPaidEnd);
        $this->addReference('novelty-suspension-start', $NoveltySuspensionStart);
        $this->addReference('novelty-suspension-end', $NoveltySuspensionEnd);
        $this->addReference('novelty-illness-start', $NoveltyGeneralIllnessStart);
        $this->addReference('novelty-illness-end', $NoveltyGeneralIllnessEnd);
        $this->addReference('novelty-work-accident-start', $NoveltyWorkAccidentStart);
        $this->addReference('novelty-professional-illneess-start', $NoveltyProfessionalIllnessStart);
        $this->addReference('novelty-professionall-illness-end', $NoveltyProfessionalIllnessEnd);
        $this->addReference('novelty-paternity-leave-start', $NoveltyPaternityLeaveStart);
        $this->addReference('novelty-salary-adjust-amount', $NoveltySalaryAdjustAmount);
        $this->addReference('novelty-bonus-amount', $NoveltyBonusAmount);
        $this->addReference('novelty-night-charge-start', $NoveltyNightChargeStart);
        $this->addReference('novelty-night-charge-end', $NoveltyNightChargeEnd);
        $this->addReference('novelty-night-charge-units', $NoveltyNightChargeUnits);
        $this->addReference('novelty-night-holiday-charge-start', $NoveltyNightHolidayChargeStart);
        $this->addReference('novelty-night-holiday-charge-end', $NoveltyNightHolidayChargeEnd);
        $this->addReference('novelty-night-holiday-charge-units', $NoveltyNightHolidayChargeUnits);
        $this->addReference('novelty-extra-hour-start', $NoveltyExtraHourStart);
        $this->addReference('novelty-extra-hour-end', $NoveltyExtraHourEnd);
        $this->addReference('novelty-extra-hour-units', $NoveltyExtraHourUnits);
        $this->addReference('novelty-extra-hour-night-start', $NoveltyExtraHourNightStart);
        $this->addReference('novelty-extra-hour-night-end', $NoveltyExtraHourNightEnd);
        $this->addReference('novelty-extra-hour-night-units', $NoveltyExtraHourNightUnits);
        $this->addReference('novelty-extra-hour-holiday-start', $NoveltyExtraHourHolidayStart);
        $this->addReference('novelty-extra-hour-holiday-end', $NoveltyExtraHourHolidayEnd);
        $this->addReference('novelty-extra-hour-holiday-units', $NoveltyExtraHourHolidayUnits);
        $this->addReference('novelty-holiday-start', $NoveltyHolidayStart);
        $this->addReference('novelty-holiday-end', $NoveltyHolidayEnd);
        $this->addReference('novelty-holiday-units', $NoveltyHolidayUnits);
        $this->addReference('novelty-holiday-night-extra-hour-start', $NoveltyHolidayNightExtraHourStart);
        $this->addReference('novelty-holiday-night-extra-hour-end', $NoveltyHolidayNightExtraHourEnd);
        $this->addReference('novelty-holiday-night-extra-hour-units', $NoveltyHolidayNightExtraHourUnits);
        $this->addReference('novelty-transport-amount', $NoveltyTransportAmount);
        $this->addReference('novelty-vacation-start', $NoveltyVacationStart);
        $this->addReference('novelty-vacation-end', $NoveltyVacationEnd);
        $this->addReference('novelty-vacation-money-start', $NoveltyVacationMoneyStart);
        $this->addReference('novelty-vacation-money-end', $NoveltyVacationMoneyEnd);
        $this->addReference('novelty-liberty-bonus-amount', $NoveltyLibertyBonusAmount);
        $this->addReference('novelty-discount-loan-amount', $NoveltyDiscountLoanAmount);
        $this->addReference('novelty-llegada-tarde-start', $llegadaTardeStart);
        $this->addReference('novelty-abandono-puesto-start', $bandonoPuesto);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
