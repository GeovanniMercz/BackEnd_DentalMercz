<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait UserScopes
 *
 *
 * @method  filterByColumn
 * @method  orderByColumn
 * @method  withRelationships
 */
trait UserScopes
{
    /**
     * Scope to filter by column.
     */
    public function scopeFilterByColumn(Builder $query, string $column, mixed $value): void
    {
        switch ($column) {
            default:
                if ($this->hasAliasScope($column)) {
                    $query->having($column, 'LIKE', '%' . trim($value, '%') . '%');

                    return;
                }
                if ($this->hasGetMutator($column)) {
                    return;
                }
                $query->where($column, 'LIKE', '%' . trim($value, '%') . '%');
                break;
        }
    }

    /**
     * Scope to order by column.
     */
    public function scopeOrderByColumn(Builder $query, string $orderBy, string $order): void
    {
        switch ($orderBy) {
            default:
                if ($this->hasGetMutator($orderBy)) {
                    return;
                }
                $query->orderBy($orderBy, $order);
                break;
        }
    }
}
