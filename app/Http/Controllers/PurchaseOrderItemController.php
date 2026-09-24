<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderItemRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequestItem;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class PurchaseOrderItemController extends Controller
{
    #[OA\Post(
        path: "/api/v1/purchase-order-items",
        summary: "Add an item to a purchase order",
        tags: ["Purchase Order Items"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["purchase_order_id", "quotation_item_id", "quantity"],
                properties: [
                    new OA\Property(property: "purchase_order_id", type: "integer", example: 1),
                    new OA\Property(property: "quotation_item_id", type: "integer", example: 1),
                    new OA\Property(property: "quantity", type: "number", example: 5),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Purchase order item created successfully"),
            new OA\Response(response: 400, description: "Purchase order or quotation item is invalid"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(PurchaseOrderItemRequest $request){
            Gate::authorize('create', PurchaseOrderItem::class);
          $user = Auth::user();
          $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);

          if($purchaseOrder->status !== 'draft'){
              return response()->json([
                  'message' => 'Cannot add items to a purchase order that is not in draft status.'
              ], 400);
          }

          $quotationItem = QuotationItem::find($request->quotation_item_id);
          if($quotationItem->quotation_id !== $purchaseOrder->quotation_id){
              return response()->json([
                  'message' => 'Quotation item does not belong to the same quotation as the purchase order.'
              ], 400);
          }

          if($quotationItem->quantity > $quotationItem->quantity){
             return response()->json([
                'message' => 'Purchase order quantity cannot exceed the approved quotation quantity.' 
             ], 400);
          }

          $totalPrice = $quotationItem->unit_price * $request->quantity;

          $purchaseOrderItem = PurchaseRequestItem::create([
              'purchase_order_id' => $purchaseOrder->id,
              'quotation_item_id' => $quotationItem->id,
              'service_id' => $quotationItem->service_id,
              'quantity' => $request->quantity,
              'unit_price' => $quotationItem->unit_price,
              'total_price' => $totalPrice,
          ]);

          $purchaseOrder->total_amount = $purchaseOrder->items()->sum('total_price') + $purchaseOrder->tax - $purchaseOrder->discount; 

          $purchaseOrder->save();

          return response()->json([ 
            'message' => 'Purchase order item added successfully.', 
            'purchase_order_item' => $purchaseOrderItem, 
            'purchase_order_total' => $purchaseOrder->total_amount, 
            ], 201);
    }
}
