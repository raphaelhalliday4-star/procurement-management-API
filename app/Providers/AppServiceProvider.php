<?php

namespace App\Providers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\payment as Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\Rfq_vendor;
use App\Models\Service;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Vendor_document;
use App\Policies\GoodsReceiptItemPolicy;
use App\Policies\GoodsReceiptPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\InvoiceItemPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\PurchaseOrderItemPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\PurchaseRequestPolicy;
use App\Policies\QuotationItemPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\RfqPolicy;
use App\Policies\RfqVendorPolicy;
use App\Policies\ServicePolicy;
use App\Policies\UserPolicy;
use App\Policies\VendorDocumentPolicy;
use App\Policies\VendorPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;

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
        Gate::define('permissions.manage', fn (User $user): bool => $user->hasRole('super-admin') || $user->hasPermissionTo('permissions.manage'));
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
        Gate::policy(Vendor_document::class, VendorDocumentPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(PurchaseRequest::class, PurchaseRequestPolicy::class);
        Gate::policy(Rfq::class, RfqPolicy::class);
        Gate::policy(Rfq_vendor::class, RfqVendorPolicy::class);
        Gate::policy(Quotation::class, QuotationPolicy::class);
        Gate::policy(QuotationItem::class, QuotationItemPolicy::class);
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(PurchaseOrderItem::class, PurchaseOrderItemPolicy::class);
        Gate::policy(GoodsReceipt::class, GoodsReceiptPolicy::class);
        Gate::policy(GoodsReceiptItem::class, GoodsReceiptItemPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(InvoiceItem::class, InvoiceItemPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
    }
}
