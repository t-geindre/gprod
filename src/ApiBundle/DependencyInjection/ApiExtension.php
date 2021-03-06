<?php
namespace ApiBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Extension class
 */
class ApiExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $this->flatten($config, 'api_bundle', $container);
    }

    /**
     * @param array            $arr
     * @param string           $parentKey
     * @param ContainerBuilder $container
     */
    protected function flatten(array $arr, string $parentKey, ContainerBuilder $container)
    {
        foreach ($arr as $key => $value) {
            $key = $parentKey.'.'.$key;
            if (is_array($value)) {
                $this->flatten($value, $key, $container);
                continue;
            }
            $container->setParameter($key, $value);
        }
    }
}
