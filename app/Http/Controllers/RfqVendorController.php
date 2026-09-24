<?php

namespace App\Http\Controllers;

use App\Http\Requests\RfqVendorRequest;
use App\Models\Rfq;
use App\Models\Rfq_vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class RfqVendorController extends Controller
{
    #[OA\Post(
        path: "/api/v1/rfq-vendors",
        summary: "Add a vendor to a draft RFQ",
        tags: ["RFQ Vendors"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["rfq_id", "vendor_id"],
                properties: [
                    new OA\Property(property: "rfq_id", type: "integer", example: 1),
                    new OA\Property(property: "vendor_id", type: "integer", example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Vendor added to RFQ successfully"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 409, description: "Vendor already added to this RFQ"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function store(RfqVendorRequest $request){
        Gate::authorize('create', Rfq_vendor::class);
        $rfq = Rfq::find($request->rfq_id);

        if($rfq->status !== 'draft'){
            return response()->json([
                'message'=> 'vendors can only be added to a draft RFQ'
            ], 400);
        }

        $selectced = Rfq_vendor::where('rfq_id', $rfq->id)->where('vendor_id', $request->vendor_id)->exists();

        if($selectced){
            return response()->json([
                'message'=> 'Vendor already added to this RFQ'
            ], 409);
        }

        $rfqVendor = Rfq_vendor::create([
            'rfq_id' => $request->rfq_id,
            'vendor_id' => $request->vendor_id,
        ]);

        return response()->json([
            'message'=> 'Vendor added to RFQ successfully',
            'rfq_vendor' => $rfqVendor
        ], 201);

    }

    #[OA\Delete(
        path: "/api/v1/rfq-vendors/{rfqVendor}",
        summary: "Remove a vendor from a draft RFQ",
        tags: ["RFQ Vendors"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "rfqVendor",
                in: "path",
                required: true,
                description: "RFQ vendor assignment ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Vendor removed from RFQ successfully"),
            new OA\Response(response: 400, description: "Vendors can only be removed from a draft RFQ"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "RFQ vendor assignment not found"),
        ]
    )]
    public function destroy(Rfq_vendor $rfqVendor){
        Gate::authorize('delete', $rfqVendor);
        $rfq = Rfq::find($rfqVendor->rfq_id);

        if($rfq->status !== 'draft'){
            return response()->json([
                'message'=> 'vendors can only be removed from a draft RFQ'
            ], 400);
        }

        $rfqVendor->delete();

        return response()->json([
            'message'=> 'Vendor removed from RFQ successfully'
        ], 200);
    }
    
}
