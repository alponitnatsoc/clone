<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\ActionType;

class LoadActionTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        $actionTypeValidarRegistroEmpleador = new ActionType();
        $actionTypeValidarRegistroEmpleador->setName('Validar Información del Empleador');
        $actionTypeValidarRegistroEmpleador->setCode('VER');
        $manager->persist($actionTypeValidarRegistroEmpleador);

        $actionTypeValidarDocumentoEmpleador = new ActionType();
        $actionTypeValidarDocumentoEmpleador->setName("Validar Documento Empleador");
        $actionTypeValidarDocumentoEmpleador->setCode("VDDE");
        $manager->persist($actionTypeValidarDocumentoEmpleador);

        $actionTypeValidarRUTEmpleador = new ActionType();
        $actionTypeValidarRUTEmpleador->setName("Validar RUT Empleador");
        $actionTypeValidarRUTEmpleador->setCode("VRTE");
        $manager->persist($actionTypeValidarRUTEmpleador);

        $actionTypeValidarRegCivEmpleador = new ActionType();
        $actionTypeValidarRegCivEmpleador->setName("Validar Registro Civil Empleador");
        $actionTypeValidarRegCivEmpleador->setCode("VRCE");
        $manager->persist($actionTypeValidarRegCivEmpleador);

        $actionTypeValidarMandato = new ActionType();
        $actionTypeValidarMandato->setName('Validar Mandato');
        $actionTypeValidarMandato->setCode('VM');
        $manager->persist($actionTypeValidarMandato);

        $actionTypeValidarEntidadesEmpleador = new ActionType();
        $actionTypeValidarEntidadesEmpleador->setName("Validar Entidad Empleador");
        $actionTypeValidarEntidadesEmpleador->setCode("VENE");
        $manager->persist($actionTypeValidarEntidadesEmpleador);

        $actionTypeInscripcionEmleador = new ActionType();
        $actionTypeInscripcionEmleador->setName('Inscripción Empleador');
        $actionTypeInscripcionEmleador->setCode('INE');
        $manager->persist($actionTypeInscripcionEmleador);

        $actionTypeValidarRegistroEmpleado = new ActionType();
        $actionTypeValidarRegistroEmpleado->setName('Validar Información del Empleado');
        $actionTypeValidarRegistroEmpleado->setCode('VEE');
        $manager->persist($actionTypeValidarRegistroEmpleado);

        $actionTypeValidarDocumento = new ActionType();
        $actionTypeValidarDocumento->setName("Validar Documento Empleado");
        $actionTypeValidarDocumento->setCode("VDD");
        $manager->persist($actionTypeValidarDocumento);

        $actionTypeValidarRUT = new ActionType();
        $actionTypeValidarRUT->setName("Validar RUT Empleado");
        $actionTypeValidarRUT->setCode("VRT");
        $manager->persist($actionTypeValidarRUT);

        $actionTypeValidarCartaDeAutorizacion = new ActionType();
        $actionTypeValidarCartaDeAutorizacion->setName("Validar Carta Autorización Empleado");
        $actionTypeValidarCartaDeAutorizacion->setCode("VCAT");
        $manager->persist($actionTypeValidarCartaDeAutorizacion);

        $actionTypeValidarRegCiv = new ActionType();
        $actionTypeValidarRegCiv->setName("Validar Registro Civil Empleado");
        $actionTypeValidarRegCiv->setCode("VRC");
        $manager->persist($actionTypeValidarRegCiv);

        $actionTypeValidarEntidades = new ActionType();
        $actionTypeValidarEntidades->setName('Validar Entidad Empleado');
        $actionTypeValidarEntidades->setCode('VEN');
        $manager->persist($actionTypeValidarEntidades);

        $actionTypeInscripcion = new ActionType();
        $actionTypeInscripcion->setName('Inscripcion Empleado');
        $actionTypeInscripcion->setCode('IN');
        $manager->persist($actionTypeInscripcion);

        $actionTypeInscripcion = new ActionType();
        $actionTypeInscripcion->setName('Inscripcion Beneficiario Empleado');
        $actionTypeInscripcion->setCode('IBN');
        $manager->persist($actionTypeInscripcion);

        $actionTypeValidarContrato = new ActionType();
        $actionTypeValidarContrato->setName('Validar Contrato Empleado');
        $actionTypeValidarContrato->setCode('VC');
        $manager->persist($actionTypeValidarContrato);

        $actionTypeLlevarDocs = new ActionType();
        $actionTypeLlevarDocs->setName('LLevar Documentos a Entidad');
        $actionTypeLlevarDocs->setCode('LDE');
        $manager->persist($actionTypeLlevarDocs);

        $actionTypeSubirDocs = new ActionType();
        $actionTypeSubirDocs->setName('Subir Radicados Entidad');
        $actionTypeSubirDocs->setCode('SDE');
        $manager->persist($actionTypeSubirDocs);

        $actionTypeLlamarCliente = new ActionType();
        $actionTypeLlamarCliente->setName('Llamar Cliente');
        $actionTypeLlamarCliente->setCode('CCL');
        $manager->persist($actionTypeLlamarCliente);

        $actionTypeValidarDocumentosEmpleador = new ActionType();
        $actionTypeValidarDocumentosEmpleador->setName('Validar Documentos Empleador');
        $actionTypeValidarDocumentosEmpleador->setCode('VDCE');
        $manager->persist($actionTypeValidarDocumentosEmpleador);

        $actionTypeValidarDocumentos = new ActionType();
        $actionTypeValidarDocumentos->setName('Validar Documentos Empleado');
        $actionTypeValidarDocumentos->setCode('VDC');
        $manager->persist($actionTypeValidarDocumentos);

        $manager->flush();

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 6;
    }
}
    