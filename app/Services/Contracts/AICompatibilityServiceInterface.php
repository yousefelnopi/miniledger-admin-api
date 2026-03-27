<?php

namespace App\Services\Contracts;

interface AICompatibilityServiceInterface
{
    /**
     * Generate compatible car models based on a query.
     *
     * @param string $query
     * @return array
     */
    public function generate(string $query): array;
}
