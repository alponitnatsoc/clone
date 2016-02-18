<?php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\NoveltyTypeHasDocumentType;

class LoadNoveltyTypeHasDocumentTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // Maternity.
        $NoveltyMaternidadCedula = new NoveltyTypeHasDocumentType();
        $NoveltyMaternidadCedula->setNoveltyTypeNoveltyType($this->getReference('novelty-maternity'));
        $NoveltyMaternidadCedula->setDocumentTypeDocumentType($this->getReference('document-type-cedula'));
        $NoveltyMaternidadCedula->setPersonType('person');
        $manager->persist($NoveltyMaternidadCedula);

        $NoveltyMaternidadCivil = new NoveltyTypeHasDocumentType();
        $NoveltyMaternidadCivil->setNoveltyTypeNoveltyType($this->getReference('novelty-maternity'));
        $NoveltyMaternidadCivil->setDocumentTypeDocumentType($this->getReference('document-registro-civ-hijo'));
        $NoveltyMaternidadCivil->setPersonType('person');
        $manager->persist($NoveltyMaternidadCivil);

        $NoveltyMaternidadLicencia = new NoveltyTypeHasDocumentType();
        $NoveltyMaternidadLicencia->setNoveltyTypeNoveltyType($this->getReference('novelty-maternity'));
        $NoveltyMaternidadLicencia->setDocumentTypeDocumentType($this->getReference('document-licencia-maternidad'));
        $NoveltyMaternidadLicencia->setPersonType('person');
        $manager->persist($NoveltyMaternidadLicencia);

        $NoveltyMaternidadClinica = new NoveltyTypeHasDocumentType();
        $NoveltyMaternidadClinica->setNoveltyTypeNoveltyType($this->getReference('novelty-maternity'));
        $NoveltyMaternidadClinica->setDocumentTypeDocumentType($this->getReference('document-historia-clinica'));
        $NoveltyMaternidadClinica->setPersonType('employee');
        $manager->persist($NoveltyMaternidadClinica);

        // Licencia no remunerada.
        $NoveltyNoRemuneradaFormato = new NoveltyTypeHasDocumentType();
        $NoveltyNoRemuneradaFormato->setNoveltyTypeNoveltyType($this->getReference('novelty-unpaid'));
        $NoveltyNoRemuneradaFormato->setDocumentTypeDocumentType($this->getReference('document-formato-soporte'));
        $NoveltyNoRemuneradaFormato->setPersonType('employee');
        $manager->persist($NoveltyNoRemuneradaFormato);

        // Licencia remunerada.
        $NoveltyRemuneradaDocumento = new NoveltyTypeHasDocumentType();
        $NoveltyRemuneradaDocumento->setNoveltyTypeNoveltyType($this->getReference('novelty-paid'));
        $NoveltyRemuneradaDocumento->setDocumentTypeDocumentType($this->getReference('document-documento-soporte'));
        $NoveltyRemuneradaDocumento->setPersonType('employee');
        $manager->persist($NoveltyRemuneradaDocumento);

        //Suspension.
        $NoveltySuspensionFormato = new NoveltyTypeHasDocumentType();
        $NoveltySuspensionFormato->setNoveltyTypeNoveltyType($this->getReference('novelty-suspension'));
        $NoveltySuspensionFormato->setDocumentTypeDocumentType($this->getReference('document-formato-soporte'));
        $NoveltySuspensionFormato->setPersonType('employee');
        $manager->persist($NoveltySuspensionFormato);

        // Enfermedad general.
        $NoveltyEnfermedadCedula = new NoveltyTypeHasDocumentType();
        $NoveltyEnfermedadCedula->setNoveltyTypeNoveltyType($this->getReference('novelty-general-illness'));
        $NoveltyEnfermedadCedula->setDocumentTypeDocumentType($this->getReference('document-type-cedula'));
        $NoveltyEnfermedadCedula->setPersonType('employee');
        $manager->persist($NoveltyEnfermedadCedula);

        $NoveltyEnfermedadHistoria = new NoveltyTypeHasDocumentType();
        $NoveltyEnfermedadHistoria->setNoveltyTypeNoveltyType($this->getReference('novelty-general-illness'));
        $NoveltyEnfermedadHistoria->setDocumentTypeDocumentType($this->getReference('document-historia-clinica'));
        $NoveltyEnfermedadHistoria->setPersonType('employee');
        $manager->persist($NoveltyEnfermedadHistoria);

        $NoveltyEnfermedadIncapacidad = new NoveltyTypeHasDocumentType();
        $NoveltyEnfermedadIncapacidad->setNoveltyTypeNoveltyType($this->getReference('novelty-general-illness'));
        $NoveltyEnfermedadIncapacidad->setDocumentTypeDocumentType($this->getReference('document-incapacidad'));
        $NoveltyEnfermedadIncapacidad->setPersonType('employee');
        $manager->persist($NoveltyEnfermedadIncapacidad);

        // Accidente de trabajo.
        $NoveltyAccidenteReporte = new NoveltyTypeHasDocumentType();
        $NoveltyAccidenteReporte->setNoveltyTypeNoveltyType($this->getReference('novelty-work-accident'));
        $NoveltyAccidenteReporte->setDocumentTypeDocumentType($this->getReference('document-reporte-acci'));
        $NoveltyAccidenteReporte->setPersonType('employee');
        $manager->persist($NoveltyAccidenteReporte);

        // Enfermedad profesional.
        $NoveltyEnfProfesionalCedula = new NoveltyTypeHasDocumentType();
        $NoveltyEnfProfesionalCedula->setNoveltyTypeNoveltyType($this->getReference('novelty-professional-illness'));
        $NoveltyEnfProfesionalCedula->setDocumentTypeDocumentType($this->getReference('document-type-cedula'));
        $NoveltyEnfProfesionalCedula->setPersonType('employee');
        $manager->persist($NoveltyEnfProfesionalCedula);

        $NoveltyEnfProfesionalHistoria = new NoveltyTypeHasDocumentType();
        $NoveltyEnfProfesionalHistoria->setNoveltyTypeNoveltyType($this->getReference('novelty-professional-illness'));
        $NoveltyEnfProfesionalHistoria->setDocumentTypeDocumentType($this->getReference('document-historia-clinica'));
        $NoveltyEnfProfesionalHistoria->setPersonType('employee');
        $manager->persist($NoveltyEnfProfesionalHistoria);

        $NoveltyEnfProfesionalIncapacidad = new NoveltyTypeHasDocumentType();
        $NoveltyEnfProfesionalIncapacidad->setNoveltyTypeNoveltyType($this->getReference('novelty-professional-illness'));
        $NoveltyEnfProfesionalIncapacidad->setDocumentTypeDocumentType($this->getReference('document-incapacidad'));
        $NoveltyEnfProfesionalIncapacidad->setPersonType('employee');
        $manager->persist($NoveltyEnfProfesionalIncapacidad);

        $NoveltyEnfProfesionalReporteAccidente = new NoveltyTypeHasDocumentType();
        $NoveltyEnfProfesionalReporteAccidente->setNoveltyTypeNoveltyType($this->getReference('novelty-professional-illness'));
        $NoveltyEnfProfesionalReporteAccidente->setDocumentTypeDocumentType($this->getReference('document-reporte-acci'));
        $NoveltyEnfProfesionalReporteAccidente->setPersonType('employee');
        $manager->persist($NoveltyEnfProfesionalReporteAccidente);

        // Licencia de paternidad.
        $NoveltyPaternidadCedula = new NoveltyTypeHasDocumentType();
        $NoveltyPaternidadCedula->setNoveltyTypeNoveltyType($this->getReference('novelty-paternity-leave'));
        $NoveltyPaternidadCedula->setDocumentTypeDocumentType($this->getReference('document-type-cedula'));
        $NoveltyPaternidadCedula->setPersonType('employee');
        $manager->persist($NoveltyPaternidadCedula);

        $NoveltyPaternidadRegistro = new NoveltyTypeHasDocumentType();
        $NoveltyPaternidadRegistro->setNoveltyTypeNoveltyType($this->getReference('novelty-paternity-leave'));
        $NoveltyPaternidadRegistro->setDocumentTypeDocumentType($this->getReference('document-registro-civ-hijo'));
        $NoveltyPaternidadRegistro->setPersonType('employee');
        $manager->persist($NoveltyPaternidadRegistro);

        $NoveltyPaternidadLicenciaMat = new NoveltyTypeHasDocumentType();
        $NoveltyPaternidadLicenciaMat->setNoveltyTypeNoveltyType($this->getReference('novelty-paternity-leave'));
        $NoveltyPaternidadLicenciaMat->setDocumentTypeDocumentType($this->getReference('document-licencia-maternidad'));
        $NoveltyPaternidadLicenciaMat->setPersonType('employee');
        $manager->persist($NoveltyPaternidadLicenciaMat);

        $NoveltyPaternidadLicenciaPat = new NoveltyTypeHasDocumentType();
        $NoveltyPaternidadLicenciaPat->setNoveltyTypeNoveltyType($this->getReference('novelty-paternity-leave'));
        $NoveltyPaternidadLicenciaPat->setDocumentTypeDocumentType($this->getReference('document-licencia-paternidad'));
        $NoveltyPaternidadLicenciaPat->setPersonType('employee');
        $manager->persist($NoveltyPaternidadLicenciaPat);

        $NoveltyPaternidadHistoria = new NoveltyTypeHasDocumentType();
        $NoveltyPaternidadHistoria->setNoveltyTypeNoveltyType($this->getReference('novelty-paternity-leave'));
        $NoveltyPaternidadHistoria->setDocumentTypeDocumentType($this->getReference('document-historia-clinica'));
        $NoveltyPaternidadHistoria->setPersonType('employee');
        $manager->persist($NoveltyPaternidadHistoria);

        // Ajuste sueldo, no documents required.
        // Bonificacion, no documents required.
        // Recargo nocturno, no documents required.
        // Recargo nocturno festivo, no documents required.
        // Hora extra diurna, no documents required.
        // Hora extra nocturna, no documents required.
        // Hora extra festiva diurna, no documents required.
        // Festivo diurno, no documents required.
        // Hora extra festiva nocturna, no documents required.
        // Subsidio de transporte, no documents required.

        // Vacaciones.
        $NoveltyVacacionesSoporte = new NoveltyTypeHasDocumentType();
        $NoveltyVacacionesSoporte->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation'));
        $NoveltyVacacionesSoporte->setDocumentTypeDocumentType($this->getReference('document-documento-soporte'));
        $NoveltyVacacionesSoporte->setPersonType('employee');
        $manager->persist($NoveltyVacacionesSoporte);


        // Vacaciones dinero.
        $NoveltyVacacionesDineroSoporte = new NoveltyTypeHasDocumentType();
        $NoveltyVacacionesDineroSoporte->setNoveltyTypeNoveltyType($this->getReference('novelty-vacation-money'));
        $NoveltyVacacionesDineroSoporte->setDocumentTypeDocumentType($this->getReference('document-documento-soporte'));
        $NoveltyVacacionesDineroSoporte->setPersonType('employee');
        $manager->persist($NoveltyVacacionesDineroSoporte);

        // Mera liberalidad.
        $NoveltyLiberalidadSoporte = new NoveltyTypeHasDocumentType();
        $NoveltyLiberalidadSoporte->setNoveltyTypeNoveltyType($this->getReference('novelty-liberty-bonus'));
        $NoveltyLiberalidadSoporte->setDocumentTypeDocumentType($this->getReference('document-documento-soporte'));
        $NoveltyLiberalidadSoporte->setPersonType('employee');
        $manager->persist($NoveltyLiberalidadSoporte);

        // Descuento prestamos.
        $NoveltyPrestamoSoporte = new NoveltyTypeHasDocumentType();
        $NoveltyPrestamoSoporte->setNoveltyTypeNoveltyType($this->getReference('novelty-discount-loan'));
        $NoveltyPrestamoSoporte->setDocumentTypeDocumentType($this->getReference('document-documento-soporte'));
        $NoveltyPrestamoSoporte->setPersonType('employee');
        $manager->persist($NoveltyPrestamoSoporte);

        $NoveltyLlegadaTarde = new NoveltyTypeHasDocumentType();
        $NoveltyLlegadaTarde->setNoveltyTypeNoveltyType($this->getReference('novelty-llegada-tarde'));
        $NoveltyLlegadaTarde->setDocumentTypeDocumentType($this->getReference('document-documento-soporte'));
        $NoveltyLlegadaTarde->setPersonType('employee');
        $manager->persist($NoveltyLlegadaTarde);

        $NoveltyAbandonoPuesto = new NoveltyTypeHasDocumentType();
        $NoveltyAbandonoPuesto->setNoveltyTypeNoveltyType($this->getReference('novelty-abandono-puesto'));
        $NoveltyAbandonoPuesto->setDocumentTypeDocumentType($this->getReference('document-documento-soporte'));
        $NoveltyAbandonoPuesto->setPersonType('employee');
        $manager->persist($NoveltyAbandonoPuesto);

        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 10;
    }
}
