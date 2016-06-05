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
        $actionTypeValidarRegistroEmpleador->setName('Validar Informacion del Empleador');
        $actionTypeValidarRegistroEmpleador->setCode('VER');
        $manager->persist($actionTypeValidarRegistroEmpleador);

        $actionTypeValidarRegistroEmpleado = new ActionType();
        $actionTypeValidarRegistroEmpleado->setName('Validar Informacion del Empleado');
        $actionTypeValidarRegistroEmpleado->setCode('VEE');
        $manager->persist($actionTypeValidarRegistroEmpleado);

        $actionTypeValidarEntidades = new ActionType();
        $actionTypeValidarEntidades->setName('Validar Entidad');
        $actionTypeValidarEntidades->setCode('VEN');
        $manager->persist($actionTypeValidarEntidades);

        $actionTypeValidarDocumentos = new ActionType();
        $actionTypeValidarDocumentos->setName('Validar Documentos');
        $actionTypeValidarDocumentos->setCode('VDC');
        $manager->persist($actionTypeValidarDocumentos);

        $actionTypeLlevarDocs = new ActionType();
        $actionTypeLlevarDocs->setName('LLevar Documentos a Entidad');
        $actionTypeLlevarDocs->setCode('LDE');
        $manager->persist($actionTypeLlevarDocs);

        $actionTypeInscripcion = new ActionType();
        $actionTypeInscripcion->setName('Inscripcion');
        $actionTypeInscripcion->setCode('IN');
        $manager->persist($actionTypeInscripcion);

        $actionTypeValidarInfoRegistrada = new ActionType();
        $actionTypeValidarInfoRegistrada->setName('Validar Informacion Registrada');
        $actionTypeValidarInfoRegistrada->setCode('VIR');
        $manager->persist($actionTypeValidarInfoRegistrada);

        $actionTypeLlamarCliente = new ActionType();
        $actionTypeLlamarCliente->setName('Llamar Cliente');
        $actionTypeLlamarCliente->setCode('CCL');
        $manager->persist($actionTypeLlamarCliente);

        
        $manager->flush();
        $this->addReference('actionType-validarEmpleador', $actionTypeValidarRegistroEmpleador);
        $this->addReference('actionType-validarEmpleado', $actionTypeValidarRegistroEmpleado);
        $this->addReference('actionType-validarEntidades', $actionTypeValidarEntidades);
        $this->addReference('actionType-validarDocumentos', $actionTypeValidarDocumentos);
        $this->addReference('actionType-llevarDocs', $actionTypeLlevarDocs);
        $this->addReference('actionType-inscripcion', $actionTypeInscripcion);
        $this->addReference('actionType-validarInfo', $actionTypeValidarInfoRegistrada);
        $this->addReference('actionType-llamarCliente', $actionTypeLlamarCliente);

    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 6;
    }
}
    