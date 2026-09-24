<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequestRequest;
use App\Http\Requests\UpdatePurchaaseRequest;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class PurchaseRequestController extends Controller
{
    #[OA\Get(
        path: "/api/v1/purchase-requests",
        summary: "Get all purchase requests for the authenticated user",
        tags: ["Purchase Requests"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Purchase requests retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "purchase_requests", type: "array", items: new OA\Items(type: "object")),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function index()
    {
        Gate::authorize('viewAny', PurchaseRequest::class);
        $user = Auth::user();
        $purchaseRequests = PurchaseRequest::with('user')->where('user_id', $user->id)->get();

        return response()->json([
            'purchase_requests' => $purchaseRequests,
        ], 200);
    }

    #[OA\Post(
        path: "/api/v1/purchase-requests",
        summary: "Create a purchase request",
        tags: ["Purchase Requests"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "description", "needed_by"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Laptop for project team"),
                    new OA\Property(property: "description", type: "string", example: "Request for 2 business laptops"),
                    new OA\Property(property: "needed_by", type: "string", format: "date", example: "2026-10-15"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Purchase request created successfully"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function store(PurchaseRequestRequest $request){
        Gate::authorize('create', PurchaseRequest::class);
     $user = Auth::user();

    $purchaseRequest = PurchaseRequest::create([
        'user_id' => $user->id,
        'title' => $request->title,
        'description' => $request->description,
        'status' => 'pending',
        'needed_by' => $request->needed_by,
    ]);

    return response()->json([
        'message' => 'Purchase request created successfully',
        'purchase_request' => $purchaseRequest,
    ], 201);
    }

    #[OA\Put(
        path: "/api/v1/purchase-requests/{purchaseRequest}",
        summary: "Update a purchase request",
        tags: ["Purchase Requests"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "purchaseRequest",
                in: "path",
                required: true,
                description: "Purchase request ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Laptop for project team"),
                    new OA\Property(property: "description", type: "string", example: "Updated request for 3 laptops"),
                    new OA\Property(property: "needed_by", type: "string", format: "date", example: "2026-10-20"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Purchase request updated successfully"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function update(UpdatePurchaaseRequest $request, PurchaseRequest $purchaseRequest){
        Gate::authorize('update', $purchaseRequest);
        $user = Auth::user();
        
        $purchaseRequest->update($request->validated());

        if($purchaseRequest->status !== 'pending'){
            return response()->json([
                'message' => 'You cannot update a purchase request that is not pending',
            ], 403);
        }

        if($purchaseRequest->user_id !== $user->id){
            return response()->json([
                'message' => 'You are not authorized to update this purchase request',
            ], 403);
        }

        return response()->json([
            'message' => 'Purchase request updated successfully',
            'purchase_request' => $purchaseRequest,
        ], 200);
    }

    #[OA\Patch(
        path: "/api/v1/purchase-requests/{purchaseRequest}/approve",
        summary: "Approve a pending purchase request",
        tags: ["Purchase Requests"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "purchaseRequest",
                in: "path",
                required: true,
                description: "Purchase request ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
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
    public function approve(PurchaseRequest $purchaseRequest){
        Gate::authorize('approve', $purchaseRequest);

        if($purchaseRequest->status !== 'pending'){
            return response()->json([
                'message' => 'You cannot approve a purchase request that is not pending',
            ], 403);
        }

        if($purchaseRequest->items()->doesntExist()){
            return response()->json([
                'message' => 'You must add at least one item to approve the purchase request'
            ], 400);
        }

        $purchaseRequest->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Purchase request approved successfully',
            'purchase_request' => $purchaseRequest,
        ], 200);
    }

    #[OA\Patch(
        path: "/api/v1/purchase-requests/{purchaseRequest}/reject",
        summary: "Reject a pending purchase request",
        tags: ["Purchase Requests"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "purchaseRequest",
                in: "path",
                required: true,
                description: "Purchase request ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Purchase request rejected successfully"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function reject(PurchaseRequest $purchaseRequest){
        Gate::authorize('reject', $purchaseRequest);

        if($purchaseRequest->status !== 'pending'){
            return response()->json([
                'message' => 'You cannot reject a purchase request that is not pending',
            ], 403);
        }

        $purchaseRequest->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Purchase request rejected successfully',
            'purchase_request' => $purchaseRequest,
        ], 200);
    }

    #[OA\Get(
        path: "/api/v1/purchase-requests/{purchaseRequest}",
        summary: "Get a single purchase request",
        tags: ["Purchase Requests"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "purchaseRequest",
                in: "path",
                required: true,
                description: "Purchase request ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Purchase request retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "purchase_request", type: "object"),
                    ]
                )
            ),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function show(Request $request, PurchaseRequest $purchaseRequest)
    {
        Gate::authorize('view', $purchaseRequest);
        $user = Auth::user();

        if ($purchaseRequest->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to view this purchase request.'
            ], 403);
        }

        return response()->json([
            'purchase_request' => $purchaseRequest->load('items.service'),
        ], 200);
    }

    #[OA\Delete(
        path: "/api/v1/purchase-requests/{purchaseRequest}",
        summary: "Delete a purchase request",
        tags: ["Purchase Requests"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "purchaseRequest",
                in: "path",
                required: true,
                description: "Purchase request ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Purchase request deleted successfully"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function destroy(PurchaseRequest $purchaseRequest)
    {
        Gate::authorize('delete', $purchaseRequest);
        $user = Auth::user();

        if ($purchaseRequest->user_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to delete this purchase request.'
            ], 403);
        }

        if ($purchaseRequest->status !== 'pending') {
            return response()->json([
                'message' => 'You cannot delete a purchase request that is not pending.'
            ], 403);
        }

        $purchaseRequest->delete();

        return response()->json([
            'message' => 'Purchase request deleted successfully.'
        ], 200);
    }

}
