<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class PageAccessTest extends TestCase
{
    public function test_spa_shell_is_served_for_client_routes(): void
    {
        $routes = [
            '/',
            '/kursus',
            '/langganan',
            '/masuk',
            '/daftar',
            '/dashboard',
            '/profile',
            '/kebijakan-privasi',
            '/syarat-layanan',
            '/halaman-tidak-ada',
            '/kursus/laravel-vue-3-full-stack',
            '/belajar/laravel-vue-3-full-stack/2-1',
            '/sertifikat',
            '/sertifikat/ilr-cert-2026-001',
        ];

        foreach ($routes as $uri) {
            $this->get($uri)
                ->assertOk()
                ->assertSee('id="app"', false);
        }
    }

    public function test_health_endpoint_is_not_captured_by_spa_route(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_missing_build_asset_returns_not_found_instead_of_spa_shell(): void
    {
        $this->get('/build/assets/does-not-exist.js')
            ->assertNotFound()
            ->assertDontSee('id="app"', false);
    }
}
