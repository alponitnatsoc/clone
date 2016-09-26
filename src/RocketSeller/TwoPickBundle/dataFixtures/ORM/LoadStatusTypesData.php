<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\ContractDocumentStatusType;
use RocketSeller\TwoPickBundle\Entity\StatusTypes;

class LoadStatusTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $statusPendingDocs = new StatusTypes( "Desabilitado" , "DIS" );
        $manager->persist($statusPendingDocs);

        $statusPendingDocs = new StatusTypes( "Documentos pendientes" , "DCPE" );
        $manager->persist($statusPendingDocs);

        $statusNew = new StatusTypes( "Nuevo" , "NEW" );
        $manager->persist( $statusNew );

        $statusStarted = new StatusTypes( "En tramite" , "STRT" );
        $manager->persist($statusStarted);

        $statusError = new StatusTypes( "Error" , "ERRO" );
        $manager->persist($statusError);

        $statusCorrected = new StatusTypes( "Corregido" , "CORT" );
        $manager->persist($statusCorrected);

        $statusFinished = new StatusTypes( "Terminado" , "FIN" );
        $manager->persist($statusFinished);

        $statusPendingContract = new StatusTypes( "Contrato pendiente" , "CTPE" );
        $manager->persist($statusPendingContract);

        $statusContractFinished = new StatusTypes( "Contrato Validado" , "CTVA" );
        $manager->persist($statusContractFinished);
	    
        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}
