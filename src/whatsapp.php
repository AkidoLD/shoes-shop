<?php

declare(strict_types=1);

function whatsapp_number(array $config): string
{
    $number = preg_replace('/\D+/', '', (string) ($config['whatsapp_number'] ?? ''));

    if ($number === null) {
        return '';
    }

    if (str_starts_with($number, '00')) {
        $number = substr($number, 2);
    }

    return $number;
}

function whatsapp_link(array $config, string $message = ''): string
{
    $url     = 'https://wa.me/' . whatsapp_number($config);
    $message = trim($message);

    if ($message === '') {
        return $url;
    }

    return $url . '?text=' . rawurlencode($message);
}

function whatsapp_product_message(array $product, array $config): string
{
    $product_url = absolute_url(
        'product.php?ref=' . rawurlencode((string) ($product['ref'] ?? '')),
        $config
    );

    $name  = (string) ($product['name'] ?? '');
    $ref   = (string) ($product['ref'] ?? '');
    $price = format_price((int) ($product['price'] ?? 0), $config);

    return 'Bonjour ' . $config['site_name'] . ",\n\n"
        . "Je souhaite commander ce modèle :\n\n"
        . '• Modèle : ' . $name . "\n"
        . '• Référence : ' . $ref . "\n"
        . '• Prix : ' . $price . "\n"
        . '• Lien : ' . $product_url . "\n\n"
        . 'Merci de me confirmer la disponibilité, la taille et la couleur souhaitées.';
}

function whatsapp_product_link(array $product, array $config): string
{
    return whatsapp_link($config, whatsapp_product_message($product, $config));
}

function whatsapp_contact_message(array $config): string
{
    return 'Bonjour ' . $config['site_name'] . ",\n\n"
        . "Je viens de votre catalogue en ligne et je souhaite avoir plus "
        . "d'informations sur vos modèles.\n\n"
        . 'Merci !';
}

function whatsapp_contact_link(array $config): string
{
    return whatsapp_link($config, whatsapp_contact_message($config));
}
