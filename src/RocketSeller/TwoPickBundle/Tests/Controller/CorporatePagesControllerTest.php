<?php

namespace RocketSeller\TwoPickBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CorporatePagesControllerTest extends WebTestCase
{
    public function testNosotros()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/nosotros');
    }

    public function testTerminoscondiciones()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'terminos-condiciones');
    }

    public function testPreguntasfrecuentes()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'preguntas-frecuentes');
    }

    public function testPoliticaprivacidad()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'politica-privacidad');
    }

}
