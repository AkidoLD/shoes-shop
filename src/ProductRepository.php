<?php

declare(strict_types=1);

namespace ShoesShop;

final class ProductRepository
{
    private const BASE_SELECT = <<<'SQL'
        SELECT
            p.id,
            p.ref,
            p.name,
            p.description,
            p.price,
            p.old_price,
            p.currency,
            p.image,
            p.og_image,
            p.available,
            p.featured,
            p.is_new,
            COALESCE(c.code, '') AS category,
            COALESCE(c.label, '') AS category_label
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        SQL;

    public function __construct(private readonly \PDO $pdo) {}

    /**
     * @return Product[]
     */
    public function all(): array
    {
        $statement = $this->pdo->query(self::BASE_SELECT.' ORDER BY p.id ASC');

        return $this->hydrate($statement->fetchAll());
    }

    public function findByRef(string $ref): ?Product
    {
        $statement = $this->pdo->prepare(self::BASE_SELECT.' WHERE p.ref = :ref');
        $statement->execute(['ref' => $ref]);
        $row = $statement->fetch();

        if (false === $row) {
            return null;
        }

        return $this->hydrate([$row])[0];
    }

    /**
     * @return array<string, string>
     */
    public function categories(): array
    {
        $statement = $this->pdo->query(
            'SELECT c.code, c.label
             FROM categories c
             WHERE EXISTS (SELECT 1 FROM products p WHERE p.category_id = c.id)
             ORDER BY c.label COLLATE NOCASE ASC'
        );

        $categories = [];

        foreach ($statement->fetchAll() as $row) {
            $categories[(string) $row['code']] = (string) $row['label'];
        }

        return $categories;
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }

    /**
     * @return Product[]
     */
    public function search(string $search = '', string $category = 'all', string $sort = 'default'): array
    {
        $sql = self::BASE_SELECT;
        $conditions = [];
        $parameters = [];
        $search = trim($search);

        if ('' !== $search) {
            $conditions[] = '(p.name LIKE :search_name OR p.ref LIKE :search_ref OR p.description LIKE :search_description OR c.label LIKE :search_category)';
            $term = '%'.$search.'%';
            $parameters = [
                'search_name' => $term,
                'search_ref' => $term,
                'search_description' => $term,
                'search_category' => $term,
            ];
        }

        if ('all' !== $category) {
            $conditions[] = 'c.code = :category';
            $parameters['category'] = $category;
        }

        if ([] !== $conditions) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }

        $sql .= match ($sort) {
            'price-asc' => ' ORDER BY p.price ASC, p.name COLLATE NOCASE ASC',
            'price-desc' => ' ORDER BY p.price DESC, p.name COLLATE NOCASE ASC',
            'name-asc' => ' ORDER BY p.name COLLATE NOCASE ASC',
            default => ' ORDER BY p.id ASC',
        };

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return $this->hydrate($statement->fetchAll());
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Product
    {
        $data = $this->normalize($data);
        $this->pdo->beginTransaction();

        try {
            $categoryId = $this->categoryId($data['category'], $data['category_label']);
            $statement = $this->pdo->prepare(
                'INSERT INTO products
                    (ref, name, description, price, old_price, currency, image, og_image, category_id, available, featured, is_new)
                 VALUES
                    (:ref, :name, :description, :price, :old_price, :currency, :image, :og_image, :category_id, :available, :featured, :is_new)'
            );
            $statement->execute([
                'ref' => $data['ref'],
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'old_price' => $data['old_price'],
                'currency' => $data['currency'],
                'image' => $data['image'],
                'og_image' => $data['og_image'],
                'category_id' => $categoryId,
                'available' => $data['available'] ? 1 : 0,
                'featured' => $data['featured'] ? 1 : 0,
                'is_new' => $data['is_new'] ? 1 : 0,
            ]);

            $productId = (int) $this->pdo->lastInsertId();
            $this->replaceSizes($productId, $data['sizes']);
            $this->replaceColors($productId, $data['colors']);
            $this->pdo->commit();
        } catch (\Throwable $error) {
            $this->pdo->rollBack();

            throw $this->translateWriteError($error);
        }

        $product = $this->findByRef($data['ref']);

        if (null === $product) {
            throw new \RuntimeException('Le produit créé est introuvable.');
        }

        return $product;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $ref, array $data): Product
    {
        $existing = $this->findByRef($ref);

        if (null === $existing) {
            throw ProductNotFoundException::forRef($ref);
        }

        $data = $this->normalize(array_merge($existing->toArray(), $data), $ref);
        $this->pdo->beginTransaction();

        try {
            $categoryId = $this->categoryId($data['category'], $data['category_label']);
            $statement = $this->pdo->prepare(
                'UPDATE products SET
                    ref = :ref,
                    name = :name,
                    description = :description,
                    price = :price,
                    old_price = :old_price,
                    currency = :currency,
                    image = :image,
                    og_image = :og_image,
                    category_id = :category_id,
                    available = :available,
                    featured = :featured,
                    is_new = :is_new,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE ref = :current_ref'
            );
            $statement->execute([
                'ref' => $data['ref'],
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'old_price' => $data['old_price'],
                'currency' => $data['currency'],
                'image' => $data['image'],
                'og_image' => $data['og_image'],
                'category_id' => $categoryId,
                'available' => $data['available'] ? 1 : 0,
                'featured' => $data['featured'] ? 1 : 0,
                'is_new' => $data['is_new'] ? 1 : 0,
                'current_ref' => $ref,
            ]);

            $this->replaceSizes((int) $existing->id, $data['sizes']);
            $this->replaceColors((int) $existing->id, $data['colors']);
            $this->pdo->commit();
        } catch (\Throwable $error) {
            $this->pdo->rollBack();

            throw $this->translateWriteError($error);
        }

        $product = $this->findByRef($data['ref']);

        if (null === $product) {
            throw new \RuntimeException('Le produit mis à jour est introuvable.');
        }

        return $product;
    }

