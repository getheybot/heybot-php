<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class Contact
{
    /**
     * @param  ContactPhone[]  $phones
     * @param  ContactEmail[]  $emails
     * @param  ContactAddress[]  $addresses
     * @param  ContactUrl[]  $urls
     */
    public function __construct(
        public ContactName $name,
        public array $phones,
        public array $emails,
        public array $addresses,
        public array $urls,
        public ?ContactOrg $org,
        public ?string $birthday,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            name: ContactName::fromArray($d['name']),
            phones: array_map(ContactPhone::fromArray(...), $d['phones'] ?? []),
            emails: array_map(ContactEmail::fromArray(...), $d['emails'] ?? []),
            addresses: array_map(ContactAddress::fromArray(...), $d['addresses'] ?? []),
            urls: array_map(ContactUrl::fromArray(...), $d['urls'] ?? []),
            org: isset($d['org']) ? ContactOrg::fromArray($d['org']) : null,
            birthday: $d['birthday'] ?? null,
        );
    }
}
