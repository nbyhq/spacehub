<?php

namespace Tests\Feature;

use App\Models\Space;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    private function makeSpace(string $name, bool $active): Space
    {
        return Space::create([
            'name' => $name,
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => 75000,
            'is_active' => $active,
        ]);
    }

    public function test_landing_page_loads_without_spaces(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_landing_page_shows_active_spaces_only(): void
    {
        $this->makeSpace('Ruang Aktif Tes', true);
        $this->makeSpace('Ruang Nonaktif Tes', false);

        $this->get('/')
            ->assertOk()
            ->assertSee('Ruang Aktif Tes')
            ->assertDontSee('Ruang Nonaktif Tes');
    }
}
