<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoodsRecieptItemRequest;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrderItem;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class GoodsReceiptItemController extends Controller
{
    #[OA\Post(
        path: "/api/v1/goods-receipt-items",
        summary: "Add an item to a goods receipt",
        tags: ["Goods Receipt Items"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["goods_receipt_id", "purchase_order_item_id", "quantity_received"],
                properties: [
                    new OA\Property(property: "goods_receipt_id", type: "integer", example: 1),
                    new OA\Property(property: "purchase_order_item_id", type: "integer", example: 1),
                    new OA\Property(property: "quantity_received", type: "integer", example: 5),
                    new OA\Property(property: "notes", type: "string", example: "Box inspected"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Goods receipt item created successfully"),
            new OA\Response(response: 400, description: "Goods receipt or purchase order item is invalid"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(GoodsRecieptItemRequest $request){
        Gate::authorize('create', GoodsReceiptItem::class);
        $user = Auth::user();

        $goodsReciept = GoodsReceipt::findOrFail($request->goods_receipt_id);

        if($goodsReciept->status !== 'pending') {
            return response()->json([
                'message' => 'Items can only be added to goods receipts that are pending.'
            ], 400);
        }

        $purchaseOrder = $goodsReciept->purchaseOrder;

        if($purchaseOrder->status !== 'sent') {
            return response()->json([
                'message' => 'Items can only be added to goods receipts for purchase orders that have been sent.'
            ], 400);
        }

        $purchaseOrderItem = PurchaseOrderItem::findOrFail($request->purchase_order_item_id);

        if($purchaseOrderItem->purchase_order_id !== $purchaseOrder->id) {
            return response()->json([
                'message' => 'The purchase order item does not belong to the same purchase order as the goods receipt.'
            ], 400);
        }

        $alreadyReceived = GoodsReceipt::where('purchase_order_id', $purchaseOrderItem->id)->sum('quantity_received');
        $remainingQuantity = $purchaseOrderItem->quantity - $alreadyReceived;

        if($request->quantity_received > $remainingQuantity) {
            return response()->json([
                'message' => 'The quantity received cannot exceed the remaining quantity of the purchase order item.',
                'remaining_quantity' => $remainingQuantity
            ], 400);
        }

        $goodsRecieptItem = GoodsReceiptItem::create([
            'goods_receipt_id' => $goodsReciept->id,
            'purchase_order_item_id' => $purchaseOrderItem->id,
            'quantity_received' => $request->quantity_received,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Goods receipt item created successfully.',
            'data' => $goodsRecieptItem
        ], 201);
    }
}
