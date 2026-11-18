<?php

declare(strict_types=1);

namespace ShoesShop;

final class ProductNotFoundException extends \RuntimeException
{
    public static function forRef(string $ref): self
    {
        return new self('Produit introuvable : '.$ref);
    }
}
