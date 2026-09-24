<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorDocumentRequest;
use App\Models\vendor_document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class VendorDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
      #[OA\Get(
        path: "/api/v1/get-documents",
        summary: "Get all vendor documents",
        tags: ["document"],
        security: [["bearerAuth" => []]],
         responses: [
            new OA\Response(
                response: 200,
                description: "user retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "documents retrieved successfully"),
                        new OA\Property(property: "documents", type: "object"),
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
    public function index()
    {
        Gate::authorize('viewAny', vendor_document::class);
        $documents = vendor_document::all();

        return response()->json([
            'documents'=>$documents
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
     
    #[OA\Post(
    path: "/api/v1/vendors/documents",
    summary: "Upload a vendor documents",
    tags: ["document"],
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                type: "object",
                required: ["document_type", "file"],
                properties: [
                new OA\Property(
                    property: "document_type",
                    type: "string",
                    enum: [
                         "certificate_of_incorporation",
                        "tax_certificate",
                        "bank_verification",
                        "business_license",
                        "company_profile"
                        ],
                        example: "tax_certificate"
                    ),
                    new OA\Property(
                        property: "file",
                        type: "string",
                        format: "binary"
                    )
                ]
            )
        )
    ),
 responses: [
            new OA\Response(
                response: 201,
                description: "documents uploaded successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "file uploaded successfully"),
                        new OA\Property(property: "author", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The file  is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Unauthorized",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", example: "Unauthorized"),
                    ]
                )
            ),
        ]
    )]
    public function store(VendorDocumentRequest $request)
    {
        Gate::authorize('create', vendor_document::class);
        $user = Auth::user();
        if(!$user->vendor_id){
            return response()->json([
             'message' => 'You are not associated with a vendor.'
        ], 403);

        }

        $file = $request->file('file');
        $filename = Str::random(15).'.'.time() .'.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('vendor_documents', $filename, 'public');

        $document = vendor_document::create([
            'vendor_id' => $user->vendor_id,
            'document_type' => $request->document_type,
            'file_path' => $path,
            'status' => 'pending',
        ]);

         return response()->json([
            'message' => 'Document uploaded successfully',
            'document' => $document,
        ], 201);
        
    }

    /**
     * Display the specified resource.
     */
     #[OA\Get(
        path: "/api/v1/vendors/documents",
        summary: "Get an authenticated vendor's documents",
        tags: ["document"],
        security: [["bearerAuth" => []]],
         responses: [
            new OA\Response(
                response: 201,
                description: "documents retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "documents retrieved successfully"),
                        new OA\Property(property: "documents", type: "object"),
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

    public function show()
    {
        Gate::authorize('viewAny', vendor_document::class);
        $user = Auth::user();

        if(!$user->vendor_id){
            return response()->json([
                'message'=> 'You are not associated with a vendor.'
            ], 403);
        }

        $document = Vendor_document::where('vendor_id', $user->vendor_id)->get();  

        return response()->json([
            'documents' => $document
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/api/v1/vendors/documents/{id}",
        summary: "Update a vendor document",
        tags: ["document"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [new OA\Property(property: "document_type", type: "string", example: "tax_certificate")]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Vendor document updated successfully"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Vendor document not found"),
        ]
    )]
    public function update(Request $request, string $id)
    {
        $document = vendor_document::findOrFail($id);
        Gate::authorize('update', $document);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/v1/vendors/documents/{id}",
        summary: "Delete a vendor document",
        tags: ["document"],
        security: [["bearerAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Vendor document deleted successfully"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 404, description: "Vendor document not found"),
        ]
    )]
    public function destroy(string $id)
    {
        $document = vendor_document::findOrFail($id);
        Gate::authorize('delete', $document);
        //
    }
}
