<?php

namespace App\Providers;

use App\Events\InvoicePaid;
use App\Events\OrderCreated;
use App\Events\ProposalCreated;
use App\Listeners\SendPaymentProofEmail;
use App\Listeners\StoreInvoicePaidNotification;
use App\Listeners\StoreOrderCreatedNotification;
use App\Listeners\StoreProposalCreatedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class AppServiceProvider extends EventServiceProvider
{
    /**
     * Event-listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        InvoicePaid::class => [
            SendPaymentProofEmail::class,
            StoreInvoicePaidNotification::class,
        ],
        ProposalCreated::class => [
            StoreProposalCreatedNotification::class,
        ],
        OrderCreated::class => [
            StoreOrderCreatedNotification::class,
        ],
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
