<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    //
    /**
     * @OA\POST(
     *     path="/api/login",
     *     tags={"Users"},
     *     summary="Login",
     *     operationId="login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="username",
     *                     description="Username",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="User password",
     *                     type="string",
     *                 ),
     *                 required={"username", "password"}
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *          @OA\MediaType(
     *             mediaType="application/json"
     *          )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */


    public function login(Request $request) {

        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required'
            ]);

            if ($validator->fails()) {

                return $this->failedResponse(['error' => $validator->errors()->all()], 'Validation issue', 403);
            }

            if(!$token = auth()->attempt($validator->validated())) {
                return $this->failedResponse(['error' => 'Unauthorized'], 'Invalid Username\Password', 401);
            }


            // Updating the token on the DB
            $user = User::where('username', $request->username)->first();
            $user->update(['access_token' => $token]);

            return $this->successResponse(['token' => $token], 'User logged in successfully', 200);

        } catch (Exception $e) {
            return $this->failedResponse([], $e->getMessage(), 200);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/user/settings/change-password",
     *     tags={"Users"},
     *     summary="Password Change",
     *     operationId="changePassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="password",
     *                     description="Password",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     description="Confirm password",
     *                     type="string"
     *                 ),
     *                 required={"password", "password_confirmation"}
     *             )
     *         ),
     *     ),
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *          @OA\MediaType(
     *             mediaType="application/json"
     *          )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

    public function changePassword(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'password' => 'required|between:8,16|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse(['error' => $validator->errors()->all()], 'Validation issue', 403);
            }

            // only token is used
            $user = User::where('username', auth()->user()->username)->first();
            $user->update(['password' => Hash::make($request->password)]);

            return $this->successResponse('[]', 'Password Changed Successfully', 200);
        } catch (Exception $e) {
            return $this->failedResponse([], $e->getMessage(), 200);
        }
    }

    public function refreshToken(Request $request) {

        $user = auth()->user();
            $new_token = auth()->refresh();
            $user->update(['access_token' => $new_token]);
            return $this->successResponse(['new_token' => $new_token], 'Token refreshed successfully', 200);
    }

    /**
     * @OA\POST(
     *     path="/api/logout",
     *     tags={"Users"},
     *     summary="Logout",
     *     description="User logout",
     *     operationId="logout",
     *     security={{"bearer_token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

    public function logout(Request $request) {

        try {
            auth()->logout();
            return $this->successResponse([], 'You have been successfully logged out!', 200);

        } catch (Exception $e) {
            return $this->failedResponse([], $e->getMessage(), 200);
        }
    }

    private function failedResponse($error, $message, $status) {
        $response = [
            'success' => false,
            'data' => $error,
            'message' => $message,
            'status' => $status,
        ];

        return response()->json($response, $status);
    }

    private function successResponse($data, $message, $status) {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
            'status' => $status,
        ];

        return response($response, $status);
    }

    public function unauthorized() {
        return response()->json(
            [
                'success' => false,
                'data' => [],
                'message' => 'User Not Authorized',
                'status' => 401,
            ],
            401
        );
    }
}
