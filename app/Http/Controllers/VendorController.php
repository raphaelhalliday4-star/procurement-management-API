<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVendorRequest;
use App\Http\Requests\VendorRequest;
use App\Models\vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class VendorController extends Controller
{
     #[OA\Get(
        path: "/api/v1/vendor",
        summary: "Get all vendors",
        tags: ["Vendor"],
        security: [["bearerAuth" => []]],
         responses: [
            new OA\Response(
                response: 201,
                description: "vendors retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "vendors retrieved successfully"),
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
    public function index(){
       $vendor = vendor::latest()->get();
         Gate::authorize('viewAny', vendor::class);
       return response()->json([
          'vendor'=>$vendor
       ]);
    }

    #[OA\Post(
    path: "/api/v1/vendor",
    summary: "Create a new vendor",
    tags: ["Vendor"],
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["company_name","email","phone","registration_number","tax_number","address","bank_name","bank_account","status"],
            properties: [
                new OA\Property(property: "company_name",type: "string",example: "ABC Supplies Ltd" ),
                new OA\Property(property: "email", type: "string", format: "email", example: "abc@example.com"),
                new OA\Property(property: "phone",type: "string",format: "password",example: "secret123"),
                new OA\Property(property: "registration_number", type: "string", example: "RC123456" ),
                new OA\Property(property: "tax_number",type: "string",example: "TIN123456789"),
                new OA\Property(property: "address",type: "string",example: "plot 315 PH"),
                new OA\Property(property: "bank_name",type: "string",example: "UBA"),
                new OA\Property(property: "bank_account",type: "integer",example: "9161645867"),
                new OA\Property(property: "status",type: "string",example: "pending"),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Vendor created successfully",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "message",type: "string",example: "Vendor registered successfully" ),
                    new OA\Property(property: "vendor",type: "object",
                        properties: [
                            new OA\Property( property: "id", type: "integer", example: 1 ),
                            new OA\Property( property: "name", type: "string", example: "ABC Supplies Ltd"),
                            new OA\Property( property: "email", type: "string", example: "abc@example.com" ),
                            new OA\Property( property: "registration_number",type: "string", example: "RC123456"),
                            new OA\Property(property: "tax_number", type: "string", example: "TIN123456789"),
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
    public function store(VendorRequest $request){
        Gate::authorize('create', vendor::class);
        $vendor = vendor::create($request->validated());

        return response()->json([
            'message'=> 'Vendor created successfully',
            'Vendor'=>$vendor
        ]);
    }
     #[OA\Get(
        path: "/api/v1/vendor/{id}",
        summary: "Get an authenticated vendor",
        tags: ["Vendor"],
        security: [["bearerAuth" => []]],
          parameters: [
            new OA\Parameter(
                name: "id",
                description: "vendor id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
         responses: [
            new OA\Response(
                response: 201,
                description: "vendor retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "vendor retrieved successfully"),
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
    public function show($id){
        $vendor = Vendor::findOrFail($id);

        Gate::authorize('view', $vendor);
        return response()->json([
          'vendor'=>$vendor
       ]);         
    }

     #[OA\Put(
        path: "/api/v1/vendor",
        summary: "update an autenticated vendor",
        tags: ["Vendor"],
         security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["company_name","email","phone","address","bank_name","bank_number"],
                properties: [
                new OA\Property(property: "company_name",type: "string",example: "ABC Supplies Ltd" ),
                new OA\Property(property: "email", type: "string", format: "email", example: "abc@example.com"),
                new OA\Property(property: "phone",type: "string",format: "password",example: "secret123"),
                new OA\Property(property: "address",type: "string",example: "plot 315 PH"),
                new OA\Property(property: "bank_name",type: "string",example: "UBA"),
                new OA\Property(property: "number_number",type: "integer",example: "9161645867"),
                ]
            )
        ),
         responses: [
            new OA\Response(
                response: 201,
                description: "vendor updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "vendor updated successfully"),
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
    public function update(UpdateVendorRequest $request){
        $vendor = Auth::user()->vendor;

        if (!$vendor) {
            abort(403, 'You are not associated with a vendor.');
        }

        $data = $request->validated();
        Gate::authorize('update',$vendor);
        $vendor->update($data);

        return response()->json([
        'message' => 'Vendor updated successfully',
        'vendor' => $vendor
    ]);
    }

     #[OA\Delete(
        path: "/api/v1/vendor",
        summary: "delete a vendor",
        tags: ["Vendor"],
         security: [["bearerAuth" => []]],
         responses: [
            new OA\Response(
                response: 201,
                description: "vendor deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "vendor deleted successfully"),
                        new OA\Property(property: "vendor", type: "object"),
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
    public function destroy(vendor $vendor){
        $user = Auth::user();
      Gate::authorize('delete',$vendor);
        $vendor->delete();

        return response()->json([
            'message' => 'Vendor account deleted successfully'
        ]);
    }
}