    public function delete(string $ref): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM products WHERE ref = :ref');
        $statement->execute(['ref' => $ref]);

        return $statement->rowCount() > 0;
    }

    public function setOgImage(string $ref, string $path): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE products SET og_image = :og_image, updated_at = CURRENT_TIMESTAMP WHERE ref = :ref'
        );
        $statement->execute([
            'og_image' => '' === trim($path) ? null : trim($path),
            'ref' => $ref,
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return Product[]
     */
    private function hydrate(array $rows): array
    {
        if ([] === $rows) {
            return [];
        }

        $ids = array_map(static fn (array $row): int => (int) $row['id'], $rows);
        $sizes = $this->fetchValues('product_sizes', 'size', $ids);
        $colors = $this->fetchValues('product_colors', 'color', $ids);

        return array_map(
            static fn (array $row): Product => Product::fromRow(
                $row,
                $sizes[(int) $row['id']] ?? [],
                $colors[(int) $row['id']] ?? []
            ),
            $rows
        );
    }

    /**
     * @param int[] $ids
     *
     * @return array<int, array<int, int|string>>
     */
    private function fetchValues(string $table, string $column, array $ids): array
    {
        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $statement = $this->pdo->prepare(
            'SELECT product_id, '.$column.' FROM '.$table.' WHERE product_id IN ('.$placeholders.') ORDER BY product_id ASC, position ASC'
        );

        foreach ($ids as $index => $id) {
            $statement->bindValue($index + 1, $id, \PDO::PARAM_INT);
        }

        $statement->execute();
        $values = [];

        foreach ($statement->fetchAll() as $row) {
            $values[(int) $row['product_id']][] = $row[$column];
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function normalize(array $data, ?string $existingRef = null): array
    {
        $ref = trim((string) ($data['ref'] ?? ''));
        $ref = '' === $ref ? (string) $existingRef : $ref;
        $ref = strtoupper($ref);

        if (1 !== preg_match('/^[A-Z0-9][A-Z0-9_-]*$/', $ref)) {
            throw new \InvalidArgumentException('La référence doit contenir uniquement des lettres, chiffres, tirets ou underscores.');
        }

        $name = trim((string) ($data['name'] ?? ''));

        if ('' === $name) {
            throw new \InvalidArgumentException('Le nom du produit est obligatoire.');
        }

        $image = trim((string) ($data['image'] ?? ''));

        if ('' === $image) {
            throw new \InvalidArgumentException('L’image du produit est obligatoire.');
        }

        if (!array_key_exists('price', $data) || !is_numeric($data['price'])) {
            throw new \InvalidArgumentException('Le prix du produit est obligatoire.');
        }

        $price = (int) $data['price'];

        if ($price < 0) {
            throw new \InvalidArgumentException('Le prix ne peut pas être négatif.');
        }

        $oldPrice = $data['old_price'] ?? null;

        if (null === $oldPrice || '' === $oldPrice) {
            $oldPrice = null;
        } elseif (!is_numeric($oldPrice)) {
            throw new \InvalidArgumentException('L’ancien prix doit être un nombre.');
        } else {
            $oldPrice = (int) $oldPrice;
        }

        if (null !== $oldPrice && $oldPrice < 0) {
            throw new \InvalidArgumentException('L’ancien prix ne peut pas être négatif.');
        }

        $currency = strtoupper(trim((string) ($data['currency'] ?? 'XAF')));
        $currency = '' === $currency ? 'XAF' : $currency;
        $category = trim((string) ($data['category'] ?? ''));
        $categoryLabel = trim((string) ($data['category_label'] ?? ''));
        $ogImage = trim((string) ($data['og_image'] ?? ''));

        return [
            'ref' => $ref,
            'name' => $name,
            'description' => trim((string) ($data['description'] ?? '')),
            'price' => $price,
            'old_price' => $oldPrice,
            'currency' => $currency,
            'image' => $image,
            'og_image' => '' === $ogImage ? null : $ogImage,
            'category' => $category,
            'category_label' => '' === $categoryLabel ? $category : $categoryLabel,
            'sizes' => $this->normalizeSizes($data['sizes'] ?? []),
            'colors' => $this->normalizeColors($data['colors'] ?? []),
            'available' => $this->toBool($data['available'] ?? true),
            'featured' => $this->toBool($data['featured'] ?? false),
            'is_new' => $this->toBool($data['is_new'] ?? false),
        ];
    }

    /**
     * @return int[]
     */
    private function normalizeSizes(mixed $sizes): array
    {
        if (!is_array($sizes)) {
            throw new \InvalidArgumentException('Les tailles doivent être une liste.');
        }

        $normalized = [];

        foreach ($sizes as $size) {
            if (!is_numeric($size)) {
                throw new \InvalidArgumentException('Chaque taille doit être un nombre.');
            }

            $normalized[] = (int) $size;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @return string[]
     */
    private function normalizeColors(mixed $colors): array
    {
        if (!is_array($colors)) {
            throw new \InvalidArgumentException('Les couleurs doivent être une liste.');
        }

        $normalized = [];

        foreach ($colors as $color) {
            $color = trim((string) $color);

            if ('' !== $color) {
                $normalized[] = $color;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
    }

    private function categoryId(string $code, string $label): ?int
    {
        $code = trim($code);

        if ('' === $code) {
            return null;
        }

        $label = '' === trim($label) ? $code : trim($label);
        $statement = $this->pdo->prepare('SELECT id FROM categories WHERE code = :code');
        $statement->execute(['code' => $code]);
        $id = $statement->fetchColumn();

        if (false !== $id) {
            $update = $this->pdo->prepare('UPDATE categories SET label = :label WHERE id = :id');
            $update->execute(['label' => $label, 'id' => $id]);

            return (int) $id;
        }

        $insert = $this->pdo->prepare('INSERT INTO categories (code, label) VALUES (:code, :label)');
        $insert->execute(['code' => $code, 'label' => $label]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param int[] $sizes
     */
    private function replaceSizes(int $productId, array $sizes): void
    {
        $this->pdo->prepare('DELETE FROM product_sizes WHERE product_id = :product_id')
            ->execute(['product_id' => $productId])
        ;
        $insert = $this->pdo->prepare(
            'INSERT INTO product_sizes (product_id, size, position) VALUES (:product_id, :size, :position)'
        );

        foreach (array_values($sizes) as $position => $size) {
            $insert->execute([
                'product_id' => $productId,
                'size' => $size,
                'position' => $position,
            ]);
        }
    }

    /**
     * @param string[] $colors
     */
    private function replaceColors(int $productId, array $colors): void
    {
        $this->pdo->prepare('DELETE FROM product_colors WHERE product_id = :product_id')
            ->execute(['product_id' => $productId])
        ;
        $insert = $this->pdo->prepare(
            'INSERT INTO product_colors (product_id, color, position) VALUES (:product_id, :color, :position)'
        );

        foreach (array_values($colors) as $position => $color) {
            $insert->execute([
                'product_id' => $productId,
                'color' => $color,
                'position' => $position,
            ]);
        }
    }

    private function translateWriteError(\Throwable $error): \Throwable
    {
        $message = $error->getMessage();

        if (str_contains($message, 'UNIQUE constraint failed: products.ref')) {
            return new \InvalidArgumentException('Cette référence produit existe déjà.', 0, $error);
        }

        return $error;
    }
}
