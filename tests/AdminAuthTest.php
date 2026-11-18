<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use ShoesShop\AdminAuth;

/**
 * @internal
 *
 * @coversNothing
 */
final class AdminAuthTest extends TestCase
{
    private AdminAuth $auth;

    #[\Override]
    protected function setUp(): void
    {
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            session_start();
        }

        $_SESSION = [];

        $this->auth = new AdminAuth([
            'admin_user' => 'admin',
            'admin_password' => 'secret',
        ]);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testAttemptAcceptsConfiguredCredentials(): void
    {
        $previousToken = $this->auth->csrfToken();

        $this->assertTrue($this->auth->attempt('admin', 'secret'));
        $this->assertTrue($this->auth->check());
        $this->assertNotSame($previousToken, $this->auth->csrfToken());
    }

    public function testAttemptFallsBackToDefaultCredentials(): void
    {
        $auth = new AdminAuth([]);

        $this->assertTrue($auth->attempt('admin', 'admin'));
        $this->assertTrue($auth->check());
    }

    public function testAttemptRejectsWrongPassword(): void
    {
        $this->assertFalse($this->auth->attempt('admin', 'wrong'));
        $this->assertFalse($this->auth->check());
    }

    public function testAttemptRejectsWrongUser(): void
    {
        $this->assertFalse($this->auth->attempt('other', 'secret'));
        $this->assertFalse($this->auth->check());
    }

    public function testCsrfTokenIsStableAndVerifiable(): void
    {
        $token = $this->auth->csrfToken();

        $this->assertSame($token, $this->auth->csrfToken());
        $this->assertTrue($this->auth->verifyCsrf($token));
        $this->assertFalse($this->auth->verifyCsrf('invalid'));
        $this->assertFalse($this->auth->verifyCsrf(null));
    }

    public function testFlashCanBePulledOnce(): void
    {
        $this->auth->flash('Produit créé.', 'success');

        $this->assertSame(
            ['message' => 'Produit créé.', 'type' => 'success'],
            $this->auth->pullFlash()
        );
        $this->assertNull($this->auth->pullFlash());
    }

    public function testLogoutClearsAuthentication(): void
    {
        $this->auth->attempt('admin', 'secret');
        $this->auth->logout();

        $this->assertFalse($this->auth->check());
    }
}
