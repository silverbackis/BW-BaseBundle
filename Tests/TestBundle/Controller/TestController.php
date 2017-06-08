<?php

namespace BW\BaseBundle\Tests\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TestController extends Controller
{
    public function testAction()
    {
        return $this->render('@BWBase/base.html.twig');
    }

    public function testEmptyAction()
    {
        return $this->render('test.html.twig');
    }
}
