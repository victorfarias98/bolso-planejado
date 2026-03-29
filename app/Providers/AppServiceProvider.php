<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\FinancialAccountRepositoryInterface;
use App\Repositories\Contracts\InvestmentRepositoryInterface;
use App\Repositories\Contracts\RecurrenceSeriesRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\DebtRepository;
use App\Repositories\FinancialAccountRepository;
use App\Repositories\InvestmentRepository;
use App\Repositories\RecurrenceSeriesRepository;
use App\Repositories\TransactionRepository;
use App\Services\Billing\FakePaymentGateway;
use App\Services\EntitlementService;
use Filament\Events\ServingFilament;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function ($app): PaymentGateway {
            return new FakePaymentGateway($app->make(EntitlementService::class));
        });

        $this->app->bind(FinancialAccountRepositoryInterface::class, FinancialAccountRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(RecurrenceSeriesRepositoryInterface::class, RecurrenceSeriesRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(DebtRepositoryInterface::class, DebtRepository::class);
        $this->app->bind(InvestmentRepositoryInterface::class, InvestmentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ServingFilament::class, function (): void {
            app()->setLocale('pt_BR');
        });
    }
}
