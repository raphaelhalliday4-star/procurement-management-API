<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes\Delete;
use OpenApi\Attributes\Get;
use OpenApi\Attributes\Put;
use Tymon\JWTAuth\Facades\JWTAuth;
use PhpParser\Node\Stmt\TryCatch;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/v1/register",
        summary: "Register a new user",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password","vendor_id"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jane Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "jane@example.com"),
                     new OA\Property(property: "vendor_id", type: "integer", example: 1),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret123"),

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
    public function register(UserRequest $request){
        $user = User::create($request->validated());

        $user->assignRole('vendor');

        return response()->json([
           'message'=>'user created successfully',
           'user'=>$user
        ]);
    }

     #[OA\Post(
        path: "/api/v1/login",
        summary: "login a user",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "jane@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret123"),

                ]
            )
        ),
         responses: [
            new OA\Response(
                response: 201,
                description: "user logged  in successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "user logged successfully"),
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
    public function login(Request $request){
     $credentials = $request->only('email', 'password');

     if (!$token = JWTAuth::attempt($credentials)) {
         return response()->json(['error' => 'Unauthorized'], 401);
     }

        return $this->respondWithToken($token);
   }

    protected function respondWithToken($token){
        return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
        ]);
    }

    #[OA\Post(
        path: "/api/v1/logout",
        summary: "Log out the authenticated user",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logged out successfully"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ]
    )]
    public function logOut(){
        Auth::logout();

        return response()->json([
            'message' => 'Successfully logged out'
            ]);
    }

      #[OA\Get(
        path: "/api/v1/get-user",
        summary: "Get an autenticated user",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
         responses: [
            new OA\Response(
                response: 201,
                description: "user retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "user retrieved successfully"),
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
    public function getUser(){
        $user = Auth::user();
        Gate::authorize('view', $user);
        if(!$user){
            return response()->json([
               'message'=>'Unauthorized access'
            ]);
        }

        return response()->json([
            'user'=>$user
        ]);
    }
        
    #[OA\Get(
        path: "/api/v1/users",
        summary: "Get all users by super Admin",
        tags: ["Auth"],
         security: [["bearerAuth" => []]],
         responses: [
            new OA\Response(
                response: 201,
                description: "users retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "users retrieved successfully"),
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

    public function getAllUser(){
        Gate::authorize('viewAny', User::class);
        $user = User::all();

        return response()->json([
            'user'=>$user,
        ]);
    }

    #[OA\Put(
        path: "/api/v1/user",
        summary: "update an autenticated user",
        tags: ["Auth"],
         security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jane Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "jane@example.com"),
                     new OA\Property(property: "vendor_id", type: "integer", example:1),
                    new OA\Property(property: "password", type: "string", format: "password", example: "secret123"),

                ]
            )
        ),
         responses: [
            new OA\Response(
                response: 201,
                description: "user retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "user retrieved successfully"),
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

    public function updateUser(UpdateUserRequest $request)
{
    $user = Auth::user();
    Gate::authorize('update', $user);

    $data = $request->validated();

    if (isset($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    }

    $user->update($data);

    return response()->json([
        'message' => 'User updated successfully',
        'user' => $user
    ]);
}

    #[OA\Delete(
        path: "/api/v1/user",
        summary: "delete a user",
        tags: ["Auth"],
         security: [["bearerAuth" => []]],
         responses: [
            new OA\Response(
                response: 201,
                description: "user deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "user deleted successfully"),
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
    public function destroy(){
          $user = Auth::user();
            Gate::authorize('delete', $user);
          $user->delete();

          return response()->json([
            'message'=>'user has been deleted successfully',
          ]);
    }

}
