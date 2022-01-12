# hyperf-meilisearch
基于 hyperf scout 的 meilisearch 包

## 环境要求 
- hyperf ^2.1
- hyperf scout ^2.1
- guzzlehttp ~7

## MeiliSearch
`MeiliSearch` 是一个快捷、易用且易部署的全文搜索引擎，支持自定义搜索和索引，支持容错搜索、过滤器和同义词。天生支持中文搜索。同时支持搜索结果词语高亮显示等。相比ElasticSearch，MeiliSearch更加轻量化。
官网地址：http://www.meilisearch.com/

## 安装
安装这个包的前提是已经安装了 hyperf-scout ，可以查看 hyperf 官方文档查看安装教程

引入组件包
```
composer require alapi/hyperf-meilisearch
```


发布资源，使用 vendor:publish 命令来生成 MeiliSearch 配置文件。这个命令将在你的 config 目录下生成一个 meilisearch.php 配置文件。
```
php bin/hyperf.php vendor:publish alapi/hyperf-meilisearch
```

## 配置 

在生成的 meilisearch.php 里配置你的 host 地址 和 key 秘钥， 然后修改 scout 的配置文件驱动为 meilisearch

在 scout 配置里增加一个meilisearch 引擎
```php
return [
    'default' => env('SCOUT_ENGINE',),#修改配置为 meilisearch
    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],
    'prefix' => env('SCOUT_PREFIX', ''),
    'soft_delete' => false,
    'concurrency' => 100,
    'engine' => [
         ...
        'meilisearch' => [ # 增加 meilisearch 引擎
            'driver' => \Alapi\Meilisearch\MeilisearchProvider::class,
        ]
    ],
];
```

配置完成后就可以正常使用了。