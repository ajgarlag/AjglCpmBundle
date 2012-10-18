<?php

namespace Ajgl\Bundle\CpmBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AjglCpmExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('ajgl_cpm.packages', $config['packages']);
        $container->setParameter('ajgl_cpm.install_dir', $config['install_dir']);
        $container->setParameter('ajgl_cpm.target_dir', $config['target_dir']);
    }
}
