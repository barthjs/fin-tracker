<?php

declare(strict_types=1);

namespace App\Contracts;

interface Chartable
{
    public function getKey(): string;

    public function getChartLabel(): string;

    public function getChartColor(): string;
}
