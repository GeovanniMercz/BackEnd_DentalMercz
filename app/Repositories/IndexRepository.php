<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class IndexRepository
 */
class IndexRepository
{
    public Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function parseQueryParams(array $params)
    {
        return [
            'first' => $params['first'] ?? false,
            'rows' => $params['rows'] ?? false,
            'orderBy' => $params['orderBy'] ?? 'updated_at',
            'ascending' => $params['ascending'] ?? 0,
            'filters' => $filters = json_decode($params['filters'] ?? '{}', true),
            'columns' => isset($params['columns']) ? json_decode($params['columns']) : array_keys($filters),
        ];
    }

    public function getModelQuery($first, $rows, $orderBy, $ascending, $filters, $columns = null)
    {
        $query = $this->model::query()->selectRaw($this->model->getTable() . '.*');

        foreach ($filters as $column => $filter) {
            if ($filter['value'] !== null && $filter['value'] !== '' && $filter['value'] !== '%%') {
                $query->filterByColumn($column, $filter['value'], $filter['matchMode'] ?? null);
            }
        }

        $order = $ascending === '1' ? 'ASC' : 'DESC';
        $query->orderByColumn($orderBy, $order);

        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index($first, $rows, $orderBy, $ascending, $filters, $columns)
    {
        array_push($columns, 'id');

        $query = $this->getModelQuery($first, $rows, $orderBy, $ascending, $filters);
        if (method_exists($this->model, 'scopeWithAliasScopes')) {
            $query->withAliasScopes($columns);
        }

        $count = DB::query()->from($query)->count();

        if ($rows !== false && $first !== false) {
            $query->offset($first)->limit($rows);
        }

        $data = $query->get();

        $data = $data->map(function ($_data) use ($columns) {
            return $this->only($_data, $columns);
        });

        return [
            'data' => $data,
            'count' => $count,
        ];
    }

    public function only(&$model, $columns)
    {
        if ($model == null) {
            return null;
        }
        $model_columns = array_filter($columns, fn ($column) => ! is_array($column));
        $relationships = array_filter($columns, fn ($column) => is_array($column));

        if (is_array($model)) {
            $model = collect($model);
        }
        $model = $model->only([...$model_columns, ...array_map(fn ($relationship) => array_keys($relationship)[0], $relationships)]);

        foreach ($relationships as $relationship) {
            $relationship_name = array_keys($relationship)[0];

            $relationship_value = $model[$relationship_name] ? $model[$relationship_name] : null;
            if (is_a($relationship_value, 'Illuminate\Database\Eloquent\Collection')) {
                $model[$relationship_name] = $relationship_value->map(fn ($relationship_item) => $this->only($relationship_item, $relationship[$relationship_name]));
            } else {
                $model[$relationship_name] = $this->only($relationship_value, $relationship[$relationship_name]);
            }
        }

        return $model;
    }
}
