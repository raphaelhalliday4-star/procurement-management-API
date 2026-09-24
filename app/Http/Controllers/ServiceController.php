<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class ServiceController extends Controller
{
    #[OA\Get(
        path: "/api/v1/service",
        summary: "Get all products and services",
        tags: ["service"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Products and services retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "services", type: "array", items: new OA\Items(type: "object")),
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function index(){
        Gate::authorize('viewAny', Service::class);
        $service = Service::latest()->get();

        return response()->json([
            'services' => $service
        ]);
    }

    #[OA\Post(
        path: "/api/v1/service",
        summary: "Create a product or service",
        tags: ["service"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["category_id", "sku", "name", "type", "unit", "estimated_price"],
                properties: [
                    new OA\Property(property: "category_id", type: "integer", example: 1),
                    new OA\Property(property: "sku", type: "string", example: "IT-001"),
                    new OA\Property(property: "name", type: "string", example: "Laptop"),
                    new OA\Property(property: "description", type: "string", example: "Business laptop"),
                    new OA\Property(property: "type", type: "string", enum: ["product", "service"], example: "product"),
                    new OA\Property(property: "unit", type: "string", example: "pcs"),
                    new OA\Property(property: "estimated_price", type: "number", format: "float", example: 1250.00),
                    new OA\Property(property: "status", type: "string", example: "active"),
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
    public function store(ServiceRequest $request){
        Gate::authorize('create', Service::class);
        $service = Service::create($request->validated());
          
        switch($service->type){
            case 'service':
                  return response()->json([
                'message' => 'Service created successfully',
                'service' => $service
            ], 201);
            break;
            case 'product':
                return response()->json([
                    'message' => 'Product created successfully',
                    'service' => $service
                ], 201);
        }

        return response()->json([
            'message' => 'Service type is invalid.',
        ], 422);
    }

    #[OA\Put(
        path: "/api/v1/service/{id}",
        summary: "Update a product or service",
        tags: ["service"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Product or service ID",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "category_id", type: "integer", example: 1),
                    new OA\Property(property: "sku", type: "string", example: "IT-001"),
                    new OA\Property(property: "name", type: "string", example: "Laptop"),
                    new OA\Property(property: "description", type: "string", example: "Business laptop"),
                    new OA\Property(property: "type", type: "string", enum: ["product", "service"], example: "product"),
                    new OA\Property(property: "unit", type: "string", example: "pcs"),
                    new OA\Property(property: "estimated_price", type: "number", format: "float", example: 1250.00),
                    new OA\Property(property: "status", type: "string", example: "active"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Product or service updated successfully"),
            new OA\Response(response: 404, description: "Product or service not found"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function update(UpdateServiceRequest $request, $id){
        $service = Service::findOrFail($id);
        Gate::authorize('update', $service);
        $service->update($request->validated());

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service
        ]);
    }

    #[OA\Delete(
        path: "/api/v1/service/{id}",
        summary: "Delete a product or service",
        tags: ["service"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Product or service ID",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Product or service deleted successfully"),
            new OA\Response(response: 404, description: "Product or service not found"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function destroy($id){
        $service = Service::findOrFail($id);
        Gate::authorize('delete', $service);
        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully'
        ]);
    }
}
