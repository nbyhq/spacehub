<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BookingReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Rabu, 18 Nov 2026, pukul 15:00
        $this->travelTo(Carbon::parse('2026-11-18 15:00:00'));
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function space(string $name = 'Ruang Laporan', int $price = 100000): Space
    {
        return Space::create([
            'name' => $name,
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => $price,
            'is_active' => true,
        ]);
    }

    private function book(Space $space, string $userName, string $date, string $start = '10:00:00', string $end = '11:00:00', string $status = 'approved'): Booking
    {
        return Booking::create([
            'user_id' => User::factory()->create(['name' => $userName])->id,
            'space_id' => $space->id,
            'booking_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $status,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.reports.bookings'))->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_open_the_report(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.reports.bookings'))
            ->assertForbidden();
    }

    public function test_admin_can_open_the_report_with_print_button(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings'))
            ->assertOk()
            ->assertSee('Laporan Booking')
            ->assertSee('window.print()', false)
            ->assertSee('Bulan ini');
    }

    public function test_default_period_is_the_current_month(): void
    {
        $space = $this->space();
        $this->book($space, 'Pemesan November', '2026-11-25');
        $this->book($space, 'Pemesan Oktober', '2026-10-20');
        $this->book($space, 'Pemesan Desember', '2026-12-05');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings'))
            ->assertSee('Pemesan November')
            ->assertDontSee('Pemesan Oktober')
            ->assertDontSee('Pemesan Desember');
    }

    public function test_last_month_preset(): void
    {
        $space = $this->space();
        $this->book($space, 'Pemesan November', '2026-11-25');
        $this->book($space, 'Pemesan Oktober', '2026-10-20');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings', ['preset' => 'bulan-lalu']))
            ->assertSee('Pemesan Oktober')
            ->assertDontSee('Pemesan November');
    }

    public function test_all_dates_preset_includes_every_booking(): void
    {
        $space = $this->space();
        $this->book($space, 'Pemesan November', '2026-11-25');
        $this->book($space, 'Pemesan Oktober', '2026-10-20');
        $this->book($space, 'Pemesan Desember', '2026-12-05');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings', ['preset' => 'semua']))
            ->assertSee('Pemesan November')
            ->assertSee('Pemesan Oktober')
            ->assertSee('Pemesan Desember');
    }

    public function test_custom_range_is_inclusive_on_both_ends(): void
    {
        $space = $this->space();
        $this->book($space, 'Pemesan Kemarin', '2026-11-24');
        $this->book($space, 'Pemesan Tepat', '2026-11-25');
        $this->book($space, 'Pemesan Besok', '2026-11-26');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings', ['from' => '2026-11-25', 'to' => '2026-11-25']))
            ->assertSee('Pemesan Tepat')
            ->assertDontSee('Pemesan Kemarin')
            ->assertDontSee('Pemesan Besok');
    }

    public function test_filter_by_status(): void
    {
        $space = $this->space();
        $this->book($space, 'Pemesan Setuju', '2026-11-25', '10:00:00', '11:00:00', 'approved');
        $this->book($space, 'Pemesan Tunggu', '2026-11-26', '10:00:00', '11:00:00', 'pending');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings', ['status' => 'pending']))
            ->assertSee('Pemesan Tunggu')
            ->assertDontSee('Pemesan Setuju');
    }

    public function test_unknown_status_is_ignored(): void
    {
        $space = $this->space();
        $this->book($space, 'Pemesan Setuju', '2026-11-25', '10:00:00', '11:00:00', 'approved');
        $this->book($space, 'Pemesan Tunggu', '2026-11-26', '10:00:00', '11:00:00', 'pending');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings', ['status' => 'bukan-status']))
            ->assertOk()
            ->assertSee('Pemesan Setuju')
            ->assertSee('Pemesan Tunggu');
    }

    public function test_filter_by_space(): void
    {
        $orion = $this->space('Ruang Orion');
        $nebula = $this->space('Aula Nebula');
        $this->book($orion, 'Pemesan Orion', '2026-11-25');
        $this->book($nebula, 'Pemesan Nebula', '2026-11-26');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings', ['space' => $orion->id]))
            ->assertSee('Pemesan Orion')
            ->assertDontSee('Pemesan Nebula');
    }

    public function test_summary_counts_and_revenue_only_include_approved_bookings(): void
    {
        $space = $this->space(price: 100000);
        $this->book($space, 'A', '2026-11-20', '10:00:00', '12:00:00', 'approved');   // 2 jam = 200.000
        $this->book($space, 'B', '2026-11-21', '14:00:00', '15:00:00', 'approved');   // 1 jam = 100.000
        $this->book($space, 'C', '2026-11-22', '09:00:00', '10:00:00', 'pending');    // tidak dihitung
        $this->book($space, 'D', '2026-11-23', '09:00:00', '10:00:00', 'cancelled');  // tidak dihitung

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings'))
            ->assertSee('data-summary="total">4</div>', false)
            ->assertSee('data-summary="approved">2</div>', false)
            ->assertSee('data-summary="pending">1</div>', false)
            ->assertSee('data-summary="cancelled">1</div>', false)
            ->assertSee('data-summary="revenue">Rp 300.000</div>', false);
    }

    public function test_report_lists_code_name_email_space_and_day(): void
    {
        $space = $this->space('Ruang Detail');
        $booking = $this->book($space, 'Budi Detail', '2026-11-25');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings'))
            ->assertSee($booking->code)
            ->assertSee('Budi Detail')
            ->assertSee($booking->user->email)
            ->assertSee('Ruang Detail')
            ->assertSee('Rabu, 25 Nov 2026')
            ->assertSee('10:00–11:00');
    }

    public function test_invalid_dates_fall_back_to_the_current_month_with_a_notice(): void
    {
        $space = $this->space();
        $this->book($space, 'Pemesan November', '2026-11-25');
        $this->book($space, 'Pemesan Oktober', '2026-10-20');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings', ['from' => '2026-11-30', 'to' => '2026-11-01']))
            ->assertOk()
            ->assertSee('tidak valid')
            ->assertSee('Pemesan November')
            ->assertDontSee('Pemesan Oktober');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings', ['from' => 'bukan-tanggal']))
            ->assertOk()
            ->assertSee('tidak valid');
    }

    public function test_array_parameters_do_not_break_the_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings').'?from[]=x&to[]=y&status[]=a&space[]=1&preset[]=zz')
            ->assertOk();
    }

    public function test_per_space_recap_is_shown_only_for_multiple_spaces(): void
    {
        $orion = $this->space('Ruang Orion');
        $nebula = $this->space('Aula Nebula');
        $this->book($orion, 'Pemesan Orion', '2026-11-25');
        $this->book($nebula, 'Pemesan Nebula', '2026-11-26');

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.reports.bookings'))
            ->assertSee('Rekap per Ruang');

        $this->actingAs($admin)
            ->get(route('admin.reports.bookings', ['space' => $orion->id]))
            ->assertDontSee('Rekap per Ruang');
    }

    public function test_deleted_space_still_appears_in_the_report(): void
    {
        $space = $this->space('Ruang Lama');
        $this->book($space, 'Pemesan Lama', '2026-11-02');   // sudah selesai, jadi ruang boleh dihapus
        $space->delete();

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings'))
            ->assertOk()
            ->assertSee('Ruang Lama')
            ->assertSee('Pemesan Lama');
    }

    public function test_empty_report_shows_a_message(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings'))
            ->assertOk()
            ->assertSee('Tidak ada booking pada filter ini.')
            ->assertSee('data-summary="total">0</div>', false);
    }

    public function test_sidebar_and_bookings_page_link_to_the_report(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertSee(route('admin.reports.bookings'));

        $this->actingAs($admin)
            ->get(route('admin.bookings.index', ['status' => 'pending']))
            ->assertSee(route('admin.reports.bookings', ['status' => 'pending']));
    }

    public function test_filter_toolbar_has_pickers_presets_and_reset(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings'))
            ->assertOk()
            ->assertSee('Filter Laporan')
            ->assertSee('id="from"', false)
            ->assertSee('id="to"', false)
            ->assertSee('Terapkan')
            ->assertSee('Reset filter')
            ->assertSee('flatpickr', false)
            ->assertSee('Bulan lalu')
            ->assertSee('7 hari terakhir');
    }

    public function test_filter_toolbar_keeps_the_chosen_values(): void
    {
        $space = $this->space('Ruang Terpilih');

        $this->actingAs($this->admin())
            ->get(route('admin.reports.bookings', [
                'from' => '2026-11-05',
                'to' => '2026-11-20',
                'status' => 'pending',
                'space' => $space->id,
            ]))
            ->assertOk()
            ->assertSee('value="2026-11-05"', false)
            ->assertSee('value="2026-11-20"', false)
            ->assertSee('<option value="pending" selected', false)
            ->assertSee('<option value="'.$space->id.'" selected', false);
    }
}
