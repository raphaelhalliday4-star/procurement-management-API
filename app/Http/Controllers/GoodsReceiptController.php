<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoodsRecieptRequest;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class GoodsReceiptController extends Controller
{
      #[OA\Post(
            path: "/api/v1/goods-receipts",
            summary: "Create a goods receipt",
            tags: ["Goods Receipts"],
            security: [["bearerAuth" => []]],
            requestBody: new OA\RequestBody(
                  required: true,
                  content: new OA\JsonContent(
                        required: ["purchase_order_id", "received_date"],
                        properties: [
                              new OA\Property(property: "purchase_order_id", type: "integer", example: 1),
                              new OA\Property(property: "received_date", type: "string", format: "date", example: "2026-09-11"),
                              new OA\Property(property: "notes", type: "string", example: "Received in good condition"),
                        ]
                  )
            ),
         responses: [
            new OA\Response(
                response: 201,
                description: "user created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "user created successfully"),
                        new OA\Property(property: "member", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The name field is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Unauthorized"),
                    ]
                )
            ),
        ]
    )]
    public function store(GoodsRecieptRequest $request)
    {
             Gate::authorize('create', GoodsReceipt::class);
       $user = Auth::user();
       $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);
             if(!in_array($purchaseOrder->status, ['sent', 'partially_received'], true)) {
         return response()->json([
                        'message' => 'Goods receipt can only be created for sent or partially received purchase orders.'
         ], 400);
       }

       $receiptNumber = 'GR-' . date('Y') . '-' . str_pad( GoodsReceipt::count() + 1, 5, '0', STR_PAD_LEFT );
    try {
         $goodsReceipt = GoodsReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'received_date' => $request->received_date,
                'notes' => $request->notes,
                'receipt_number' => $receiptNumber,
                'received_by' => $user->id,
                'status' => 'pending',
          ]);
             } catch (\Throwable $exception) {
                report($exception);

                return response()->json([
                    'message' => 'Goods receipt could not be created.'
                ], 500);
             }
    
          return response()->json([
                'message' => 'Goods receipt created successfully.',
                'data' => $goodsReceipt
          ], 201);
    }
}
