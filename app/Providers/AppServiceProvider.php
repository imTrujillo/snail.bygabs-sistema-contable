<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\TaxDocument;
use App\Models\User;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use App\Observers\AppointmentObserver;
use App\Observers\CustomerObserver;
use App\Observers\ExpenseObserver;
use App\Observers\JournalEntryObserver;
use App\Observers\ProductObserver;
use App\Observers\PurchaseObserver;
use App\Observers\SaleObserver;
use App\Observers\ServiceObserver;
use App\Observers\SupplierObserver;
use App\Observers\TaxDocumentObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        Sale::observe(SaleObserver::class);
        Purchase::observe(PurchaseObserver::class);
        Expense::observe(ExpenseObserver::class);
        User::observe(UserObserver::class);
        Customer::observe(CustomerObserver::class);
        Service::observe(ServiceObserver::class);
        Appointment::observe(AppointmentObserver::class);
        Product::observe(ProductObserver::class);
        Supplier::observe(SupplierObserver::class);
        TaxDocument::observe(TaxDocumentObserver::class);
        JournalEntry::observe(JournalEntryObserver::class);

        Field::configureUsing(function (Field $component) {
            $component->translateLabel();
        });

        Column::configureUsing(function (Column $component) {
            $component->translateLabel();
        });

        BaseFilter::configureUsing(function (BaseFilter $component) {
            $component->translateLabel();
        });
    }
}
