<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvokeRequest;
use App\Http\Requests\PermissionRequest;
use App\Http\Requests\RemovePermitRequest;
use App\Http\Requests\UserRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
     #[OA\Get(
        path: "/api/get-roles",
        summary: "Get all roles",
        tags: ["permission"],
        security: [["bearerAuth" => []]],
         responses: [
           new OA\Response(
                response: 200,
                description: "Roles retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "roles retrieved successfully"),
                        new OA\Property(property: "roles", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "role not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Role not found"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The isbn field has already been taken."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
        ]
    )]
    public function getRoles()
    {
        Gate::authorize('manage', Permission::class);

        return response()->json([
            'roles'=> Role::all(),
        ]);
    }

    #[OA\Get(
        path: "/api/get-permissions",
        summary: "Get all permissions",
        tags: ["permission"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Permissions retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "permissions", type: "array", items: new OA\Items()),
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
    public function getPermissions()
    {
        Gate::authorize('manage', Permission::class);

        return response()->json([
            'permissions' => Permission::all(),
        ]);
    }

    #[OA\Post(
        path: "/api/v1/permissions/roles",
        summary: "Assign roles to a user",
        tags: ["Permissions"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id", "roles"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer", example: 1),
                    new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "integer"), example: [2]),
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

    public function assignRole(UserRoleRequest $request){
        Gate::authorize('manage', Permission::class);

        $user = User::findOrFail($request->user_id);
        $user->assignRole($request->roles);

        return response()->json([
            'message'=>'Role has been assigned to this user',
            'user'=>$user->load('roles'),
        ]);
    }

    #[OA\Post(
        path: "/api/v1/permissions",
        summary: "Assign permissions to a role",
        tags: ["Permissions"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["role_id", "permissions"],
                properties: [
                    new OA\Property(property: "role_id", type: "integer", example: 1),
                    new OA\Property(property: "permissions", type: "array", items: new OA\Items(type: "integer"), example: [3]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Permission assigned successfully"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function assignPermission(PermissionRequest $request){
        Gate::authorize('manage', Permission::class);

        $role = Role::findOrFail($request->role_id);

        $role->givePermissionTo($request->permissions);

        return response()->json([
            'message'=>'Permission has been given to a role successfully',
            'role'=>$role->load('permissions')
        ]);
    }

        #[OA\Post(
        path: "/api/auth/remove-role",
        summary: "Remove a role from a user",
        tags: ["permission"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id", "role"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer", example: 1),
                    new OA\Property(property: "role", type: "array", items: new OA\Items(type: "integer"), example: [1]),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Role removed from user successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "role removed from user succesfully"),
                        new OA\Property(property: "user", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The user_id field is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "User or role not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User or role not found"),
                    ]
                )
            ),
        ]
    )]
    public function invokeRole(InvokeRequest $request){
        Gate::authorize('manage', Permission::class);
      
        $user = User::findOrFail($request->user_id);

        $user->removeRole($request->role);

        return response()->json([
            'message'=>'role removed from user succesfully',
            'user'=>$user->load('roles')
        ]);

    }



    #[OA\Post(
        path: "/api/auth/remove-permission",
        summary: "Remove a permission from a role",
        tags: ["permission"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["role_id", "permission"],
                properties: [
                    new OA\Property(property: "role_id", type: "integer", example: 1),
                    new OA\Property(property: "permission", type: "array", items: new OA\Items(type: "integer"), example: [1]),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Permission removed from role successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "permission removed from role successfully"),
                        new OA\Property(property: "role", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The role_id field is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Role or permission not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Role or permission not found"),
                    ]
                )
            ),
        ]
    )]
    public function invokePermission(RemovePermitRequest $request){
        Gate::authorize('manage', Permission::class);

        $role = Role::findOrFail($request->role_id);

        $role->revokePermissionTo($request->permission);

        return response()->json([
            'message'=> 'permission removed from role successfully',
            'role'=> $role->load('permissions')
        ]);

    }

     #[OA\Get(
        path: "/api/user-role/{id}",
        summary: "Get user roles and permissions",
        tags: ["permission"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Author id",
                required: true,
                in: "path",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "User roles retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "user", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "The user_id field is required."),
                        new OA\Property(property: "errors", type: "object"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "User or role not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User or role not found"),
                    ]
                )
            ),
        ]
    )]
    public function getUserRole($id){
        Gate::authorize('manage', Permission::class);

        $user = User::with('roles.permissions')->findOrFail($id);

        return response()->json([
            'user'=>$user->load('roles.permissions'),
            'role'=>$user->roles
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('manage', Permission::class);

        $role = Role::findOrFail($id);

        $role->delete();

        return response()->json([
            'message'=> 'role has been deleted successfully',
        ]);
    }
}
