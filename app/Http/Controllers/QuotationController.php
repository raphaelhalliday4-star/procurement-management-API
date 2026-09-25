<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class QuotationController extends Controller
{

    #[OA\Get(
        path: "/api/v1/quotations",
        summary: "Get quotations for the authenticated user's vendor",
        tags: ["Quotations"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Quotations retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Quotations retrieved successfully."),
                        new OA\Property(
                            property: "quotations",
                            type: "array",
                            items: new OA\Items(type: "object")
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function index()
    {
        Gate::authorize('viewAny', Quotation::class);
        $user = Auth::user();
        $quotations = Quotation::with('items')->where('vendor_id', $user->vendor_id)->get();

        return response()->json([
            'message' => 'Quotations retrieved successfully.',
            'quotations' => $quotations,
        ], 200);
    }

    #[OA\Post(
        path: "/api/v1/quotations",
        summary: "Create a quotation for an RFQ",
        tags: ["Quotations"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["rfq_id", "quotation_date", "valid_until"],
                properties: [
                    new OA\Property(property: "rfq_id", type: "integer", example: 1),                    
                    new OA\Property(property: "quotation_date", type: "string", format: "date", example: "2026-09-04"),
                    new OA\Property(property: "valid_until", type: "string", format: "date", example: "2026-09-18"),
                    new OA\Property(property: "notes", type: "string", example: "Includes delivery and installation support"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Quotation created successfully"),
            new OA\Response(response: 403, description: "Vendor not associated or unauthorized"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function store(QuotationRequest $request){
        Gate::authorize('create', Quotation::class);
        $user = Auth::user();
        if(!$user->vendor_id){
            return response()->json([
                'message' => 'You are not associated with a vendor.'
            ], 403);
        } 

        $quotationNumber = 'QT-' . date('Y') . '-' . str_pad(Quotation::count() + 1,5,'0',STR_PAD_LEFT);

          $quotation = Quotation::create([
            'rfq_id'=>$request->rfq_id,
            'vendor_id' => $user->vendor_id,
            'quotation_number' => $quotationNumber,
            'quotation_date' => $request->quotation_date,
            'valid_until' => $request->valid_until,
            'status' => 'pending',
            'total_amount' => 0,
            'notes' => $request->notes,
        ]);

        

        return response()->json([
            'message' => 'Quotation created successfully.',
            'quotation' => $quotation,
        ], 201);
    }

    #[OA\Patch(
        path: "/api/v1/quotations/{quotation}",
        summary: "Submit a pending quotation",
        tags: ["Quotations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "quotation",
                in: "path",
                required: true,
                description: "Quotation ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Quotation submitted successfully"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 403, description: "The quotation does not belong to the authenticated vendor"),
            new OA\Response(response: 404, description: "Quotation not found"),
            new OA\Response(response: 409, description: "Only pending quotations can be submitted"),
        ]
    )]
    public function submit(Quotation $quotation)
    {
        Gate::authorize('submit', $quotation);
        $user = Auth::user();

        if ($quotation->vendor_id !== $user->vendor_id) {
            return response()->json([
                'message' => 'You are not authorized to submit this quotation.',
            ], 403);
        }

        if ($quotation->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending quotations can be submitted.',
            ], 409);
        }

        $rfq = $quotation->rfq;

        if ($rfq->status !== 'published') {
            return response()->json([
                'message' => 'The RFQ is not currently accepting quotations.'
            ], 400);
        }

        // if (now()->lt($rfq->opening_date)) {
        //     return response()->json([
        //         'message' => 'The quotation submission window has not opened yet.'
        //     ], 400);
        // }

        if (now()->gt($rfq->closing_date)) {
            return response()->json([
                'message' => 'The quotation submission window has closed.'
            ], 400);
        }

        if ($quotation->items()->count() === 0) {
            return response()->json([
                'message' => 'A quotation must contain at least one item before submission.'
            ], 400);
        }

        $quotation->update(['status' => 'submitted']);

        return response()->json([
            'message' => 'Quotation submitted successfully.',
            'quotation' => $quotation->fresh(),
        ], 200);
    }

    #[OA\Delete(
        path: "/api/v1/quotations/{quotation}",
        summary: "Delete a quotation",
        tags: ["Quotations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "quotation",
                in: "path",
                required: true,
                description: "Quotation ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Quotation deleted successfully"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function destroy(Quotation $quotation)
    {
        Gate::authorize('delete', $quotation);
        $user = Auth::user();
        if ($user->vendor_id !== $quotation->vendor_id) {
            return response()->json([
                'message' => 'You are not authorized to delete this quotation.'
            ], 403);
        }

        $quotation->delete();

        return response()->json([
            'message' => 'Quotation deleted successfully.'
        ], 200);
    }

    #[OA\Patch(
        path: "/api/v1/quotations/{quotation}/evaluate",
        summary: "Move a submitted quotation under review",
        tags: ["Quotations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "quotation",
                in: "path",
                required: true,
                description: "Quotation ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Quotation is now under review"),
            new OA\Response(response: 400, description: "Quotation cannot be evaluated"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Quotation not found"),
        ]
    )]
    public function evaluate(Quotation $quotation){
    Gate::authorize('evaluate', $quotation);
    $user = Auth::user();

    if($quotation->status !== 'submitted'){
        return response()->json([
            'message' => 'Only submitted quotations can be evaluated.'
        ], 400);
    }

    // if($quotation->rfq->status !== 'closed'){
    //     return response()->json([
    //         'message' => 'Quotations can only be evaluated for closed RFQs.'
    //     ], 400);
    // }

    $quotation->update(['status' => 'under_review']);

    return response()->json([
        'message' => 'Quotation is now under review.',
        'quotation' => $quotation->fresh(),
    ], 200);
    }

    #[OA\Patch(
        path: "/api/v1/quotations/{quotation}/accept",
        summary: "Accept a quotation under review",
        tags: ["Quotations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "quotation",
                in: "path",
                required: true,
                description: "Quotation ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Quotation accepted successfully"),
            new OA\Response(response: 400, description: "Quotation cannot be accepted"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Quotation not found"),
        ]
    )]
    public function accept(Quotation $quotation){
        Gate::authorize('evaluate', $quotation);
        $user = Auth::user();

        if($quotation->status !== 'under_review'){
            return response()->json([
                'message' => 'Only quotations under review can be accepted.'
            ], 400);
        }

        // if($quotation->rfq->status !== 'closed'){
        //     return response()->json([
        //         'message' => 'Quotations can only be accepted for closed RFQs.'
        //     ], 400);
        // }

        $quotation->update(['status' => 'accepted']);

        return response()->json([
            'message' => 'Quotation has been accepted.',
            'quotation' => $quotation->fresh(),
        ], 200);
    }

    #[OA\Patch(
        path: "/api/v1/quotations/{quotation}/reject",
        summary: "Reject a quotation under review",
        tags: ["Quotations"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "quotation",
                in: "path",
                required: true,
                description: "Quotation ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Quotation rejected successfully"),
            new OA\Response(response: 400, description: "Quotation cannot be rejected"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Quotation not found"),
        ]
    )]
    public function reject(Quotation $quotation){
        Gate::authorize('evaluate', $quotation);
        $user = Auth::user();

        if($quotation->status !== 'under_review'){
            return response()->json([
                'message' => 'Only quotations under review can be rejected.'
            ], 400);
        }

        if($quotation->rfq->status !== 'closed'){
            return response()->json([
                'message' => 'Quotations can only be rejected for closed RFQs.'
            ], 400);
        }

        $quotation->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Quotation has been rejected.',
            'quotation' => $quotation->fresh(),
        ], 200);
    }
}
