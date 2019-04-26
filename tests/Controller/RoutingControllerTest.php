<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoutingControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        $buttonCrawlerNode = $crawler->selectButton('login');
        $form = $buttonCrawlerNode->form();
        
        $form = $buttonCrawlerNode->form(array(
            '_username'  => 'carlos',
            '_password'  => 'drrrr',
        ));
        
        $crawler = $client->submit($form);
        
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        
        $this->assertContains("Error",$crawler->filter('div#error')->first()->text());

    }
}