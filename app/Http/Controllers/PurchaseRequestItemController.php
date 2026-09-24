<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequestItemRequest;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class PurchaseRequestItemController extends Controller
{
    #[OA\Post(
        path: "/api/v1/purchase-request-items",
        summary: "Create a purchase request item",
        tags: ["Purchase Request Items"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["purchase_request_id", "service_id", "quantity"],
                properties: [
                    new OA\Property(property: "purchase_request_id", type: "integer", example: 1),
                    new OA\Property(property: "service_id", type: "integer", example: 1),
                    new OA\Property(property: "quantity", type: "integer", example: 1),
                    new OA\Property(property: "description", type: "string", example: "Dell laptop for operations team"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Purchase request item created successfully"),
            new OA\Response(response: 400, description: "Purchase request not pending"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function store(PurchaseRequestItemRequest $request)
    {
        Gate::authorize('create', PurchaseRequestItem::class);
        $user = Auth::user();
        $purchaseRequest = PurchaseRequest::where('id', $request->purchase_request_id)->where('user_id', $user->id)->first();

        if (!$purchaseRequest) {
        return response()->json([
            'message' => 'Purchase request not found or you are not authorized to add items to it.'
        ], 403);
    }

    if ($purchaseRequest->status !== 'pending') {
        return response()->json([
            'message' => 'Items can only be added to pending purchase requests.'
        ], 400);
    }

        $purchaseRequestItem = PurchaseRequestItem::create($request->validated());


        return response()->json([
            'message' => 'Purchase request item created successfully',
            'purchase_request_item' => $purchaseRequestItem,
        ], 201);
    }

    #[OA\Put(
        path: "/api/v1/purchase-request-items/{purchaseRequestItem}",
        summary: "Update a purchase request item",
        tags: ["Purchase Request Items"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "purchaseRequestItem",
                in: "path",
                required: true,
                description: "Purchase request item ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "quantity", type: "integer", example: 3),
                    new OA\Property(property: "description", type: "string", example: "Updated item description"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Purchase request item updated successfully"),
            new OA\Response(response: 400, description: "Purchase request not pending"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function update(Request $request, PurchaseRequestItem $purchaseRequestItem)
    {
        Gate::authorize('update', $purchaseRequestItem);
        $user = Auth::user();
        $purchaseRequest = $purchaseRequestItem->purchaseRequest;

        if ($purchaseRequest->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to update this purchase request item.'
            ], 403);
        }

        if ($purchaseRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Items can only be updated in pending purchase requests.'
            ], 400);
        }

        $purchaseRequestItem->update($request->validated());

        return response()->json([
            'message' => 'Purchase request item updated successfully',
            'purchase_request_item' => $purchaseRequestItem,
        ], 200);
    }
}
