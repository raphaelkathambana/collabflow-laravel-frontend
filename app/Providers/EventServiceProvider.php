<?php

namespace App\Providers;

use App\Events\ProjectStarted;
use App\Events\TaskCompleted;
use App\Listeners\CheckForReadyTasks;
use App\Listeners\TriggerOrchestration;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ProjectStarted::class => [
            TriggerOrchestration::class,
        ],
        TaskCompleted::class => [
            CheckForReadyTasks::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
