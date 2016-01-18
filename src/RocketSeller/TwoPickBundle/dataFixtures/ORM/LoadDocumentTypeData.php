<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\DocumentType;

class LoadDocumentTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $documentTypeCedula = new DocumentType();              
        $documentTypeCedula->setName('Cedula');                
        $manager->persist($documentTypeCedula);

        $documentTypeRut = new DocumentType();
        $documentTypeRut->setName('Rut');                
        $manager->persist($documentTypeRut);

        $documentTypeContrato = new DocumentType();
        $documentTypeContrato->setName('Contrato');                
        $manager->persist($documentTypeContrato);

        $documentTypeCartaAut = new DocumentType();
        $documentTypeCartaAut->setName('Carta autorización Symplifica');                
        $manager->persist($documentTypeCartaAut);
        
        $manager->flush();

       
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}
    