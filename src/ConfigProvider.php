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

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for meilisearch.',
                    'source' => __DIR__ . '/../publish/meilisearch.php',
                    'destination' => BASE_PATH . '/config/autoload/meilisearch.php',
                ],
            ],
        ];
    }
}
