<?php
// src/AppBundle/DataFixtures/ORM/LoadUserData.php

namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\ProcedureType;

class LoadProcedureTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $procedureTypeInscription = new ProcedureType();
        $procedureTypeInscription->setName('Registro empleador y empleados');
        $procedureTypeInscription->setCode('REE');
        $manager->persist($procedureTypeInscription);

        $procedureTypeAcciones = new ProcedureType();
        $procedureTypeAcciones->setName('Acciones de validación');
        $procedureTypeAcciones->setCode('VAC');
        $manager->persist($procedureTypeAcciones);

        $procedureTypePagoPila = new ProcedureType();
        $procedureTypePagoPila->setName('Pago de pila');
        $procedureTypePagoPila->setCode('PPL');
        $manager->persist($procedureTypePagoPila);

        $procedureTypeSubirPlanilla = new ProcedureType();
        $procedureTypeSubirPlanilla->setName('Subir Planillas');
        $procedureTypeSubirPlanilla->setCode('SPL');
        $manager->persist($procedureTypeSubirPlanilla);
        
        $manager->flush();

        $this->addReference('procedureType-registro', $procedureTypeInscription);
        $this->addReference('procedureType-pila', $procedureTypePagoPila);
        $this->addReference('procedureType-planilla', $procedureTypeSubirPlanilla);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 6;
    }
}
    