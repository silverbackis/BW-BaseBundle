<?php

namespace BW\BaseBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use BW\BaseBundle\DependencyInjection\Compiler\SonataSeoExtensionPass;

class BWBaseBundle extends Bundle
{
	public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SonataSeoExtensionPass());
        $container->setParameter('favicon_base', 'bundles/app/images/favicons/');
        $container->setParameter('opengraph_base', 'bundles/app/images/opengraph/');
    }
}
