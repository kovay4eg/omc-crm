<?php

namespace Tests\Feature;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use ReflectionMethod;
use Tests\TestCase;

class EventCreateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_create_action_uses_livewire_instead_of_html_form_submit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $component = Livewire::test(CreateEvent::class)->instance();
        $method = new ReflectionMethod(CreateEvent::class, 'getCreateFormAction');
        $action = $method->invoke($component);

        $this->assertFalse($action->canSubmitForm());
        $this->assertTrue($action->hasAction());
        $this->assertSame('create', $action->getLivewireClickHandler());
        $this->assertTrue($action->isLivewireClickHandlerEnabled());
    }

    public function test_event_is_created_once_and_redirects_to_event_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(CreateEvent::class)
            ->fillForm([
                'title' => 'Тестовий анонс',
                'description' => 'Опис тестового заходу',
                'event_date' => now()->addWeek()->startOfMinute(),
                'has_registration_button' => false,
                'status' => 'published',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(EventResource::getUrl('index'));

        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseHas('events', [
            'title' => 'Тестовий анонс',
            'status' => 'published',
            'user_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('event_histories', [
            'action' => 'created',
            'user_id' => $admin->id,
        ]);
    }
}
