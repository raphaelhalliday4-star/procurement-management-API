<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Http\Requests\UserRoleRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
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
            new OA\Response(response: 200, description: "Role assigned successfully"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validation error"),
        ]
    )]
    public function assignRole(UserRoleRequest $request){
        Gate::authorize('permissions.manage');
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
        Gate::authorize('permissions.manage');
        $role = Role::findOrFail($request->role_id);

        $role->givePermissionTo($request->permissions);

        return response()->json([
            'message'=>'Permission has been given to a role successfully',
            'role'=>$role->load('permissions')
        ]);
    }
}
