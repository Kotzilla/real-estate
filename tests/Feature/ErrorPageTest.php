<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_unknown_url_shows_our_404_page(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee(__('error.page.404.title'))
            ->assertSee(__('error.home'));
    }

    public function test_wrong_method_shows_our_405_page(): void
    {
        $this->post('/backend/dashboard')
            ->assertStatus(405)
            ->assertSee(__('error.page.405.title'));
    }

    public function test_404_page_uses_the_visitors_language(): void
    {
        // The fallback route runs the web middleware, so the language chosen with /lang/th applies to the 404 too
        $this->get('/lang/th');

        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee('lang="th"', false)
            ->assertSee('ไม่พบหน้านี้')
            ->assertSee('หน้าแรก');
    }

    public function test_a_code_without_its_own_text_gets_the_generic_one(): void
    {
        app('router')->get('/teapot-test', fn () => abort(418));

        $this->get('/teapot-test')->assertStatus(418)->assertSee(__('error.page.generic.title'));
    }
}
