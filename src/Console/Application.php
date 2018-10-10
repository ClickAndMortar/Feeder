<?php
namespace Feeder\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Custom Console Application to allow injecting DI container
 *
 * @package Console\Console
 * @author  Michael BOUVY <michael.bouvy@clickandmortar.fr>
 */
class Application extends SymfonyApplication implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Get container
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
