<?php

declare(strict_types=1);

namespace ShoesShop;

final class AdminAuth
{
    private const SESSION_KEY = 'admin_authenticated';
    private const CSRF_KEY = 'admin_csrf';
    private const FLASH_KEY = 'admin_flash';

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private readonly array $config)
    {
        if (\PHP_SESSION_NONE === session_status()) {
            $isHttps = (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'])
                || 'https' === ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');

            session_name('SHOES_SHOP_ADMIN');
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'cookie_secure' => $isHttps,
                'use_strict_mode' => true,
            ]);
        }
    }

    public function check(): bool
    {
        return true === ($_SESSION[self::SESSION_KEY] ?? false);
    }

    public function attempt(string $username, string $password): bool
    {
        $expectedUsername = trim((string) ($this->config['admin_user'] ?? ''));
        $expectedPassword = (string) ($this->config['admin_password'] ?? '');

        if ('' === $expectedUsername) {
            $expectedUsername = 'admin';
        }

        if ('' === $expectedPassword) {
            $expectedPassword = 'admin';
        }

        if (!hash_equals($expectedUsername, $username) || !hash_equals($expectedPassword, $password)) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION[self::SESSION_KEY] = true;
        $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));

        return true;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parameters['path'],
                'domain' => $parameters['domain'],
                'secure' => $parameters['secure'],
                'httponly' => $parameters['httponly'],
                'samesite' => $parameters['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();
    }

    public function requireLogin(): void
    {
        if ($this->check()) {
            return;
        }

        header('Location: login.php');

        exit;
    }

    public function csrfToken(): string
    {
        if (!isset($_SESSION[self::CSRF_KEY]) || !is_string($_SESSION[self::CSRF_KEY])) {
            $_SESSION[self::CSRF_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CSRF_KEY];
    }

    public function verifyCsrf(mixed $token): bool
    {
        return is_string($token) && '' !== $token && hash_equals($this->csrfToken(), $token);
    }

    public function flash(string $message, string $type = 'success'): void
    {
        $_SESSION[self::FLASH_KEY] = [
            'message' => $message,
            'type' => $type,
        ];
    }

    /**
     * @return null|array{message: string, type: string}
     */
    public function pullFlash(): ?array
    {
        $flash = $_SESSION[self::FLASH_KEY] ?? null;
        unset($_SESSION[self::FLASH_KEY]);

        if (!is_array($flash) || !isset($flash['message'], $flash['type'])) {
            return null;
        }

        return [
            'message' => (string) $flash['message'],
            'type' => (string) $flash['type'],
        ];
    }
}
