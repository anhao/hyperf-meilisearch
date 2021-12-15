<?php

declare(strict_types=1);
/**
 * This file is part of ALAPI.
 *
 * @link     https://www.alapi.cn
 * @document https://www.alapi.cn
 * @contact  support@alapi.cn
 * @license  https://github.com/anhao/hyperf-meilisearch/blob/main/LICENSE
 */
namespace Alapi\Meilisearch;

use Alapi\Meilisearch\Engine\MeilisearchEngine;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\Scout\Engine\Engine;
use Hyperf\Scout\Provider\ProviderInterface;
use MeiliSearch\Client;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MeilisearchProvider implements ProviderInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function make(string $name): Engine
    {
        $config = $this->container->get(ConfigInterface::class);
        $client = $this->container->get(ClientFactory::class)->create();
        return new MeilisearchEngine(new Client($config->get('meilisearch.host'), $config->get('meilisearch.key'), $client));
    }
}
