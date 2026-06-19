<?php

declare(strict_types=1);

namespace Heybot\Resources;

use Heybot\HeybotObject;

class Template extends Resource
{
    /**
     * Send message template.
     *
     * @param  array{limit?: int, after?: string, status?: string}  $params
     */
    public function send(array $params = []): HeybotObject
    {
        return $this->post('/template', $params);
    }
}
