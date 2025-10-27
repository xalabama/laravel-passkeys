<?php

namespace Spatie\LaravelPasskeys\ValueObjects;

use Illuminate\Support\Arr;

readonly class RelyingParty
{
    public string $id;
    public function __construct(
        public string $name,
        string $id,
        public ?string $icon
    ) {
        $this->id = $this->normalizeId($id);
    }

    public static function fromArray(array $config): self
    {
        return new self($config['name'], $config['id'], Arr::get($config, 'icon'));
    }

    protected function normalizeId(string $id): string
    {
        if (str_starts_with($id, 'http://') || str_starts_with($id, 'https://')) {
            return parse_url($id, PHP_URL_HOST);
        }

        return $id;
    }
}
