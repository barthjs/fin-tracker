<?php

declare(strict_types=1);

namespace App\Enums;

enum ApiAbility: string
{
    case ACCOUNT = 'account';
    case CATEGORY = 'category';
    case PORTFOLIO = 'portfolio';
    case SECURITY = 'security';
    case STATISTIC = 'statistic';
    case TRANSACTION = 'transaction';
    case TRADE = 'trade';

    public function read(): string
    {
        return $this->value.':read';
    }

    public function write(): string
    {
        return $this->value.':write';
    }

    /** @return array<string> */
    public function all(): array
    {
        return [$this->read(), $this->write()];
    }
}
