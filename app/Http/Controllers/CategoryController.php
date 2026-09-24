<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
     #[OA\Get(
        path: "/api/v1/category",
        summary: "Get all categories",
        tags: ["category"],
         responses: [
            new OA\Response(
                response: 201,
                description: "categories retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "category retrieved successfully"),
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
          Gate::authorize('viewAny', Category::class);
        $category = Category::latest()->get();

        return response()->json([
            'categories'=>$category
        ]);
     }


     #[OA\Post(
    path: "/api/v1/category",
    summary: "Create a new vendor",
    tags: ["category"],
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "description"],
            properties: [
                new OA\Property(property: "name",type: "string",example: "IT Equipment" ),
                new OA\Property(property: "description",type: "string",example: "Books and resources covering software development, computers, programming, and emerging technologies." ),

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

    public function store(Request $request){
         Gate::authorize('create', Category::class);
       $data = $request->validate([
            'name'=>'required|string',
            'description'=>'required|string'
        ]);
        
        $category = Category::create($data);

        return response()->json([
            'message'=>'category created successfully',
            'category'=>$category
        ]);

    }


     #[OA\Put(
        path: "/api/v1/category/{id}",
        summary: "update a category",
        tags: ["category"],
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
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "description"],
                properties: [
                new OA\Property(property: "name",type: "string",example: "IT Equipment" ),
                new OA\Property(property: "description",type: "string",example: "Books and resources covering software development, computers, programming, and emerging technologies." ),
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
    public function update(Request $request, $id){
       $category = Category::findOrFail($id);
         Gate::authorize('update', $category);
        
       if(!$category){
         return response()->json([
            'message'=>'This category does not exist'
         ]);
       }

        $data = $request->validate([
            'name'=>'sometimes|string',
            'description'=>'sometimes|string'
        ]); 

        $category->update($data);

        return response()->json([
            'message'=>'category updated successfully',
            'category'=>$category
        ]);
    }

     #[OA\Delete(
        path: "/api/v1/category/{id}",
        summary: "delete a category",
        tags: ["category"],
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
                description: "category deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "category deleted successfully"),
                        new OA\Property(property: "category", type: "object"),
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
    public function destroy($id){
        $category = Category::findOrFail($id);
        Gate::authorize('delete', $category);
        $category->delete();

        return response()->json([
            'message'=> 'category deleted successfully'
        ]);
    }
}
