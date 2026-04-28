<?php

declare(strict_types=1);

namespace Heybot\Resources;

use Heybot\HeybotObject;

class Contact extends Resource
{
    /**
     * Create or update a contact.
     *
     * @param  array{phone: string, name?: string, metadata?: array<string, mixed>}  $params
     */
    public function create(array $params): HeybotObject
    {
        return $this->post('/contacts', $params);
    }

    /**
     * Retrieve a contact by ID.
     */
    public function retrieve(string $id): HeybotObject
    {
        return $this->get("/contacts/{$id}");
    }

    /**
     * List contacts.
     *
     * @param  array{limit?: int, after?: string}  $params
     */
    public function list(array $params = []): HeybotObject
    {
        return $this->get('/contacts', $params);
    }

    /**
     * Delete a contact.
     */
    public function delete(string $id): HeybotObject
    {
        return $this->delete("/contacts/{$id}");
    }
}
