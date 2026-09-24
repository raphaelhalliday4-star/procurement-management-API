<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Models\GoodsReceiptItem;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class InvoiceController extends Controller
{
    #[OA\Post(
        path: "/api/v1/invoices",
        summary: "Create an invoice for a purchase order",
        tags: ["Invoices"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["purchase_order_id", "invoice_date"],
                properties: [
                    new OA\Property(property: "purchase_order_id", type: "integer", example: 1),
                    new OA\Property(property: "invoice_date", type: "string", format: "date", example: "2026-09-21"),
                    new OA\Property(property: "due_date", type: "string", format: "date", nullable: true, example: "2026-10-21"),
                    new OA\Property(property: "tax", type: "number", format: "float", example: 50),
                    new OA\Property(property: "discount", type: "number", format: "float", example: 0),
                    new OA\Property(property: "notes", type: "string", nullable: true, example: "Invoice for delivered goods"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Invoice created successfully"),
            new OA\Response(response: 400, description: "Purchase order is not eligible for invoicing"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 409, description: "An invoice already exists for this purchase order"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(InvoiceRequest $request){
        Gate::authorize('create', Invoice::class);
        $user = Auth::user();
        $purchaseOrder = PurchaseOrder::find($request->purchase_order_id);

        if($purchaseOrder->vendor !==  $user->vendor){
              return response()->json([
                'message'=> 'This purchase order does not belong to you.'
              ]);
        }

        if(!in_array($purchaseOrder, ['sent', 'partially_recieved', 'recieved'])){
            return response()->json([ 
              'message' => 'An invoice can only be created for a sent, partially received, or received purchase order.' 
            ], 400);
        }
  
        if($purchaseOrder->goodsReceipts()->count() === 0){
            return response()->json([ 
                'message' => 'An invoice can only be created after goods have been received.' 
            ], 400);
        }

        $existinInvoice = Invoice::where('purchase_order_id', $purchaseOrder->id)->whereIn('status', ['pending', 'approved', 'paid'])->exists();

        if($existinInvoice){
            return response()->json([ 
                'message' => 'An invoice already exists for this purchase order.' 
                ], 409);
        }

        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad( Invoice::count() + 1, 5, '0', STR_PAD_LEFT );

        $invoice = Invoice::create([
            'purchase_order_id' => $purchaseOrder->id, 
            'vendor_id' => $user->vendor_id, 
            'invoice_number' => $invoiceNumber, 
            'invoice_date' => $request->invoice_date, 
            'due_date' => $request->due_date, 
            'subtotal' => 0, 
            'tax' => $request->tax ?? 0, 
            'discount' => $request->discount ?? 0, 
            'total_amount' => 0, 
            'status' => 'pending', 
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message'=> 'Invoice created successfully',
            'invoice'=> $invoice
        ], 201);

    }

    #[OA\Patch(
        path: "/api/v1/invoices/{invoice}/submit",
        summary: "Submit a pending invoice for approval",
        tags: ["Invoices"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "invoice", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Invoice submitted for approval successfully"),
            new OA\Response(response: 400, description: "Invoice cannot be submitted"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Invoice does not belong to the authenticated vendor"),
            new OA\Response(response: 404, description: "Invoice not found"),
        ]
    )]
    public function submit(Invoice $invoice){
            Gate::authorize('submit', $invoice);
         $user = Auth::user();
         if($invoice->status !== 'pending'){
            return response()->json([
                'message' => 'You can only submit a pending invoice'
            ]);
         }

         $PO = $invoice->vendor_id;
         if($PO !== $user->vendor_id){
            return response()->json([
                'message'=> 'This invoice does not belong to the vendor'
            ]);
         }

        if($invoice->items()->count() === 0){
            return response()->json([
                'message'=> 'An invoice must atleast have oe item before submission'
            ]);
        }

        $invoice->update([
            'status' => 'submitted'
        ]);
         

        return response()->json([
            'message'=> 'Invoice submitted for approval successfully',
            'invoice' => $invoice->fresh()
        ]);
    }

    #[OA\Patch(
        path: "/api/v1/invoices/{invoice}/approve",
        summary: "Approve a submitted invoice",
        tags: ["Invoices"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "invoice", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Invoice approved successfully"),
            new OA\Response(response: 400, description: "Invoice cannot be approved"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Invoice not found"),
        ]
    )]
    public function approve(Invoice $invoice){
            Gate::authorize('approve', $invoice);
      $user = Auth::user();
      if($invoice->status !== 'submitted'){
        return response()->json([
            'message' => 'The invoice must be submitted'
        ]);
      }

    if($invoice->items()->count() === 0){
        return response()->json([
            'message'=> 'Invoice must have atleast one item'
        ]);
    }

    $purchase0rder = $invoice->purchaseOrder;
    if($purchase0rder->vendor_id !== $invoice->vendor_id){
        return response()->json([
            'message'=>'Invoice vendor does not match the purchase order vendor.'
        ]);
    }

    foreach($invoice->items as $items){
        $recievedQuantity = GoodsReceiptItem::where('purchase_order_item_id', $items->purchase_order_item_id)->sum('quantity_recieved');
    }

    if($items > $recievedQuantity){
        return response()->json([
            'message'=> 'Invoice quantity cannot exceed the received quantity.',
            'purchase_order_item_id' => $items->purchase_order_item_id
        ]);
    }

    $invoice->update([
        'status'=> 'approved'
    ]);

    return response()->json([
        'message'=>'This invoice has been approved successfully',
        'invoice' => $invoice->fresh(),
    ]);
     
    }

    #[OA\Patch(
        path: "/api/v1/invoices/{invoice}/reject",
        summary: "Reject a submitted invoice",
        tags: ["Invoices"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "invoice", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["rejection_reason"],
                properties: [
                    new OA\Property(property: "rejection_reason", type: "string", minLength: 5, example: "The invoice quantity does not match the goods received."),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Invoice rejected successfully"),
            new OA\Response(response: 400, description: "Invoice cannot be rejected"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Invoice not found"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function reject(Request $request, Invoice $invoice){
         Gate::authorize('reject', $invoice);
       $user = Auth::user();
      if($invoice->status !== 'submitted'){
        return response()->json([
            'message' => 'The invoice must be submitted'
        ]);
      }

    if($invoice->items()->count() === 0){
        return response()->json([
            'message'=> 'Invoice must have atleast one item'
        ]);
    }

    $purchase0rder = $invoice->purchaseOrder;
    if($purchase0rder->vendor_id !== $invoice->vendor_id){
        return response()->json([
            'message'=>'Invoice vendor does not match the purchase order vendor.'
        ]);
    }

    foreach($invoice->items as $items){
        $recievedQuantity = GoodsReceiptItem::where('purchase_order_item_id', $items->purchase_order_item_id)->sum('quantity_recieved');
    }

    if($items > $recievedQuantity){
        return response()->json([
            'message'=> 'Invoice quantity cannot exceed the received quantity.',
            'purchase_order_item_id' => $items->purchase_order_item_id
        ]);
    }

    $request->validate([
        'rejection_reason'=> 'required|string|min:5',
    ]);

    $invoice->update([
        'status'=> 'reject',
        'rejection_reason'=> $request->rejection_reason
    ]);

    return response()->json([
        'message'=>'This invoice has been rejected',
        'invoice' => $invoice->fresh(),
    ]);
    }
}
