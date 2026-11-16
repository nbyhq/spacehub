<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminSpaceBookingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Jumat 20 Nov 2026, pukul 10:30
        $this->travelTo(Carbon::parse('2026-11-20 10:30:00'));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function space(string $name = 'Ruang Jadwal'): Space
    {
        return Space::create([
            'name' => $name,
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => 100000,
            'is_active' => true,
        ]);
    }

    private function book(Space $space, User $user, string $date, string $start, string $end, string $status = 'approved'): Booking
    {
        return Booking::create([
            'user_id' => $user->id,
            'space_id' => $space->id,
            'booking_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $status,
        ]);
    }

    public function test_list_shows_who_booked_and_on_which_day(): void
    {
        $space = $this->space();
        $budi = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi.santoso@contoh.test']);
        $this->book($space, $budi, '2026-11-21', '10:00:00', '12:00:00', 'approved');

        $this->actingAs($this->admin())
            ->get(route('admin.spaces.index'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('budi.santoso@contoh.test')
            ->assertSee('Sabtu, 21 Nov 2026')
            ->assertSee('10:00–12:00')
            ->assertSee('Besok');
    }

    public function test_bookings_are_grouped_by_day_in_chronological_order(): void
    {
        $space = $this->space();
        $citra = User::factory()->create(['name' => 'Citra Lestari']);
        $dewi = User::factory()->create(['name' => 'Dewi Anggraini']);
        $eko = User::factory()->create(['name' => 'Eko Prasetyo']);

        // Sengaja dibuat tidak berurutan
        $this->book($space, $eko, '2026-11-24', '09:00:00', '10:00:00');
        $this->book($space, $dewi, '2026-11-21', '14:00:00', '15:00:00');
        $this->book($space, $citra, '2026-11-21', '09:00:00', '10:00:00');

        $this->actingAs($this->admin())
            ->get(route('admin.spaces.index'))
            ->assertSeeInOrder([
                'Sabtu, 21 Nov 2026', 'Citra Lestari', 'Dewi Anggraini',
                'Selasa, 24 Nov 2026', 'Eko Prasetyo',
            ]);
    }

    public function test_today_is_labelled_for_bookings_later_today(): void
    {
        $space = $this->space();
        $this->book($space, User::factory()->create(['name' => 'Fajar Nugroho']), '2026-11-20', '15:00:00', '16:00:00');

        $this->actingAs($this->admin())
            ->get(route('admin.spaces.index'))
            ->assertSee('Jumat, 20 Nov 2026')
            ->assertSee('Hari ini');
    }

    public function test_finished_and_cancelled_bookings_are_not_listed(): void
    {
        $space = $this->space();
        $this->book($space, User::factory()->create(['name' => 'Gita Selesai']), '2026-11-19', '10:00:00', '11:00:00');
        $this->book($space, User::factory()->create(['name' => 'Hadi Pagi']), '2026-11-20', '08:00:00', '09:00:00');
        $this->book($space, User::factory()->create(['name' => 'Indra Batal']), '2026-11-25', '10:00:00', '11:00:00', 'cancelled');

        $this->actingAs($this->admin())
            ->get(route('admin.spaces.index'))
            ->assertOk()
            ->assertDontSee('Gita Selesai')
            ->assertDontSee('Hadi Pagi')
            ->assertDontSee('Indra Batal')
            ->assertSee('Belum ada booking mendatang untuk ruang ini.');
    }

    public function test_each_space_lists_only_its_own_bookings(): void
    {
        $meeting = $this->space('Ruang Orion');
        $hall = $this->space('Aula Nebula');
        $this->book($meeting, User::factory()->create(['name' => 'Joko Orion']), '2026-11-21', '10:00:00', '11:00:00');
        $this->book($hall, User::factory()->create(['name' => 'Kiki Nebula']), '2026-11-22', '10:00:00', '11:00:00');

        $html = $this->actingAs($this->admin())
            ->get(route('admin.spaces.index'))
            ->assertOk()
            ->getContent();

        $orion = $this->dialogHtml($html, $meeting);
        $nebula = $this->dialogHtml($html, $hall);

        $this->assertStringContainsString('Joko Orion', $orion);
        $this->assertStringNotContainsString('Kiki Nebula', $orion);
        $this->assertStringContainsString('Kiki Nebula', $nebula);
        $this->assertStringNotContainsString('Joko Orion', $nebula);
    }

    public function test_jadwal_button_shows_booking_count(): void
    {
        $space = $this->space();
        $user = User::factory()->create();
        $this->book($space, $user, '2026-11-21', '10:00:00', '11:00:00');
        $this->book($space, $user, '2026-11-22', '10:00:00', '11:00:00');

        $this->actingAs($this->admin())
            ->get(route('admin.spaces.index'))
            ->assertSee('2 booking aktif');
    }

    public function test_regular_user_cannot_open_the_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.spaces.index'))
            ->assertForbidden();
    }

    /** Potongan HTML dialog milik satu ruang. */
    private function dialogHtml(string $html, Space $space): string
    {
        $start = strpos($html, 'id="space-bookings-'.$space->id.'"');
        $this->assertNotFalse($start, 'Dialog untuk ruang tidak ditemukan');

        $end = strpos($html, '</dialog>', $start);

        return substr($html, $start, $end - $start);
    }
}
