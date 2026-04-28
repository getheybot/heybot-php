<?php

declare(strict_types=1);

namespace Heybot\Resources;

use Heybot\HeybotObject;

class Template extends Resource
{
    /**
     * List approved message templates.
     *
     * @param  array{limit?: int, after?: string, status?: string}  $params
     */
    public function list(array $params = []): HeybotObject
    {
        return $this->get('/template', $params);
    }

    /**
     * Retrieve a single template by ID.
     */
    public function retrieve(string $id): HeybotObject
    {
        return $this->get("/template/{$id}");
    }
}
