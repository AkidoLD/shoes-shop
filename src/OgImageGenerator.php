<?php

declare(strict_types=1);

namespace ShoesShop;

final class OgImageGenerator
{
    public const WIDTH = 1200;
    public const HEIGHT = 630;
    public const QUALITY = 88;

    public function __construct(
        private readonly int $width = self::WIDTH,
        private readonly int $height = self::HEIGHT,
        private readonly int $quality = self::QUALITY,
    ) {
        if ($width < 1 || $height < 1) {
            throw new \InvalidArgumentException('Les dimensions de l’image OG doivent être positives.');
        }

        if ($quality < 0 || $quality > 100) {
            throw new \InvalidArgumentException('La qualité JPEG doit être comprise entre 0 et 100.');
        }
    }

    public function generate(string $sourcePath, string $destinationPath): void
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('L’extension GD est requise pour générer les images OG.');
        }

        if (!function_exists('imagejpeg')) {
            throw new \RuntimeException('GD doit être compilé avec le support JPEG pour générer les images OG.');
        }

        $source = $this->createImage($sourcePath);
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $targetRatio = $this->width / $this->height;
        $sourceRatio = $sourceWidth / $sourceHeight;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $sourceX = (int) floor(($sourceWidth - $cropWidth) / 2);
            $sourceY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $sourceX = 0;
            $sourceY = (int) floor(($sourceHeight - $cropHeight) / 2);
        }

        $canvas = imagecreatetruecolor($this->width, $this->height);

        if (false === $canvas) {
            throw new \RuntimeException('Impossible de créer l’image OG.');
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);

        if (false !== $white) {
            imagefilledrectangle($canvas, 0, 0, $this->width, $this->height, $white);
        }

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            $sourceX,
            $sourceY,
            $this->width,
            $this->height,
            $cropWidth,
            $cropHeight
        );
        imageinterlace($canvas, true);

        $directory = dirname($destinationPath);

        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Impossible de créer le dossier des images OG : '.$directory);
        }

        if (!imagejpeg($canvas, $destinationPath, $this->quality)) {
            throw new \RuntimeException('Impossible d’écrire l’image OG : '.$destinationPath);
        }

        imagedestroy($canvas);
        imagedestroy($source);
    }

    public function generateForProduct(Product $product, string $publicDirectory): string
    {
        $publicDirectory = rtrim($publicDirectory, '/');
        $relativePath = $product->ogImage;

        if (null === $relativePath || '' === $relativePath) {
            $relativePath = 'assets/img/og/'.strtolower($product->ref).'.jpg';
        }

        $relativePath = ltrim($relativePath, '/');
        $source = $this->resolvePath($publicDirectory, $product->image);
        $destination = $this->resolvePath($publicDirectory, $relativePath);
        $this->generate($source, $destination);

        return $relativePath;
    }

    private function createImage(string $path): \GdImage
    {
        if (!is_file($path)) {
            throw new \RuntimeException('Image source introuvable : '.$path);
        }

        $info = getimagesize($path);

        if (false === $info) {
            throw new \RuntimeException('Format d’image non reconnu : '.$path);
        }

        $image = match ($info['mime'] ?? '') {
            'image/jpeg' => function_exists('imagecreatefromjpeg') ? imagecreatefromjpeg($path) : false,
            'image/png' => function_exists('imagecreatefrompng') ? imagecreatefrompng($path) : false,
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => false,
        };

        if (!$image instanceof \GdImage) {
            throw new \RuntimeException('Impossible de lire l’image : '.$path);
        }

        return $image;
    }

    private function resolvePath(string $publicDirectory, string $relativePath): string
    {
        $relativePath = trim($relativePath);

        if ('' === $relativePath) {
            throw new \RuntimeException('Le chemin d’image est vide.');
        }

        if (1 === preg_match('#^[a-z][a-z0-9+.-]*://#i', $relativePath)) {
            throw new \RuntimeException('Les images distantes ne sont pas prises en charge : '.$relativePath);
        }

        $relativePath = ltrim($relativePath, '/');

        if (str_contains($relativePath, '..')) {
            throw new \RuntimeException('Chemin d’image invalide : '.$relativePath);
        }

        return $publicDirectory.'/'.$relativePath;
    }
}
