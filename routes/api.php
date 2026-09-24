<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\GoodsReceiptItemController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderItemController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\PurchaseRequestItemController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\QuotationItemController;
use App\Http\Controllers\RfqController;
use App\Http\Controllers\RfqVendorController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorDocumentController;

Route::prefix('v1')->group( function(){
  Route::post('register', [AuthController::class, 'register']);
  Route::post('login', [AuthController::class, 'logIn']);

  Route::middleware('auth:api')->group(function () {
    Route::get('get-user', [AuthController::class, 'getUser']);
    Route::get('users', [AuthController::class, 'getAllUser']);
    Route::put('user', [AuthController::class, 'updateUser']);
    Route::delete('user', [AuthController::class, 'destroy']);
    Route::post('logout', [AuthController::class, 'logOut']);
  });

});

// vendor route
Route::prefix('v1')->group(function () {

    Route::middleware('auth:api')->group(function () {
       Route::get('vendor', [VendorController::class, 'index']);
        Route::post('vendor', [VendorController::class, 'store']);
        Route::get('vendor/{id}', [VendorController::class, 'show']);
        Route::put('vendor', [VendorController::class, 'update']);
        Route::delete('vendor', [VendorController::class, 'destroy']);
    });

});

// vendor document upload route
Route::prefix('v1')->group(function() {
    Route::middleware('auth:api')->group( function() {
      Route::get('/get-documents', [VendorDocumentController::class, 'index']);
      Route::post('/vendors/documents', [VendorDocumentController::class, 'store']);
      Route::get('/vendors/documents', [VendorDocumentController::class, 'show']);
      Route::put('/vendors/documents/{id}', [VendorDocumentController::class, 'update']);
      Route::delete('/vendors/documents/{id}', [VendorDocumentController::class, 'destroy']);
    });
});

// category route
Route::prefix('v1')->group(function() {
        Route::get('/category', [CategoryController::class, 'index']);
     Route::middleware('auth:api')->group( function() {
        Route::post('/category', [CategoryController::class, 'store']);
        Route::put('/category/{id}', [CategoryController::class, 'update']);
        Route::delete('/category/{id}', [CategoryController::class, 'destroy']);
     });
});

// product and service route
Route::prefix('v1')->group(function() {
   Route::get('/service', [ServiceController::class, 'index']);
     Route::middleware('auth:api')->group( function() {
   Route::post('/service', [ServiceController::class, 'store']);
   Route::put('/service/{id}', [ServiceController::class, 'update']);
   Route::delete('/service/{id}', [ServiceController::class, 'destroy']);
     });
});

// purchase request routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
    Route::get('purchase-requests', [PurchaseRequestController::class, 'index']);
    Route::get('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'show']);
    Route::post('purchase-requests', [PurchaseRequestController::class, 'store']);
    Route::put('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'update']);
    Route::patch('purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve']);
    Route::patch('purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject']);
    Route::delete('purchase-requests/{purchaseRequest}', [PurchaseRequestController::class, 'destroy']);
});

// purchase request item routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
    Route::post('purchase-request-items', [PurchaseRequestItemController::class, 'store']);
    Route::put('purchase-request-items/{purchaseRequestItem}', [PurchaseRequestItemController::class, 'update']);

    // Backward-compatible aliases
    Route::post('purchase-items', [PurchaseRequestItemController::class, 'store']);
    Route::put('purchase-items/{purchaseRequestItem}', [PurchaseRequestItemController::class, 'update']);
});

// quotation routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::get('quotations', [QuotationController::class, 'index']);
    Route::post('quotations', [QuotationController::class, 'store']);
    Route::patch('quotations/{quotation}', [QuotationController::class, 'submit']);
    Route::patch('quotations/{quotation}/evaluate', [QuotationController::class, 'evaluate']);
    Route::patch('quotations/{quotation}/accept', [QuotationController::class, 'accept']);
    Route::patch('quotations/{quotation}/reject', [QuotationController::class, 'reject']);
    Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy']);
});

// quotation item routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
    Route::post('quotation-items', [QuotationItemController::class, 'store']);
    Route::put('quotation-items/{quotationItem}', [QuotationItemController::class, 'update']);
});

// RFQ routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::get('rfqs', [RfqController::class, 'index']);
  Route::post('rfqs', [RfqController::class, 'store']);
  Route::patch('rfqs/{rfq}', [RfqController::class, 'publish']);
  Route::patch('rfqs/{rfq}/close', [RfqController::class, 'close']);
});

// RFQ-Vendors routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::post('rfq-vendors', [RfqVendorController::class, 'store']);
  Route::delete('rfq-vendors/{rfqVendor}', [RfqVendorController::class, 'destroy']);
});

// purchase order routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::post('purchase-orders', [PurchaseOrderController::class, 'store']);
  Route::patch('purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submitForApproval']);
  Route::patch('purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve']);
  Route::patch('purchase-orders/{purchaseOrder}/reject', [PurchaseOrderController::class, 'reject']);
  Route::patch('purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send']);
  Route::patch('purchase-orders/{purchaseOrder}/receiving-status', [PurchaseOrderController::class, 'receivingStatus']);
});

// purchase order item routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::post('purchase-order-items', [PurchaseOrderItemController::class, 'store']);
});

// goods receipt routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::post('goods-receipts', [GoodsReceiptController::class, 'store']);
  Route::post('goods-receipt-items', [GoodsReceiptItemController::class, 'store']);
});

// invoice routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::post('invoices', [InvoiceController::class, 'store']);
  Route::patch('invoices/{invoice}/submit', [InvoiceController::class, 'submit']);
  Route::patch('invoices/{invoice}/approve', [InvoiceController::class, 'approve']);
  Route::patch('invoices/{invoice}/reject', [InvoiceController::class, 'reject']);
});

// invoice item routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::post('invoice-items', [InvoiceItemController::class, 'store']);
});

// goods receipt item routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::post('goods-receipt-items', [GoodsReceiptItemController::class, 'store']);
});

// permission routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::post('permissions/roles', [PermissionController::class, 'assignRole']);
  Route::post('permissions', [PermissionController::class, 'assignPermission']);
});

// payment routes
Route::prefix('v1')->middleware('auth:api')->group(function () {
  Route::post('payments', [PaymentController::class, 'store']);
});
