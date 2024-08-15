<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\role;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class authController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/registerUser",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully created User",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created User"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="johndoe@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create User",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create User: Error message")
     *         )
     *     )
     * )
     */
    public function registerUser(Request $request)
    {
        try {
                $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required',
                ]);

            $data = [
                'name' => $request->name,
                'email' => $request->email,

                'password' => Hash::make($request->password),
            ];

            $response = User::create($data);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Successfully create User',
                    'data' => $response,
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('Failed to create Product: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Failed to create User: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/loginUser",
     *     summary="Login a user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged in User",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully logged in User"),
     *             @OA\Property(property="token", type="string", example="token_value")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized: Email and password not match or validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="email and password not match")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to login User",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to login User: Error message")
     *         )
     *     )
     * )
     */
    public function loginUser(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email',
                'password' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Fail Process Login',
                        'data' => $validator->errors(),
                    ],
                    401,
                );
            }

            if (!Auth::attempt($request->only(['email', 'password']))) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'email and password not match',
                    ],
                    401,
                );
            }

            $data = User::where('email', $request->email)->first();
            $role = role::join('user_role', 'user_role.role_id', '=', 'roles.id')
                ->join('users', 'users.id', '=', 'user_role.user_id')
                ->where('user_id', $data->id)
                ->pluck('roles.role_name')
                ->toArray();
            if (empty($role)) {
                $role = ['user'];
            }
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Successfully login User',
                    'token' => $data->createToken('api-product', $role)->plainTextToken,
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('Failed to create Product: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Failed to create User: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout a user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to log out User",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to log out User: Error message")
     *         )
     *     )
     * )
     */
    public function logoutUser(Request $request)
    {
        try {
            $user = $request->user();

            $user->currentAccessToken()->delete();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Successfully logged out',
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('Failed to log out User: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Failed to log out User: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

}
