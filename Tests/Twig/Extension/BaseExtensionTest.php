<?php

namespace BW\BaseBundle\Tests\Twig\Extension;

use BW\BaseBundle\Tests\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseExtensionTest extends WebTestCase
{
    public function testServiceIsDefinedInContainer()
    {
        $client = static::createClient();
        $service = $client->getKernel()->getContainer()->get('bw.base.page');

        $this->assertInstanceOf('BW\BaseBundle\BasePage', $service);
    }

    public function testBaseUrlTags()
    {
        $client = static::createClient(array(
          'environment' => 'test_asset_base_urls',
          'debug'       => true,
        ));
        $twig_service = $client->getKernel()->getContainer()->get('bw.base.page');
        // Check that the link tags are being added
        $this->assertEquals("http://cdn.b-w.uk/test/url.png", $twig_service->getUrl('test/url.png'));
        $this->assertTrue($twig_service->hasSDK('twitter'));
    }

    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}