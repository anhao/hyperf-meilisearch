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

namespace Alapi\Meilisearch\Engine;

use Hyperf\Database\Model\Collection;
use Hyperf\Database\Model\Model;
use Hyperf\Scout\Builder;
use Hyperf\Scout\Engine\Engine;
use Hyperf\Utils\Collection as BaseCollection;
use MeiliSearch\Client as Meilisearch;
use MeiliSearch\Search\SearchResult;

class MeilisearchEngine extends Engine
{
    /**
     * @var Meilisearch
     */
    protected Meilisearch $meilisearch;

    /**
     * Create a new engine instance.
     */
    public function __construct(Meilisearch $meilisearch)
    {
        $this->meilisearch = $meilisearch;
    }

    public function __call($method, $parameters)
    {
        return $this->meilisearch->{$method}(...$parameters);
    }

    public function update(Collection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }
        $index = $this->meilisearch->index($models->first()->searchableAs());

        $objects = $models->map(function ($model) {
            if (empty($searchableData = $model->toSearchableArray())) {
                return;
            }
            return array_merge($searchableData, $model->scoutMetadata());
        })->filter()->values()->all();

        if (!empty($objects)) {
            $index->addDocuments($objects, $models->first()->getKeyName());
        }
    }

    public function delete(Collection $models): void
    {
        $index = $this->meilisearch->index($models->first()->searchableAs());

        $index->deleteDocuments(
            $models->map->getScoutKey()
                ->values()
                ->all()
        );
    }

    public function search(Builder $builder)
    {
        return $this->performSearch($builder, array_filter([
            'filters' => $this->filters($builder),
            'limit' => $builder->limit,
        ]));
    }

    public function paginate(Builder $builder, int $perPage, int $page)
    {
        return $this->performSearch($builder, array_filter([
            'filters' => $this->filters($builder),
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage,
        ]));
    }

    public function mapIds($results): BaseCollection
    {
        if (count($results['hits']) === 0) {
            return collect();
        }

        $hits = collect($results['hits']);
        $key = key($hits->first());

        return $hits->pluck($key)->values();
    }

    public function map(Builder $builder, $results, Model $model): Collection
    {
        if (is_null($results) || count($results['hits']) === 0) {
            return $model->newCollection();
        }

        $objectIds = collect($results['hits'])->pluck($model->getKeyName())->values()->all();
        $objectIdPositions = array_flip($objectIds);

        return $model->getScoutModelsByIds(
            $builder,
            $objectIds
        )->filter(function ($model) use ($objectIds) {
            return in_array($model->getScoutKey(), $objectIds);
        })->sortBy(function ($model) use ($objectIdPositions) {
            return $objectIdPositions[$model->getScoutKey()];
        })->values();
    }

    public function getTotalCount($results): int
    {
        return (int)$results['nbHits'];
    }

    public function flush(Model $model): void
    {
        $index = $this->meilisearch->index($model->searchableAs());
        $index->deleteAllDocuments();
    }

    protected function performSearch(Builder $builder, array $searchParams = [])
    {
        $meilisearch = $this->meilisearch->index($builder->index ?: $builder->model->searchableAs());

        if ($builder->callback) {
            $result = call_user_func(
                $builder->callback,
                $meilisearch,
                $builder->query,
                $searchParams
            );

            return $result instanceof SearchResult ? $result->getRaw() : $result;
        }

        return $meilisearch->rawSearch($builder->query, $searchParams);
    }

    /**
     * Get the filter array for the query.
     *
     * @return string
     */
    protected function filters(Builder $builder): string
    {
        return collect($builder->wheres)->map(function ($value, $key) {
            return sprintf('%s="%s"', $key, $value);
        })->values()->implode(' AND ');
    }
}
