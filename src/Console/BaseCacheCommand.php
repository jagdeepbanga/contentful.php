<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheItemPoolFactoryInterface;
use Contentful\Delivery\Client;
use Contentful\Delivery\ClientOptions;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class BaseCacheCommand extends Command
{
    /**
     * @return string
     */
    abstract protected function getCommandName(): string;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName($this->getCommandName())
            ->setDefinition([
                new InputOption('access-token', 't', InputOption::VALUE_REQUIRED, 'Token to access the space.'),
                new InputOption('space-id', 's', InputOption::VALUE_REQUIRED, 'ID of the space to use.'),
                new InputOption('environment-id', 'e', InputOption::VALUE_REQUIRED, 'ID of the environment to use', 'master'),
                new InputOption('factory-class', 'f', InputOption::VALUE_REQUIRED, \sprintf(
                    'The FQCN of a factory class which implements "%s".',
                    CacheItemPoolFactoryInterface::class
                )),
                new InputOption('use-preview', 'p', InputOption::VALUE_NONE, 'Use the Preview API instead of the Delivery API'),
                new InputOption('cache-content', 'c', InputOption::VALUE_NONE, 'Include entries and assets'),
            ])
        ;
    }

    /**
     * @param InputInterface $input
     *
     * @return Client
     */
    protected function getClient(InputInterface $input): Client
    {
        $accessToken = $input->getOption('access-token');
        $spaceId = $input->getOption('space-id');
        $environmentId = $input->getOption('environment-id');
        $options = new ClientOptions();
        if ($input->getOption('use-preview')) {
            $options = $options->usingPreviewApi();
        }

        return new Client($accessToken, $spaceId, $environmentId, $options);
    }

    /**
     * @param InputInterface $input
     * @param Client         $client
     *
     * @return CacheItemPoolInterface
     */
    protected function getCacheItemPool(InputInterface $input, Client $client): CacheItemPoolInterface
    {
        $factoryClass = $input->getOption('factory-class');
        $cacheItemPoolFactory = new $factoryClass();
        if (!$cacheItemPoolFactory instanceof CacheItemPoolFactoryInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Cache item pool factory must implement "%s".',
                CacheItemPoolFactoryInterface::class
            ));
        }

        return $cacheItemPoolFactory->getCacheItemPool(
            $client->getApi(),
            $client->getSpaceId(),
            $client->getEnvironmentId()
        );
    }
}