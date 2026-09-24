<?php

namespace App\Http\Controllers;

use App\Http\Requests\RfqRequest;
use App\Models\PurchaseRequest;
use App\Models\Rfq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class RfqController extends Controller
{

 #[OA\Get(
        path: "/api/v1/rfqs",
        summary: "Get an Rfqs of an authenticated user",
        tags: ["RFQs"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Rfqs retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Rfqs retrieved successfully."),
                        new OA\Property(
                            property: "rfqs",
                            type: "array",
                            items: new OA\Items(type: "object")
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function index(){
        Gate::authorize('viewAny', Rfq::class);
        $user = Auth::user();
        $rfq = Rfq::with('purchaseRequest')->where('created_by', Auth::id())->get();

        return response()->json([
            'Rfq'=> $rfq
        ]);
    }

    #[OA\Post(
        path: "/api/v1/rfqs",
        summary: "Create an RFQ for an approved purchase request",
        tags: ["RFQs"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["purchase_request_id", "title", "opening_date", "closing_date"],
                properties: [
                    new OA\Property(property: "purchase_request_id", type: "integer", example: 1),
                    new OA\Property(property: "title", type: "string", example: "Office furniture procurement"),
                    new OA\Property(property: "description", type: "string", nullable: true, example: "Procurement of desks and chairs"),
                    new OA\Property(property: "opening_date", type: "string", format: "date-time", example: "2026-09-10 09:00:00"),
                    new OA\Property(property: "closing_date", type: "string", format: "date-time", example: "2026-09-20 17:00:00"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "RFQ created successfully"),
            new OA\Response(response: 400, description: "Purchase request must be approved"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(RfqRequest $request){
        Gate::authorize('create', Rfq::class);
        $user = Auth::user();

        $purchaseRequest = PurchaseRequest::find($request->purchase_request_id);

        if($purchaseRequest->status !== 'approved'){
            return response()->json(['message' => 'Purchase Request must be approved before creating RFQ.'], 400);
        }

        $rfqNumber = 'RFQ-' . date('Y') . '-' . str_pad(Rfq::count() + 1,5,'0',STR_PAD_LEFT);

        $rfq = Rfq::create([
            'purchase_request_id' => $request->purchase_request_id,
            'rfq_number' => $rfqNumber,
            'title' => $request->title,
            'description' => $request->description,
            'opening_date' => $request->opening_date,
            'closing_date' => $request->closing_date,
            'created_by' => $user->id,
            'status' => 'draft',
        ]);

        return response()->json([
            'message' => 'RFQ created successfully',
            'rfq' => $rfq
        ], 201);
    }

    #[OA\Patch(
        path: "/api/v1/rfqs/{rfq}",
        summary: "Publish a draft RFQ",
        tags: ["RFQs"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "rfq",
                in: "path",
                required: true,
                description: "RFQ ID",
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

    public function publish(Rfq $rfq){
        Gate::authorize('publish', $rfq);
        $user = Auth::user();

        if($rfq->created_by !== $user->id){
            return response()->json([
                'message'=> 'Unauthorized access',
            ], 403);
        }

        if($rfq->status !== 'draft'){
            return response()->json([
                'message' => 'Only draft RFQs can be published.',
            ], 409);
        }

        if ($rfq->vendors()->count() === 0) {
        return response()->json([
            'message' => 'At least one vendor must be selected before publishing the RFQ.'
        ], 400);
    }

    if (now()->gte($rfq->opening_date)) {
        return response()->json([
            'message' => 'The opening date must be in the future.'
        ], 400);
    }

    if ($rfq->closing_date->lte($rfq->opening_date)) {
        return response()->json([
            'message' => 'The closing date must be after the opening date.'
        ], 400);
    }

        $rfq->update(['status' => 'published']);

        return response()->json([
            'message'=> 'RFQ has been published successfully',
            'rfq'=> $rfq->fresh(),
        ], 200);
    }

    #[OA\Patch(
        path: "/api/v1/rfqs/{rfq}/close",
        summary: "Close a published RFQ",
        tags: ["RFQs"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "rfq",
                in: "path",
                required: true,
                description: "RFQ ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "RFQ closed successfully"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "Only the RFQ creator can close it"),
            new OA\Response(response: 404, description: "RFQ not found"),
            new OA\Response(response: 409, description: "RFQ is not ready to be closed"),
        ]
    )]
    public function close(Rfq $rfq){
        Gate::authorize('close', $rfq);
        $user = Auth::user();

        if($rfq->created_by !== $user->id){
            return response()->json([
                'message'=> 'Unauthorized access',
            ], 403);
        }

        if($rfq->status !== 'published'){
            return response()->json([
                'message' => 'Only published RFQs can be closed.',
            ], 409);
        }

        if(now()->lt($rfq->closing_date)){
            return response()->json([
                'message' => 'The RFQ cannot be closed before the closing date.',
            ], 409);
        }

        $rfq->update(['status' => 'closed']);

        return response()->json([
            'message'=> 'RFQ has been closed successfully',
            'rfq'=> $rfq->fresh(),
        ], 200);
    }
}
