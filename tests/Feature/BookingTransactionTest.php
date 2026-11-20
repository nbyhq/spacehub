<?php

namespace Tests\Feature;

use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingTransactionTest extends TestCase
{
    use RefreshDatabase;

    private function space(): Space
    {
        return Space::create([
            'name' => 'Ruang Transaksi',
            'type' => 'meeting_room',
            'capacity' => 8,
            'location' => 'Lantai 2',
            'price_per_hour' => 100000,
            'is_active' => true,
        ]);
    }

    private function payload(string $start, string $end): array
    {
        return [
            'booking_date' => now()->addDay()->toDateString(),
            'start_time' => $start,
            'end_time' => $end,
        ];
    }

    public function test_space_row_is_locked_first_and_check_and_insert_share_one_transaction(): void
    {
        $space = $this->space();
        $user = User::factory()->create();
        $base = DB::transactionLevel();

        $log = [];
        DB::listen(function ($query) use (&$log) {
            $log[] = ['sql' => strtolower($query->sql), 'level' => DB::transactionLevel()];
        });

        $this->actingAs($user)
            ->post(route('bookings.store', $space), $this->payload('10:00', '11:00'))
            ->assertRedirect(route('bookings.index'));

        $inside = collect($log)->filter(fn ($q) => $q['level'] > $base)->values();

        // Pembacaan pertama di dalam transaksi harus berupa kunci baris ruang
        $this->assertStringContainsString('from "spaces"', $inside->first()['sql']);
        // Cek bentrok dan penyimpanan terjadi di dalam transaksi yang sama
        $this->assertTrue($inside->contains(fn ($q) => str_contains($q['sql'], 'from "bookings"') && str_contains($q['sql'], 'exists')));
        $this->assertTrue($inside->contains(fn ($q) => str_contains($q['sql'], 'insert into "bookings"')));
        // Tidak ada query ke tabel bookings di luar transaksi
        $this->assertFalse(collect($log)->filter(fn ($q) => $q['level'] <= $base)->contains(fn ($q) => str_contains($q['sql'], '"bookings"')));
        $this->assertSame($base, DB::transactionLevel());
    }

    public function test_rejected_booking_saves_nothing_and_closes_the_transaction(): void
    {
        $space = $this->space();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $space), $this->payload('10:00', '12:00'))
            ->assertRedirect(route('bookings.index'));

        $base = DB::transactionLevel();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store', $space), $this->payload('11:00', '13:00'))
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('bookings', 1);
        $this->assertSame($base, DB::transactionLevel());
    }
}
