<?php

namespace idoit\Module\Search;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class IndexDataCollectorCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $managerDefinition = $container->getDefinition('idoit.search.index.manager');

        $collectors = $container->findTaggedServiceIds('idoit.search.index.data.collector');

        foreach ($collectors as $collector => $tags) {
            $managerDefinition->addMethodCall('addCollector', [
                new Reference($collector),
                $collector
            ]);
        }
    }
}
