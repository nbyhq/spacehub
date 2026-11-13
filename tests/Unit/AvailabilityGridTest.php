<?php

namespace Tests\Unit;

use App\Support\AvailabilityGrid;
use PHPUnit\Framework\TestCase;

class AvailabilityGridTest extends TestCase
{
    private function states(array $slots): array
    {
        return array_column($slots, 'state', 'start');
    }

    public function test_converts_time_strings_and_minutes(): void
    {
        $this->assertSame(8 * 60, AvailabilityGrid::toMinutes('08:00'));
        $this->assertSame(17 * 60 + 30, AvailabilityGrid::toMinutes('17:30:00'));
        $this->assertSame('09:05', AvailabilityGrid::format(9 * 60 + 5));
    }

    public function test_builds_one_slot_per_hour_between_open_and_close(): void
    {
        $slots = AvailabilityGrid::build(8 * 60, 12 * 60, []);

        $this->assertSame(['08:00', '09:00', '10:00', '11:00'], array_column($slots, 'start'));
        $this->assertSame(['free'], array_values(array_unique(array_column($slots, 'state'))));
        $this->assertSame('12:00', $slots[3]['end']);
    }

    public function test_last_slot_is_shorter_when_closing_is_not_on_the_hour(): void
    {
        $slots = AvailabilityGrid::build(9 * 60, 11 * 60 + 30, []);

        $this->assertCount(3, $slots);
        $this->assertSame('11:00', $slots[2]['start']);
        $this->assertSame('11:30', $slots[2]['end']);
    }

    public function test_booking_marks_slots_as_taken(): void
    {
        $slots = AvailabilityGrid::build(8 * 60, 13 * 60, [[10 * 60, 12 * 60, 'approved']]);

        $this->assertSame([
            '08:00' => 'free', '09:00' => 'free', '10:00' => 'taken', '11:00' => 'taken', '12:00' => 'free',
        ], $this->states($slots));
        $this->assertFalse($slots[2]['pending']);
        $this->assertSame('10:00–12:00', $slots[2]['ranges']);
    }

    public function test_partial_booking_marks_slot_as_partial(): void
    {
        $slots = AvailabilityGrid::build(9 * 60, 12 * 60, [[10 * 60, 10 * 60 + 30, 'approved']]);

        $this->assertSame(['09:00' => 'free', '10:00' => 'partial', '11:00' => 'free'], $this->states($slots));
    }

    public function test_back_to_back_bookings_do_not_bleed_into_neighbour_slots(): void
    {
        $slots = AvailabilityGrid::build(9 * 60, 13 * 60, [
            [10 * 60, 11 * 60, 'approved'],
            [11 * 60, 12 * 60, 'pending'],
        ]);

        $this->assertSame([
            '09:00' => 'free', '10:00' => 'taken', '11:00' => 'taken', '12:00' => 'free',
        ], $this->states($slots));
        $this->assertFalse($slots[1]['pending']);
        $this->assertTrue($slots[2]['pending']);
    }

    public function test_slots_that_already_started_are_past(): void
    {
        $slots = AvailabilityGrid::build(8 * 60, 12 * 60, [], 9 * 60 + 15);

        $this->assertSame([
            '08:00' => 'past', '09:00' => 'past', '10:00' => 'free', '11:00' => 'free',
        ], $this->states($slots));
    }

    public function test_slot_starting_exactly_now_is_past(): void
    {
        $slots = AvailabilityGrid::build(9 * 60, 11 * 60, [], 10 * 60);

        $this->assertSame(['09:00' => 'past', '10:00' => 'past'], $this->states($slots));
    }

    public function test_no_slots_when_closed(): void
    {
        $this->assertSame([], AvailabilityGrid::build(10 * 60, 10 * 60, []));
    }
}
