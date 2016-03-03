<?php
namespace RocketSeller\TwoPickBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Country;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCountryData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $path = $this->container->getParameter('kernel.root_dir') . "/../web/public/docs/data/paises_colombia.csv";

        if (file_exists($path)) {
            $data = file($path);
            foreach ($data as $key => $country) {

                if ($key != 0) {
                    $datos = explode("," , $country);

                    $continente = trim($datos[0]);
                    $countryCode = trim($datos[1]);
                    $countryName = ucwords(strtolower(trim($datos[2])));

                    $countryEntity = new Country();
                    $countryEntity->setName($countryName);
                    $countryEntity->setCountryCode($countryCode);
                    $countryEntity->setContinente($continente);

                    $manager->persist($countryEntity);

                    $this->addReference('country-code-' . $countryCode, $countryEntity);
                }
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        // the order in which fixtures will be loaded
        // the lower the number, the sooner that this fixture is loaded
        return 3;
    }
}