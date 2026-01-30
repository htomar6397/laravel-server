<?php

namespace App\Providers;

use App\Models\{DataEntry, Expenditure, PhotoCapture, Project};
use App\Policies\{DataEntryPolicy, ExpenditurePolicy, PhotoCapturePolicy, ProjectPolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Expenditure::class => ExpenditurePolicy::class,
        DataEntry::class => DataEntryPolicy::class,
        PhotoCapture::class => PhotoCapturePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
