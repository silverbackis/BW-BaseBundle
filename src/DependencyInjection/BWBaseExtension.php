<?php
namespace BW\BaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
//use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

use Sonata\SeoBundle\DependencyInjection\SonataSeoExtension;
/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class BWBaseExtension extends SonataSeoExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        if( isset($config['page']) )
        {
            $this->configureBasePage($config['page'], $container);
        }
        $this->configureClassesToCompile();
    }

    /**
     * Add class to compile.
     */
    public function configureClassesToCompile()
    {
        $this->addClassesToCompile(array(
            'BW\\BaseBundle\\BWBaseInterface',
        ));
    }

    /**
     * Configure the extra page config vars
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function configureBasePage(array $config, ContainerBuilder $container)
    {
        $definition = $container->getDefinition( 'bw.base.page' );
        if( isset($config['links']) )
        {
            $definition->addMethodCall('setLinks', array($config['links']));
        }
        if( isset($config['metas']) )
        {
            $definition->addMethodCall('setMetas', array($config['metas']));
        }
        if( isset($config['js_sdk']) )
        {
            $definition->addMethodCall('setSDKs', array($config['js_sdk']));
        }
    }

    //protected function configureSeoPage(array $config, ContainerBuilder $container)
    //{}
}
