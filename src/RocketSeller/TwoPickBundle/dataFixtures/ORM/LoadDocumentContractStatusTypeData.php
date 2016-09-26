<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\ContractDocumentStatusType;
use RocketSeller\TwoPickBundle\Entity\DocumentStatusType;

class LoadContractDocumentStatusTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $pendingContract= new ContractDocumentStatusType();
        $pendingContract->setName("Contract is pending");
        $pendingContract->setContractDocumentStatusCode("CTPE");
        $manager->persist($pendingContract);

        $contractUploaded= new ContractDocumentStatusType();
        $contractUploaded->setName("Contract was uploaded");
        $contractUploaded->setContractDocumentStatusCode("CTUP");
        $manager->persist($contractUploaded);

        $contractHasErrors= new ContractDocumentStatusType();
        $contractHasErrors->setName("Contract has errors");
        $contractHasErrors->setContractDocumentStatusCode("CTER");
        $manager->persist($contractHasErrors);

        $contractValidated= new ContractDocumentStatusType();
        $contractValidated->setName("Contract Validated");
        $contractValidated->setContractDocumentStatusCode("CTVA");
        $manager->persist($contractValidated);
	    
        $manager->flush();

        $this->addReference('contract-document-status-type-pending', $pendingContract);
        $this->addReference('contract-document-status-type-uploaded', $contractUploaded);
        $this->addReference('contract-document-status-type-error', $contractHasErrors);
        $this->addReference('contract-document-status-type-validated', $contractValidated);



    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}
