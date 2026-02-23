<?php

namespace App\Dto;

class ProductDto
{
    public ?string $type = null;

    public ?string $name = null;
    public ?string $description = null;
    public ?float $price = null;

    public ?float $weight = null;
    public ?int $stock = null;
}