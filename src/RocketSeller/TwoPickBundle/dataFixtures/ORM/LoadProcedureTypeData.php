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
        $manager->persist($procedureTypeInscription);

        $procedureTypePagoPila = new ProcedureType();
        $procedureTypePagoPila->setName('Pago de pila');
        $manager->persist($procedureTypePagoPila);

        $procedureTypeSubirPlanilla = new ProcedureType();
        $procedureTypeSubirPlanilla->setName('Subir Planillas');
        $manager->persist($procedureTypeSubirPlanilla);

        $procedureTypeEmpleado = new ProcedureType();
        $procedureTypeEmpleado->setName('Registro empleado');
        $manager->persist($procedureTypeEmpleado);
        
        $manager->flush();

        $this->addReference('procedureType-registro', $procedureTypeInscription);
        $this->addReference('procedureType-pila', $procedureTypePagoPila);
        $this->addReference('procedureType-planilla', $procedureTypeSubirPlanilla);
        $this->addReference('procedureType-empleado', $procedureTypeEmpleado);
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 6;
    }
}
    