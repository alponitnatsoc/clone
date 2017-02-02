<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Document;
use RocketSeller\TwoPickBundle\Entity\DocumentType;

class LoadDocumentTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $documentTypeCedula = new DocumentType();
        $documentTypeCedula->setName('Cédula');
        $documentTypeCedula->setDocCode('CC');
        $manager->persist($documentTypeCedula);

        $documentTypeRut = new DocumentType();
        $documentTypeRut->setName('Rut');
        $documentTypeRut->setDocCode('RUT');
        $manager->persist($documentTypeRut);

        $documentTypeContrato = new DocumentType();
        $documentTypeContrato->setName('Contrato');
        $documentTypeContrato->setDocCode('CTR');
        $manager->persist($documentTypeContrato);

        $documentTypeCartaAut = new DocumentType();
        $documentTypeCartaAut->setName('Carta autorización Symplifica');
        $documentTypeCartaAut->setDocCode('CAS');
        $manager->persist($documentTypeCartaAut);

        $documentTypeRegistroCiv = new DocumentType();
        $documentTypeRegistroCiv->setName('Registro civil de nacimiento');
        $documentTypeRegistroCiv->setDocCode('RCDN');
        $manager->persist($documentTypeRegistroCiv);

        $documentLicenciaMaternidad = new DocumentType();
        $documentLicenciaMaternidad->setName('Licencia de maternidad original');
        $documentLicenciaMaternidad->setDocCode('LDM');
        $manager->persist($documentLicenciaMaternidad);

        $documentLicenciaPaternidad = new DocumentType();
        $documentLicenciaPaternidad->setName('Licencia de paternidad original');
        $documentLicenciaPaternidad->setDocCode('LDP');
        $manager->persist($documentLicenciaPaternidad);

        $documentTypeHistoriaClinica = new DocumentType();
        $documentTypeHistoriaClinica->setName('Historia clinica del suceso');
        $documentTypeHistoriaClinica->setDocCode('HCDE');
        $manager->persist($documentTypeHistoriaClinica);

        $documentTypeDocumentoSoporte = new DocumentType();
        $documentTypeDocumentoSoporte->setName('DOCUMENTOS SOPORTES DE LA NOVEDAD');
        $documentTypeDocumentoSoporte->setDocCode('DSN');
        $manager->persist($documentTypeDocumentoSoporte);

        $documentTypeFormatoSoporte = new DocumentType();
        $documentTypeFormatoSoporte->setName('FORMATOS SOPORTES DE LA NOVEDAD');
        $documentTypeFormatoSoporte->setDocCode('FSN');
        $manager->persist($documentTypeFormatoSoporte);

        $documentTypeIncapacidad = new DocumentType();
        $documentTypeIncapacidad->setName('Soporte de Incapacidad General');
        $documentTypeIncapacidad->setDocCode('INC');
        $manager->persist($documentTypeIncapacidad);

        $documentTypeReporteAcci = new DocumentType();
        $documentTypeReporteAcci->setName('Reporte de accidente de trabajo');
        $documentTypeReporteAcci->setDocCode('RADT');
        $manager->persist($documentTypeReporteAcci);

        $documentTypeCertificadoDefuncion = new DocumentType();
        $documentTypeCertificadoDefuncion->setName('CERTIFICADO DE DEFUNCIÓN');
        $documentTypeCertificadoDefuncion->setDocCode('CERD');
        $manager->persist($documentTypeCertificadoDefuncion);

        $cartaRenuncia = new DocumentType();
        $cartaRenuncia->setName('Carta de renuncia');
        $cartaRenuncia->setDocCode('CDR');
        $manager->persist($cartaRenuncia);

        $solVacaciones = new DocumentType();
        $solVacaciones->setName('Solicitud de vacaciones');
        $solVacaciones->setRefPdf('vacaciones');
        $solVacaciones->setDocCode('SDV');
        $manager->persist($solVacaciones);

        $suspencion = new DocumentType();
        $suspencion->setName('Suspencion');
        $suspencion->setRefPdf('suspencion');
        $suspencion->setDocCode('SUSP');
        $manager->persist($suspencion);

        $descargo = new DocumentType();
        $descargo->setName('Version libre de hechos');
        $descargo->setRefPdf('descargo');
        $descargo->setDocCode('VLDH');
        $manager->persist($descargo);

        $descuento = new DocumentType();
        $descuento->setName('Autorización de descuento');
        $descuento->setRefPdf('aut-descuento');
        $descuento->setDocCode('ADES');
        $manager->persist($descuento);

        $dotacion = new DocumentType();
        $dotacion->setName('Soporte entrega de dotación');
        $dotacion->setRefPdf('dotacion');
        $dotacion->setDocCode('DOT');
        $manager->persist($dotacion);

        $permiso = new DocumentType();
        $permiso->setName('Licencia no remunerada');
        $permiso->setRefPdf('permiso');
        $permiso->setDocCode('LNRE');
        $manager->persist($permiso);

        $notDespido = new DocumentType();
        $notDespido->setName('Notificación de despido');
        $notDespido->setRefPdf('not-despido');
        $notDespido->setDocCode('NDSP');
        $manager->persist($notDespido);

        $mandato = new DocumentType();
        $mandato->setName('Mandato');
        $mandato->setRefPdf('mandato');
        $mandato->setDocCode('MAND');
        $manager->persist($mandato);

        $otroSi = new DocumentType();
        $otroSi->setName('OtroSi');
        $otroSi->setDocCode('OTRS');
        $manager->persist($otroSi);

        $documentTypeComprobante = new DocumentType();
        $documentTypeComprobante->setName('Comprobante');
        $documentTypeComprobante->setDocCode('CPR');
        $manager->persist($documentTypeComprobante);

        $signature = new DocumentType();
        $signature->setName('Firma');
        $signature->setDocCode('FIRM');
        $manager->persist($signature);

        $documentTypeTI = new DocumentType();
        $documentTypeTI->setName('Tarjeta de Identidad');
        $documentTypeTI->setDocCode('TI');
        $manager->persist($documentTypeTI);

        $documentTypeIncapacidadProf = new DocumentType();
        $documentTypeIncapacidadProf->setName('Soporte de Incapacidad Profecional');
        $documentTypeIncapacidadProf->setDocCode('INCP');
        $manager->persist($documentTypeIncapacidadProf);

        $documentTypeRegNacVivo = new DocumentType();
        $documentTypeRegNacVivo->setName('Registro civil de hijo nacido vivo');
        $documentTypeRegNacVivo->setDocCode('RCNV');
        $manager->persist($documentTypeRegNacVivo);

        $documentTypeRem = new DocumentType();
        $documentTypeRem->setName('Licencia remunerada');
        $documentTypeRem->setDocCode('LREM');
        $documentTypeRem->setRefPdf('formato-licencia');
        $manager->persist($documentTypeRem);

        $documentTypeSDVD = new DocumentType();
        $documentTypeSDVD->setName('Solicitud vacaciones en dinero');
        $documentTypeSDVD->setDocCode('SDVD');
        $documentTypeSDVD->setRefPdf('vacaciones-dinero');
        $manager->persist($documentTypeSDVD);
	
        $documentTypeCE = new DocumentType();
        $documentTypeCE->setName('Cédula de extranjería');
        $documentTypeCE->setDocCode('CE');
        $manager->persist($documentTypeCE);

        $documentTypeRad = new DocumentType();
        $documentTypeRad->setName('Radicado');
        $documentTypeRad->setDocCode('RAD');
        $manager->persist($documentTypeRad);
	    
        $documentTypePilaOperatorLog = new DocumentType();
        $documentTypePilaOperatorLog->setName('Enlace Operativo imagen de errores');
        $documentTypePilaOperatorLog->setDocCode('EOIE');
        $manager->persist($documentTypePilaOperatorLog);

        $documentTypePayslipPila = new DocumentType();
        $documentTypePayslipPila->setName('Enlace Operativo comprobante pago planilla');
        $documentTypePayslipPila->setDocCode('EOCP');
        $manager->persist($documentTypePayslipPila);

        $documentTypePasaporte = new DocumentType();
        $documentTypePasaporte->setName('Pasaporte');
        $documentTypePasaporte->setDocCode('PASAPORTE');
        $manager->persist($documentTypePasaporte);

        $documentComprobanteDotacion = new DocumentType();
        $documentComprobanteDotacion->setName('Comprobante Dotación');
        $documentComprobanteDotacion->setDocCode('CPRDOT');
        $manager->persist($documentComprobanteDotacion);

        $documentCartaTerminacionContrato = new DocumentType();
        $documentCartaTerminacionContrato->setName('Carta de Terminación de Contrato');
        $documentCartaTerminacionContrato->setDocCode('CTC');
        $manager->persist($documentCartaTerminacionContrato);

        $documentComprobanteCesantias = new DocumentType();
        $documentComprobanteCesantias->setName('Comprobante Pago Cesantías');
        $documentComprobanteCesantias->setDocCode('CPRCES');
        $manager->persist($documentComprobanteCesantias);
	    
        $manager->flush();

        $this->addReference('document-type-cedula', $documentTypeCedula);
        $this->addReference('document-type-rut', $documentTypeRut);
        $this->addReference('document-type-contrato', $documentTypeContrato);
        $this->addReference('document-carta-aut', $documentTypeCartaAut);
        $this->addReference('document-registro-civ', $documentTypeRegistroCiv);
        $this->addReference('document-licencia-maternidad', $documentLicenciaMaternidad);
        $this->addReference('document-licencia-paternidad', $documentLicenciaPaternidad);
        $this->addReference('document-historia-clinica', $documentTypeHistoriaClinica);
        $this->addReference('document-documento-soporte', $documentTypeDocumentoSoporte);
        $this->addReference('document-formato-soporte', $documentTypeFormatoSoporte);
        $this->addReference('document-incapacidad', $documentTypeIncapacidad);
        $this->addReference('document-reporte-acci', $documentTypeReporteAcci);
        $this->addReference('document-certificado-defuncion-padre', $documentTypeCertificadoDefuncion);
        $this->addReference('document-carta-renuncia', $cartaRenuncia);
        $this->addReference('document-not-despido', $notDespido);
        $this->addReference('document-permiso', $permiso);
        $this->addReference('document-dotacion', $dotacion);
        $this->addReference('document-aut-descuento', $descuento);
        $this->addReference('document-descargo', $descargo);
        $this->addReference('document-suspencion', $suspencion);
        $this->addReference('document-type-comprobante', $documentTypeComprobante);
        $this->addReference('document-sol-vacaciones', $solVacaciones);
        $this->addReference('document-otro-si',$otroSi);
        $this->addReference('document-firma',$signature);
        $this->addReference('document-TI',$documentTypeTI);
        $this->addReference('document-incapacidad-profecional',$documentTypeIncapacidadProf);
        $this->addReference('document-Registro-civil-nacido-vivo',$documentTypeRegNacVivo);
        $this->addReference('document-formato-rem',$documentTypeRem);
        $this->addReference('document-vacaciones-dinero',$documentTypeSDVD);
        $this->addReference('document-CE',$documentTypeCE);
        $this->addReference('document-RAD', $documentTypeRad);
        $this->addReference('document-EOIE', $documentTypePilaOperatorLog);
        $this->addReference('document-EOCP', $documentTypePayslipPila);
        $this->addReference('document-PASAPORTE', $documentTypePasaporte);
        $this->addReference('document-CPRDOT', $documentComprobanteDotacion);
        $this->addReference('document-CTC',$documentCartaTerminacionContrato);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}
