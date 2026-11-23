<?php

namespace App\Support;

final class ReadinessResult
{
    /**
     * @param  array<int, string>  $reasons
     */
    public function __construct(
        public readonly bool $ready,
        public readonly array $reasons = [],
    ) {}

    public function robotsMeta(): string
    {
        return $this->ready ? 'index, follow' : 'noindex, follow';
    }

    public function summary(): string
    {
        return $this->ready ? 'Ready' : implode('; ', $this->reasons);
    }
}
