<?php

namespace RocketSeller\TwoPickBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/hello/Fabien');

        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
    }
    public function testAction()
    {
    	$this->forward('RocketSeller.TwoPickBundle:ProcedureController:validateAction');
    }
}
