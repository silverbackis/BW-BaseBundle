<?php

namespace BW\BaseBundle\Tests;

use BW\BaseBundle\Tests\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerResponseTest extends WebTestCase
{
    public function testAutoAbsUrlTags()
    {
        $client = static::createClient(array(
          'environment' => 'test',
          'debug'       => true,
        ));

        $crawler = $client->request('GET', '/');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        
        // Check that the link tags are being added
        $this->assertEquals("http://localhost/bundles/app/images/favicons/apple-touch-icon-57x57.png", $crawler->filter('link')->eq(0)->attr('href'));
        $this->assertEquals("http://localhost/bundles/app/images/favicons/favicon-196x196.png", $crawler->filter('link')->eq(1)->attr('href'));
        // The config has 2 meta in sonata, 6 in bw_base
        // 1 meta for sonata is key non-existent for bw_base (test merge properly)
        // bw_base has metas for property key non-existent in sonata (test merge properly)
        $this->assertEquals(14, $crawler->filter('meta')->count());
        $this->assertEquals("http://localhost/bundles/app/images/opengraph/opengraph_image1.png", $crawler->filterXPath('//meta[@property=\'og:image\']')->attr('content'));

        $this->assertEquals(1, $crawler->filter('#sdk_facebook')->count(), "Facebook SDK is not present");
        $this->assertEquals(1, $crawler->filter('#sdk_twitter')->count(), "Twitter SDK is not present");
        $this->assertEquals(1, $crawler->filter('#sdk_google_analytics')->count(), "Google Analyitcs SDK is not present");
        $this->assertEquals(1, $crawler->filter('#sdk_woopra')->count(), "Woopra SDK is not present");
        $this->assertEquals(1, $crawler->filter('#sdk_facebook_pixel')->count(), "Facebook Pixel SDK is not present");

        // Test that we have automatically set the tags that we can
        $this->assertEquals(1, $crawler->filterXPath('//meta[@property=\'og:title\']')->count(), "og:title meta tag missing");
        $this->assertEquals($crawler->filter('title')->text(), $crawler->filterXPath('//meta[@property=\'og:title\']')->attr('content'), "og:title tag content attribute incorrect");

        $this->assertEquals(1, $crawler->filterXPath('//meta[@property=\'og:description\']')->count(), "og:description meta tag missing");
        $this->assertEquals('test description', $crawler->filterXPath('//meta[@property=\'og:description\']')->attr('content'), "og:description tag content attribute incorrect");
        
        $this->assertEquals(1, $crawler->filterXPath('//meta[@property=\'og:url\']')->count(), "og:url meta tag missing");
        $this->assertEquals('http://localhost/', $crawler->filterXPath('//meta[@property=\'og:url\']')->attr('content'), "og:url tag content attribute incorrect");
        
        $this->assertEquals(1, $crawler->filterXPath('//meta[@name=\'twitter:title\']')->count(), "twitter:title meta tag missing");
        $this->assertEquals($crawler->filter('title')->text(), $crawler->filterXPath('//meta[@name=\'twitter:title\']')->attr('content'), "twitter:title tag content attribute incorrect");
        
        $this->assertEquals(1, $crawler->filterXPath('//meta[@name=\'twitter:description\']')->count(), "twitter:description meta tag missing");
        $this->assertEquals('test description', $crawler->filterXPath('//meta[@name=\'twitter:description\']')->attr('content'), "twitter:description tag content attribute incorrect");
    }

    public function testNoConfig()
    {
        $client = static::createClient(array(
          'environment' => 'empty',
          'debug'       => true,
        ));
        $crawler = $client->request('GET', '/empty');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPartialConfig()
    {
        $client = static::createClient(array(
          'environment' => 'partial',
          'debug'       => true,
        ));
        $crawler = $client->request('GET', '/empty');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}
