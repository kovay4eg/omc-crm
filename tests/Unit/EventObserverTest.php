<?php

namespace Tests\Unit;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use App\Observers\EventObserver;
use App\Services\GoogleCalendarService;
use Mockery\MockInterface;
use Tests\TestCase;

class EventObserverTest extends TestCase
{
    public function test_cancelled_event_is_deleted_from_google_calendar(): void
    {
        $user = new User(['name' => 'Admin']);
        $event = new Event(['title' => 'Cancelled', 'status' => EventStatus::Cancelled]);
        $event->setRelation('user', $user);

        $google = $this->mock(GoogleCalendarService::class, function (MockInterface $mock) use ($event, $user): void {
            $mock->shouldReceive('deleteEvent')->once()->with($user, $event)->andReturnTrue();
            $mock->shouldNotReceive('updateEvent');
        });

        (new EventObserver)->updated($event);

        $this->assertSame($google, app(GoogleCalendarService::class));
    }

    public function test_active_event_is_updated_in_google_calendar(): void
    {
        $user = new User(['name' => 'Admin']);
        $event = new Event(['title' => 'Published', 'status' => EventStatus::Published]);
        $event->setRelation('user', $user);

        $this->mock(GoogleCalendarService::class, function (MockInterface $mock) use ($event, $user): void {
            $mock->shouldReceive('updateEvent')->once()->with($user, $event)->andReturnTrue();
            $mock->shouldNotReceive('deleteEvent');
        });

        (new EventObserver)->updated($event);

        $this->assertTrue(true);
    }
}
