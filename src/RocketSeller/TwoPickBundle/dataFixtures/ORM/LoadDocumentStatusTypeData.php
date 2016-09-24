<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\DocumentStatusType;

class LoadDocumentStatusTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $documentStatusTypeAllDocsPending= new DocumentStatusType();
        $documentStatusTypeAllDocsPending->setName("All docs pending");
        $documentStatusTypeAllDocsPending->setDocumentStatusCode("ALLDCP");
        $manager->persist($documentStatusTypeAllDocsPending);

        $documentStatusTypeEmployeeDocsPending= new DocumentStatusType();
        $documentStatusTypeEmployeeDocsPending->setName("Employee docs pending");
        $documentStatusTypeEmployeeDocsPending->setDocumentStatusCode("EEDCPE");
        $manager->persist($documentStatusTypeEmployeeDocsPending);

        $documentStatusTypeEmployerDocsPending= new DocumentStatusType();
        $documentStatusTypeEmployerDocsPending->setName("Employer docs pending");
        $documentStatusTypeEmployerDocsPending->setDocumentStatusCode("ERDCPE");
        $manager->persist($documentStatusTypeEmployerDocsPending);

        $documentStatusTypeMessageDocsReady= new DocumentStatusType();
        $documentStatusTypeMessageDocsReady->setName("Message all docs ready");
        $documentStatusTypeMessageDocsReady->setDocumentStatusCode("ALLDCR");
        $manager->persist($documentStatusTypeMessageDocsReady);

        $documentStatusTypeAllDocsInValidation= new DocumentStatusType();
        $documentStatusTypeAllDocsInValidation->setName("All docs in validation");
        $documentStatusTypeAllDocsInValidation->setDocumentStatusCode("ALDCIV");
        $manager->persist($documentStatusTypeAllDocsInValidation);

        $documentStatusTypeEmployerDocsValidated= new DocumentStatusType();
        $documentStatusTypeEmployerDocsValidated->setName("Only employer docs validated");
        $documentStatusTypeEmployerDocsValidated->setDocumentStatusCode("ERDCVA");
        $manager->persist($documentStatusTypeEmployerDocsValidated);

        $documentStatusTypeEmployeeDocsValidated= new DocumentStatusType();
        $documentStatusTypeEmployeeDocsValidated->setName("Only employee docs validated");
        $documentStatusTypeEmployeeDocsValidated->setDocumentStatusCode("EEDCVA");
        $manager->persist($documentStatusTypeEmployeeDocsValidated);

        $documentStatusTypeEmployerDocsValidatedEmployeeDocsHadErrors= new DocumentStatusType();
        $documentStatusTypeEmployerDocsValidatedEmployeeDocsHadErrors->setName("Employer docs validated but employee docs had errors");
        $documentStatusTypeEmployerDocsValidatedEmployeeDocsHadErrors->setDocumentStatusCode("ERVEEE");
        $manager->persist($documentStatusTypeEmployerDocsValidatedEmployeeDocsHadErrors);

        $documentStatusTypeEmployeeDocsValidatedEmployerDocsHadErrors= new DocumentStatusType();
        $documentStatusTypeEmployeeDocsValidatedEmployerDocsHadErrors->setName("Employee docs validated but employer docs had errors");
        $documentStatusTypeEmployeeDocsValidatedEmployerDocsHadErrors->setDocumentStatusCode("EEVERE");
        $manager->persist($documentStatusTypeEmployeeDocsValidatedEmployerDocsHadErrors);

        $documentStatusTypeEmployerDocsError= new DocumentStatusType();
        $documentStatusTypeEmployerDocsError->setName("Only employer docs had errors");
        $documentStatusTypeEmployerDocsError->setDocumentStatusCode("ERDCE");
        $manager->persist($documentStatusTypeEmployerDocsError);

        $documentStatusTypeEmployeeDocsError= new DocumentStatusType();
        $documentStatusTypeEmployeeDocsError->setName("Only employee docs had errors");
        $documentStatusTypeEmployeeDocsError->setDocumentStatusCode("EEDCE");
        $manager->persist($documentStatusTypeEmployeeDocsError);

        $documentStatusTypeAllDocsError= new DocumentStatusType();
        $documentStatusTypeAllDocsError->setName("All docs had errors");
        $documentStatusTypeAllDocsError->setDocumentStatusCode("ALLDCE");
        $manager->persist($documentStatusTypeAllDocsError);

        $documentStatusTypeDocsErrorMessage= new DocumentStatusType();
        $documentStatusTypeDocsErrorMessage->setName("Docs had errors message");
        $documentStatusTypeDocsErrorMessage->setDocumentStatusCode("DCERRM");
        $manager->persist($documentStatusTypeDocsErrorMessage);

        $documentStatusTypeAllDocsValidatedMessage= new DocumentStatusType();
        $documentStatusTypeAllDocsValidatedMessage->setName("All docs validated message");
        $documentStatusTypeAllDocsValidatedMessage->setDocumentStatusCode("ALDCVM");
        $manager->persist($documentStatusTypeAllDocsValidatedMessage);

        $documentStatusTypeAllDocsValidated= new DocumentStatusType();
        $documentStatusTypeAllDocsValidated->setName("All docs validated");
        $documentStatusTypeAllDocsValidated->setDocumentStatusCode("ALDCVA");
        $manager->persist($documentStatusTypeAllDocsValidated);

        $documentStatusTypeBackOfficeFinishedMessage= new DocumentStatusType();
        $documentStatusTypeBackOfficeFinishedMessage->setName("Backoffice finished message");
        $documentStatusTypeBackOfficeFinishedMessage->setDocumentStatusCode("BOFFMS");
        $manager->persist($documentStatusTypeBackOfficeFinishedMessage);

        $documentStatusTypeBackOfficeFinished= new DocumentStatusType();
        $documentStatusTypeBackOfficeFinished->setName("Backoffice finished");
        $documentStatusTypeBackOfficeFinished->setDocumentStatusCode("BOFFFF");
        $manager->persist($documentStatusTypeBackOfficeFinished);

	    
        $manager->flush();

        $this->addReference('document-status-type-all-docs-pending', $documentStatusTypeAllDocsPending);
        $this->addReference('document-status-type-employee-docs-pending', $documentStatusTypeEmployeeDocsPending);
        $this->addReference('document-status-type-employer-docs-pending', $documentStatusTypeEmployerDocsPending);
        $this->addReference('document-status-type-all-docs-ready-message', $documentStatusTypeMessageDocsReady);
        $this->addReference('document-status-type-all-docs-in-validation', $documentStatusTypeAllDocsInValidation);
        $this->addReference('document-status-type-employer-docs-validated', $documentStatusTypeEmployerDocsValidated);
        $this->addReference('document-status-type-employee-docs-validated', $documentStatusTypeEmployeeDocsValidated);
        $this->addReference('document-status-type-employer-docs-validated-employee-docs-error', $documentStatusTypeEmployerDocsValidatedEmployeeDocsHadErrors);
        $this->addReference('document-status-type-employee-docs-validated-employer-docs-error', $documentStatusTypeEmployeeDocsValidatedEmployerDocsHadErrors);
        $this->addReference('document-status-type-employer-docs-error', $documentStatusTypeEmployerDocsError);
        $this->addReference('document-status-type-employee-docs-error', $documentStatusTypeEmployeeDocsError);
        $this->addReference('document-status-type-docs-error-message', $documentStatusTypeDocsErrorMessage);
        $this->addReference('document-status-type-all-docs-error', $documentStatusTypeAllDocsError);
        $this->addReference('document-status-type-all-docs-validated-message', $documentStatusTypeAllDocsValidatedMessage);
        $this->addReference('document-status-type-all-docs-validated', $documentStatusTypeAllDocsValidated);
        $this->addReference('document-status-type-back-office-finished-message', $documentStatusTypeBackOfficeFinishedMessage);
        $this->addReference('document-status-type-back-office-finished', $documentStatusTypeBackOfficeFinished);


    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}
