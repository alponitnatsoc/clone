<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Department;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadDepartmentData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $path = $this->container->getParameter('kernel.root_dir') . "/../web/public/docs/data/colombia_departamentos.csv";
        $countryCode = 343; //Codigo de Colombia

        if (file_exists($path)) {
            $data = file($path);
            foreach ($data as $key => $depto) {

                if ($key != 0) {
                    $datos = explode("," , $depto);

                    $deptoCode = trim($datos[0]);
                    $deptoName = trim($datos[1]);

                    $departament = new Department();
                    $departament->setCountryCountry($this->getReference('country-code-' . $countryCode));
                    $departament->setDepartmentCode($deptoCode);
                    $departament->setName($deptoName);

                    $manager->persist($departament);

                    $this->addReference('c-code-' . $countryCode . '-d-code-' . $deptoCode, $departament);
                }
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 4;
    }
}