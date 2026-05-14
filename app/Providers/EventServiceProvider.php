<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Observers\DepartmentObserver;
use App\Observers\EmployeeObserver;
use App\Observers\LeaveRequestObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'eloquent.created: App\Models\Activity' => [
            'App\Listeners\BroadcastActivity',
        ],
    ];

    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        Employee::class => [EmployeeObserver::class],
        Department::class => [DepartmentObserver::class],
        LeaveRequest::class => [LeaveRequestObserver::class],
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