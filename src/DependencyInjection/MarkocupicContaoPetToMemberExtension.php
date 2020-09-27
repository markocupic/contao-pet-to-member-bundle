<?php

/**
 * This file is part of a markocupic Contao Bundle.
 *
 * (c) Marko Cupic 2020 <m.cupic@gmx.ch>
 * @author     Marko Cupic
 * @package    Formulartest
 * @license    MIT
 * @see        https://github.com/markocupic/contao-pet-to-member-bundle
 *
 */

declare(strict_types=1);

namespace Markocupic\ContaoPetToMemberBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class MarkocupicContaoPetToMemberExtension
 *
 * @package Markocupic\ContaoPetToMemberBundle\DependencyInjection
 */
class MarkocupicContaoPetToMemberExtension extends Extension
{

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('parameters.yml');
        $loader->load('services.yml');
        $loader->load('listener.yml');
    }
}
