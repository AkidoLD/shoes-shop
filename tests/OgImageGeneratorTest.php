<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ShoesShop\OgImageGenerator;
use ShoesShop\Product;

/**
 * @internal
 *
 * @coversNothing
 */
final class OgImageGeneratorTest extends TestCase
{
    private string $directory;

    #[\Override]
    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/shoes-shop-og-'.bin2hex(random_bytes(6));
        mkdir($this->directory, 0o775, true);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->removeDirectory($this->directory);
    }

    public function testGenerateCreatesCenteredCropAtExpectedSize(): void
    {
        $source = $this->directory.'/source.jpg';
        $destination = $this->directory.'/og/product.jpg';
        $image = imagecreatetruecolor(1600, 900);

        $this->assertNotFalse($image);
        $color = imagecolorallocate($image, 200, 30, 30);
        $this->assertNotFalse($color);
        imagefilledrectangle($image, 0, 0, 1599, 899, $color);
        $this->assertTrue(imagejpeg($image, $source, 90));
        imagedestroy($image);

        $generator = new OgImageGenerator();
        $generator->generate($source, $destination);

        $this->assertFileExists($destination);
        $size = getimagesize($destination);

        $this->assertIsArray($size);
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }

    public function testGenerateForProductUsesDerivedPathWhenOgImageIsMissing(): void
    {
        $publicDirectory = $this->directory.'/public';
        mkdir($publicDirectory.'/assets/img', 0o775, true);
        $source = $publicDirectory.'/assets/img/sh999.jpg';
        $image = imagecreatetruecolor(800, 1200);

        $this->assertNotFalse($image);
        $color = imagecolorallocate($image, 20, 120, 200);
        $this->assertNotFalse($color);
        imagefilledrectangle($image, 0, 0, 799, 1199, $color);
        $this->assertTrue(imagejpeg($image, $source, 90));
        imagedestroy($image);

        $product = new Product(
            id: 1,
            ref: 'SH999',
            name: 'Test',
            description: '',
            price: 1000,
            oldPrice: null,
            currency: 'XAF',
            image: 'assets/img/sh999.jpg',
            ogImage: null,
            category: 'test',
            categoryLabel: 'Test',
            sizes: [],
            colors: [],
            featured: false,
            isNew: false,
            available: true,
        );

        $relativePath = new OgImageGenerator()->generateForProduct($product, $publicDirectory);

        $this->assertSame('assets/img/og/sh999.jpg', $relativePath);
        $this->assertFileExists($publicDirectory.'/'.$relativePath);
    }

    public function testGenerateThrowsForMissingSource(): void
    {
        $this->expectException(\RuntimeException::class);

        new OgImageGenerator()->generate(
            $this->directory.'/missing.jpg',
            $this->directory.'/out.jpg'
        );
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        if (false === $items) {
            return;
        }

        foreach ($items as $item) {
            if ('.' === $item || '..' === $item) {
                continue;
            }

            $path = $directory.'/'.$item;

            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }
}
