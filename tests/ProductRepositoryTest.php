<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ShoesShop\Database;
use ShoesShop\Product;
use ShoesShop\ProductNotFoundException;
use ShoesShop\ProductRepository;

/**
 * @internal
 *
 * @coversNothing
 */
final class ProductRepositoryTest extends TestCase
{
    private \PDO $pdo;
    private ProductRepository $repository;

    #[\Override]
    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:', null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $this->pdo->exec('PRAGMA foreign_keys = ON');

        $root = dirname(__DIR__);
        Database::migrate($this->pdo, $root.'/database/schema.sql');
        Database::seed($this->pdo, $root.'/database/seed.sql');

        $this->repository = new ProductRepository($this->pdo);
    }

    public function testAllReturnsSeededProducts(): void
    {
        $products = $this->repository->all();

        $this->assertCount(8, $products);
        $this->assertSame('SH001', $products[0]->ref);
        $this->assertSame([39, 40, 41, 42, 43, 44], $products[0]->sizes);
        $this->assertSame(['Noir', 'Blanc'], $products[0]->colors);
    }

    public function testFindByRefReturnsNullWhenUnknown(): void
    {
        $this->assertNull($this->repository->findByRef('UNKNOWN'));
    }

    public function testCategoriesAreDerivedFromProducts(): void
    {
        $categories = $this->repository->categories();

        $this->assertSame(['sandales', 'sneakers', 'sport', 'ville'], array_keys($categories));
        $this->assertSame('Sneakers', $categories['sneakers']);
    }

    public function testSearchFiltersAndSorts(): void
    {
        $products = $this->repository->search('sneakers', 'all', 'price-asc');

        $this->assertSame(
            ['SH001', 'SH005'],
            array_map(static fn (Product $product): string => $product->ref, $products)
        );
    }

    public function testSearchByCategory(): void
    {
        $products = $this->repository->search('', 'sport', 'price-desc');

        $this->assertSame(
            ['SH007', 'SH003'],
            array_map(static fn (Product $product): string => $product->ref, $products)
        );
    }

    public function testCreatePersistsProductAndRelations(): void
    {
        $product = $this->repository->create([
            'ref' => 'sh009',
            'name' => 'Test Runner',
            'description' => 'Un produit de test.',
            'price' => 12345,
            'old_price' => 15000,
            'currency' => 'xaf',
            'image' => 'assets/img/sh009.jpg',
            'og_image' => '',
            'category' => 'test',
            'category_label' => 'Test',
            'sizes' => [40, 41, 41],
            'colors' => ['Noir', 'Bleu', 'Noir'],
            'available' => true,
            'featured' => true,
            'is_new' => true,
        ]);

        $this->assertSame('SH009', $product->ref);
        $this->assertSame([40, 41], $product->sizes);
        $this->assertSame(['Noir', 'Bleu'], $product->colors);
        $this->assertSame('Test', $product->categoryLabel);
        $this->assertSame('XAF', $product->currency);
        $this->assertNull($product->ogImage);
        $this->assertSame(9, $this->repository->count());
    }

    public function testUpdateMergesAndReplacesRelations(): void
    {
        $product = $this->repository->update('SH001', [
            'name' => 'Sneakers Urban 2',
            'price' => 16000,
            'sizes' => [42],
            'colors' => ['Blanc'],
        ]);

        $this->assertSame('Sneakers Urban 2', $product->name);
        $this->assertSame(16000, $product->price);
        $this->assertSame([42], $product->sizes);
        $this->assertSame(['Blanc'], $product->colors);
    }

    public function testUpdateUnknownProductThrows(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $this->repository->update('UNKNOWN', ['name' => 'X']);
    }

    public function testUpdateCanChangeRef(): void
    {
        $product = $this->repository->update('SH001', ['ref' => 'SH100']);

        $this->assertSame('SH100', $product->ref);
        $this->assertNull($this->repository->findByRef('SH001'));
        $this->assertNotNull($this->repository->findByRef('SH100'));
    }

    public function testDuplicateRefThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->repository->create([
            'ref' => 'SH001',
            'name' => 'Doublon',
            'price' => 1000,
            'image' => 'assets/img/sh001.jpg',
        ]);
    }

    public function testDeleteRemovesProductAndRelations(): void
    {
        $this->assertTrue($this->repository->delete('SH001'));
        $this->assertNull($this->repository->findByRef('SH001'));
        $this->assertSame(7, $this->repository->count());
        $this->assertSame(
            0,
            (int) $this->pdo->query('SELECT COUNT(*) FROM product_sizes WHERE product_id = 1')->fetchColumn()
        );
    }

    public function testSetOgImage(): void
    {
        $this->repository->setOgImage('SH001', 'assets/img/og/custom.jpg');
        $product = $this->repository->findByRef('SH001');

        $this->assertNotNull($product);
        $this->assertSame('assets/img/og/custom.jpg', $product->ogImage);
    }
}
