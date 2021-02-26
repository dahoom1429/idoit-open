<?php

namespace idoit\Module\Search;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SearchExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $configs   An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLoader = new YamlFileLoader($container, new FileLocator(\isys_module_search::get_dir()));
        $fileLoader->load('resources/services/index.yml');

        $compilerPass = new IndexDataCollectorCompilerPass();

        $container->addCompilerPass($compilerPass);
    }
}
