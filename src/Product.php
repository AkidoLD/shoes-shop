<?php

declare(strict_types=1);

namespace ShoesShop;

final class Product
{
    /**
     * @param int[]    $sizes
     * @param string[] $colors
     */
    public function __construct(
        public readonly int $id,
        public readonly string $ref,
        public readonly string $name,
        public readonly string $description,
        public readonly int $price,
        public readonly ?int $oldPrice,
        public readonly string $currency,
        public readonly string $image,
        public readonly ?string $ogImage,
        public readonly string $category,
        public readonly string $categoryLabel,
        public readonly array $sizes,
        public readonly array $colors,
        public readonly bool $featured,
        public readonly bool $isNew,
        public readonly bool $available,
    ) {}

    /**
     * @param array<string, mixed> $row
     * @param array<int, mixed>    $sizes
     * @param array<int, mixed>    $colors
     */
    public static function fromRow(array $row, array $sizes = [], array $colors = []): self
    {
        $ogImage = $row['og_image'] ?? null;

        return new self(
            (int) $row['id'],
            (string) $row['ref'],
            (string) $row['name'],
            (string) $row['description'],
            (int) $row['price'],
            null === $row['old_price'] ? null : (int) $row['old_price'],
            (string) $row['currency'],
            (string) $row['image'],
            null === $ogImage || '' === (string) $ogImage ? null : (string) $ogImage,
            (string) ($row['category'] ?? ''),
            (string) ($row['category_label'] ?? ''),
            array_values(array_map(static fn (mixed $size): int => (int) $size, $sizes)),
            array_values(array_map(static fn (mixed $color): string => (string) $color, $colors)),
            (bool) $row['featured'],
            (bool) $row['is_new'],
            (bool) $row['available'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ref' => $this->ref,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'old_price' => $this->oldPrice,
            'currency' => $this->currency,
            'image' => $this->image,
            'og_image' => $this->ogImage,
            'category' => $this->category,
            'category_label' => $this->categoryLabel,
            'sizes' => $this->sizes,
            'colors' => $this->colors,
            'featured' => $this->featured,
            'is_new' => $this->isNew,
            'available' => $this->available,
        ];
    }
}
