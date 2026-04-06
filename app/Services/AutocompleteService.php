<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class AutocompleteService
{
    public function applySearch(Builder $query, string $term, array $searchFields): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }

        $tokens = array_values(array_filter(preg_split('/\s+/', $term)));

        $query->where(function ($q) use ($tokens, $searchFields) {
            foreach ($tokens as $token) {
                $like = "%{$token}%";

                $q->where(function ($sub) use ($like, $searchFields) {
                    foreach ($searchFields as $field) {
                        if (str_contains($field, '.')) {
                            $lastDot = strrpos($field, '.');
                            $relation = substr($field, 0, $lastDot);
                            $column = substr($field, $lastDot + 1);

                            $sub->orWhereRelation($relation, $column, 'like', $like);
                        } else {
                            $sub->orWhere($field, 'like', $like);
                        }
                    }
                });
            }
        });

        return $query;
    }

    public function search(Builder $query, string $term, array $searchFields, int $limit = 10): Collection
    {
        $limit = max(1, min(50, $limit));

        $this->applySearch($query, $term, $searchFields);

        return $query->limit($limit)->get();
    }

    public function response(Collection $results, callable $mapCallback): JsonResponse
    {
        $suggestions = $results->map($mapCallback);

        return response()->json([
            'data' => $suggestions,
        ]);
    }
}
