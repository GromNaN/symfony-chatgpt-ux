<?php

namespace App\MongoDB\Aggregation\Stage;

use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Doctrine\ODM\MongoDB\Aggregation\Stage;

/**
 * @todo migrate to Doctrine ODM repository
 * @todo add other fields https://www.mongodb.com/docs/atlas/atlas-search/text/#fields
 */
class Search extends Stage
{
    private ?string $index = null;
    private string|array $query;
    private string|array $path;
    private array $fuzzy;

    public function __construct(Builder $builder, string|array $query, string|array $path)
    {
        parent::__construct($builder);
        $this->query = $query;
        $this->path = $path;
    }

    public function index(string $index): self
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @param string|string[] $query
     */
    public function query(string|array $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param string|string[] $path
     */
    public function path(string|array $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getExpression(): array
    {
        $search = [
            'autocomplete' => [
                'query' => $this->query,
                'path' => $this->path,
            ],
        ];

        if ($this->index) {
            $search['index'] = $this->index;
        }

        if (isset($this->fuzzy)) {
            $search['text']['fuzzy'] = $this->fuzzy;
        }

        return ['$search' => $search];
    }
}
