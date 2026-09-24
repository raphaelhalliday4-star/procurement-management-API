<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationItemRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class QuotationItemController extends Controller
{
    #[OA\Post(
        path: "/api/v1/quotation-items",
        summary: "Create a quotation item",
        tags: ["Quotations items"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["quotation_id", "purchase_request_item_id", "quantity", "unit_price"],
                properties: [
                    new OA\Property(property: "quotation_id", type: "integer", example: 1),
                    new OA\Property(property: "purchase_request_item_id", type: "integer", example: 2),
                    new OA\Property(property: "quantity", type: "integer", example: 3),
                    new OA\Property(property: "unit_price", type: "number", format: "float", example: 450.00),
                ]
            )
        ),
      responses: [
        new OA\Response(
            response: 201,
            description: "category created successfully",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "message",type: "string",example: "Vendor registered successfully" ),
                    new OA\Property(property: "category",type: "object",
                        properties: [
                            new OA\Property(property: "name",type: "string",example: "IT Equipment" ),
                            new OA\Property(property: "description",type: "string",example: "Books and resources covering software development, computers, programming, and emerging technologies." ),

                        ]
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 422,
            description: "Validation error",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "message",
                        type: "string",
                        example: "The email has already been taken."
                    ),
                    new OA\Property(
                        property: "errors",
                        type: "object"
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 500,
            description: "Server error",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "message", type: "string", example: "Something went wrong."),
                ]
            )
        ),
    ]
)]
    public function store(QuotationItemRequest $request)
    {
        Gate::authorize('create', QuotationItem::class);
        $user = Auth::user();
        if(!$user || !$user->vendor_id){
            return response()->json([
                'message' => 'You are not associated with a vendor.'
            ], 403);
        }

        $quotation = Quotation::find($request->quotation_id);
        if($quotation->vendor_id !== $user->vendor_id){
            return response()->json([
                'message' => 'You are not authorized to add items to this quotation.'
            ], 403);
        }

        $purchaseRequestItem = PurchaseRequestItem::find($request->purchase_request_item_id);
        if($purchaseRequestItem->purchaseRequest->id !== $quotation->rfq->purchase_request_id){
            return response()->json([
                'message' => 'The purchase request item does not belong to the same purchase request as the quotation.'
            ], 400);
        }

        if($request->quantity > $purchaseRequestItem->quantity){
            return response()->json([
                'message' => 'The quantity for the quotation item cannot exceed the quantity of the purchase request item.'
            ], 400);
        }

        $totalPrice = $request->quantity * $request->unit_price;
try{
        $quotationItem = QuotationItem::create([
            'quotation_id' => $quotation->id,
            'purchase_request_item_id' => $request->purchase_request_item_id,
            'service_id' => $purchaseRequestItem->service_id,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'total_price' => $totalPrice,
        ]);

        $quotation->total_amount = $quotation->items()->sum('total_price');
        $quotation->save();

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the quotation item.'
            ], 500);
        }

        return response()->json([
            'message' => 'Quotation item created successfully',
            'data' => $quotationItem
        ], 201);
    }

    #[OA\Put(
        path: "/api/v1/quotation-items/{quotationItem}",
        summary: "Update a quotation item",
        tags: ["Quotations items"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "quotationItem",
                in: "path",
                required: true,
                description: "Quotation item ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "quantity", type: "integer", example: 5),
                    new OA\Property(property: "unit_price", type: "number", format: "float", example: 500.00),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Quotation item updated successfully"),
            new OA\Response(response: 400, description: "Invalid quotation item request"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function update(Request $request, QuotationItem $quotationItem)
    {
        Gate::authorize('update', $quotationItem);
        $user = Auth::user();
        if(!$user || !$user->vendor_id){
            return response()->json([
                'message' => 'You are not associated with a vendor.'
            ], 403);
        }

        $quotation = $quotationItem->quotation;
        if(!$quotation || $quotation->vendor_id !== $user->vendor_id){
            return response()->json([
                'message' => 'You are not authorized to update items in this quotation.'
            ], 403);
        }

    if($quotation->status !== 'pending'){
        return response()->json([
            'message'=> 'you can not update a submitted quotation'
        ]);
    }

        $purchaseRequestItem = $quotationItem->purchaseRequestItem;
        if($request->quantity > $purchaseRequestItem->quantity){
            return response()->json([
                'message' => 'The quantity for the quotation item cannot exceed the quantity of the purchase request item.'
            ], 400);
        }

        $totalPrice = $request->quantity * $request->unit_price;

        $quotationItem->update([
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'total_price' => $totalPrice,
        ]);

        $quotation->total_amount = $quotation->items()->sum('total_price');
        $quotation->save();

        return response()->json([
            'message' => 'Quotation item updated successfully',
            'data' => $quotationItem
        ], 200);
        }
}
