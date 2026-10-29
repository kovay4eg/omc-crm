<?php

namespace Tests\Feature;

use Tests\TestCase;

class OmcCrmSupportPageTest extends TestCase
{
    public function test_support_page_is_public_and_contains_required_information(): void
    {
        $this->get('/support/omc-crm')
            ->assertOk()
            ->assertSee('Підтримка ОМЦ CRM')
            ->assertSee('внутрішнього використання працівниками')
            ->assertSee('post@omc.pl.ua')
            ->assertSee(route('privacy-policy'));
    }
}
