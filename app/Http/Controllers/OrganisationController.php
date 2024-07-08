<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class OrganisationController extends \Illuminate\Routing\Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getUser(Request $request, $id)
    {
        $user = Auth::user(); // Get the authenticated user

        // Check if the authenticated user has access to the user record
        if ($user->userId != $id && !$user->organisations()->where('user_id', $id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied',
            ], 403);
        }

        // Retrieve user data
        $userData = User::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved successfully',
            'data' => $userData,
        ], 200);
    }

    public function getOrganisations()
    {
        $user = Auth::user(); // Get the authenticated user
        $organisations = $user->organisations; // Retrieve the organisations of the user

        return response()->json([
            'status' => 'success',
            'message' => 'Organisations retrieved successfully',
            'data' => [
                'organisations' => $organisations,
            ],
        ], 200);
    }

    public function getOrganisation($orgId)
    {
        $user = Auth::user(); // Get the authenticated user
        $organisation = $user->organisations()->where('orgId', $orgId)->firstOrFail(); // Retrieve the specific organisation

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation retrieved successfully',
            'data' => $organisation,
        ], 200);
    }

    public function createOrganisation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client error',
                'statusCode' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = Auth::user(); // Get the authenticated user

        $organisation = Organisation::create($validator->validated());
        $user->organisations()->attach($organisation->orgId); // Attach the created organisation to the user

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation created successfully',
            'data' => $organisation,
        ], 201);
    }

    public function addUserToOrganisation(Request $request, $orgId)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:users,userId',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Client error',
                'statusCode' => 400,
                'errors' => $validator->errors(),
            ], 400);
        }

        $organisation = Organisation::findOrFail($orgId);
        $organisation->users()->attach($request->userId); // Add user to the organisation

        return response()->json([
            'status' => 'success',
            'message' => 'User added to organisation successfully',
        ], 200);
    }
}
