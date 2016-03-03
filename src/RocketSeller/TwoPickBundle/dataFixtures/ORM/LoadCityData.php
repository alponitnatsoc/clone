<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\City;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCityData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $path = $this->container->getParameter('kernel.root_dir') . "/../web/public/docs/data/colombia_ciudades.csv";
        $countryCode = 343; //Codigo de Colombia

        if (file_exists($path)) {
            $data = file($path);
            foreach ($data as $key => $city) {

                if ($key != 0) {
                    $datos = explode("," , $city);

                    $deptoCode = trim($datos[0]);
                    $cityCode = trim($datos[1]);
                    $cityName = ucwords(mb_strtolower(trim($datos[3]), "UTF8"));

                    $cityEntity = new City();
                    $cityEntity->setCityCode($cityCode);
                    $cityEntity->setDepartmentDepartment($this->getReference('c-code-' . $countryCode . '-d-code-' . $deptoCode));
                    $cityEntity->setName($cityName);

                    $manager->persist($cityEntity);

                    $this->addReference('c-code-' . $countryCode . 'd-code-' . $deptoCode . '-city-code-' . $cityCode, $cityEntity);
                }
            }
        }

        $manager->flush();
    }
    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 5;
    }
}