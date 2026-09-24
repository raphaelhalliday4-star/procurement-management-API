<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceItemRequest;
use App\Models\GoodsReceiptItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class InvoiceItemController extends Controller
{
    #[OA\Post(
        path: "/api/v1/invoice-items",
        summary: "Add an item to an invoice",
        tags: ["Invoice Items"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["invoice_id", "purchase_order_item_id", "quantity"],
                properties: [
                    new OA\Property(property: "invoice_id", type: "integer", example: 1),
                    new OA\Property(property: "purchase_order_item_id", type: "integer", example: 1),
                    new OA\Property(property: "quantity", type: "integer", minimum: 1, example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Invoice item added successfully"),
            new OA\Response(response: 400, description: "Invoice item cannot be added"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Invoice does not belong to the authenticated vendor"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(InvoiceItemRequest $request){
            Gate::authorize('create', InvoiceItem::class);
          $user = Auth::user();
          $invoice = Invoice::find($request->invoice_id);

          if($invoice->vendor_id !== $user->vendor){
            return response()->json([ 
                'message' => 'This invoice does not belong to you.' 
            ], 403);
          }

          if($invoice->status !== 'pending'){
            return response()->json([ 
                'message' => 'Items can only be added to pending invoices.' 
                ], 400);
          }

          $purchaseOrderItem = PurchaseOrderItem::find($request->purchase_order_item_id);

          if($purchaseOrderItem->purchaseOrder !== $invoice->purchaseOrder){
            return response()->json([ 
                'message' => 'This purchase order item does not belong to the invoice purchase order.' 
             ], 400);
          }

            $reciecvedQuantity = GoodsReceiptItem::where('purchase_order_item_id', $purchaseOrderItem->id)->sum('quantity_recieved');

            if($reciecvedQuantity === 0){
                return response()->json([
                     'message' => 'this puurchase order item has not been recieved',
                ]);
            }

            $alreadyInvoiced = Invoice::where('purchase_order_item',$purchaseOrderItem->id)->sum('quantity');

            $remainingQuantity = $reciecvedQuantity - $alreadyInvoiced;

            if($alreadyInvoiced <= 0){
                return response()->json([ 
                    'message' => 'The received quantity for this item has already been fully invoiced.' 
                ], 400);
            }

            if($request->quantity > $remainingQuantity){
                 return response()->json([
                    'message' => 'Invoice quantity cannot exceed the remaining received quantity.',
                   'remaining_quantity' => $remainingQuantity,
                ], 400);
            }

            $totalPrice = $request->quantity * $purchaseOrderItem->unit_price;

            $invoiceItem = InvoiceItem::create([
                'invoice_id' => $invoice->id, 
                'purchase_order_item_id' => $purchaseOrderItem->id, 
                'product_id' => $purchaseOrderItem->product_id, 
                'service_id' => $purchaseOrderItem->service_id, 
                'quantity' => $request->quantity, 
                'unit_price' => $purchaseOrderItem->unit_price, 
                'total_price' => $totalPrice,
            ]);

            $subtotal = $invoice->items()->sum('total_price'); 
            $totalAmount = $subtotal + $invoice->tax - $invoice->discount; 
            $invoice->update([ 
                'subtotal' => $subtotal, 
                'total_amount' => $totalAmount, 
                ]);

            return response()->json([
               'message' => 'Invoice item added successfully.', 
               'invoice_item' => $invoiceItem, 
               'invoice_subtotal' => $subtotal, 
               'invoice_total' => $totalAmount, 
               'remaining_quantity' => $remainingQuantity - $request->quantity,
            ]);

          }
          
    }

