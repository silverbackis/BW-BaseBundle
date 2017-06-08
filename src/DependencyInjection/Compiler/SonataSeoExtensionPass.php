<?php

namespace BW\BaseBundle\DependencyInjection\Compiler;

use BW\BaseBundle\Twig\Extension\BaseExtension;
use BW\BaseBundle\Base;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class SonataSeoExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition( 'sonata.seo.twig.extension' )) {
            return;
        }
        // Get the sonata seo twig extension
        $TwigExtension = $container->getDefinition( 'sonata.seo.twig.extension' );
        // Modify the sonata seo twig extension class and parameters
        $TwigExtension->setClass( BaseExtension::class );
        $TwigExtension->addArgument( new Reference('bw.base.page') );
    }
}