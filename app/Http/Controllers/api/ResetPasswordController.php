<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    //
    /**
     * @OA\POST(
     *     path="/api/forgot-password",
     *     tags={"Users"},
     *     summary="Generate Password Reset Token",
     *     operationId="forgotPassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     description="User Email",
     *                     type="string",
     *                     example="john@example.com"
     *                 ),
     *                 required={"email"}
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

    public function forgotPassword(Request $request) {

        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return $this->failedResponse(['error' => $validator->errors()->all()], 'Validation issue', 403);
            }

            $user = User::where('email', $request->email)->first();

            if($user) {

                $resetToken = PasswordResetToken::where('email', $request->email)->first();

                if($resetToken) {
                    DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                }

                $token = Str::random(50);
                $created_at = now();
                PasswordResetToken::create(
                    [
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => $created_at
                    ]
                );

                $user->update(['passcode' => $token]);

                $this->send($token, $request->email);
                return $this->successResponse($token, 'Password reset mail sent successfully', 200);
            } else {

                return $this->failedResponse([], 'User not found', 200);
            }
        } catch (Exception $e) {

            return $this->failedResponse([], $e->getMessage(), 200);
        }

    }

    private function send($token, $email) {
        Mail::to($email)->send(new ResetPasswordMail($token));
    }

    public function resetPasswordChecker(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->failedResponse(['error' => $validator->errors()->all()], 'Validation issue', 403);
            }

            $token_info = PasswordResetToken::where('token', $request->token)->first();
            // dd($token_info->email);
            if ($token_info && (strcmp($token_info->token, $request->token) == 0)) {

                $t_limit = strtotime(date('Y-m-d H:i:s', strtotime($token_info->created_at . '+ 10 minutes')));
                $curr_datetime = strtotime(Carbon::now()->format('Y-m-d H:i:s'));

                if ($t_limit < $curr_datetime) {
                    DB::table('password_reset_tokens')->where('token', $request->token)->delete();
                    User::where('passcode', $request->token)->first()->update(['passcode' => NULL]);
                    return redirect('http://localhost:4200/token-denied')->with(['message' => 'Token Expired', 'status' => 200]);
                } else {
                    return redirect('http://localhost:4200/reset-password')->with(['data' => ['email' => $token_info->email], 'message' => 'Token exists', 'status' => 200]);
                }
            } else {
                return redirect('http://localhost:4200/token-denied')->with(['message' => 'Unauthorixed Access', 'status' => 200]);
            }
        } catch (Exception $e) {
            return $this->failedResponse([], $e->getMessage(), 200);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/reset-password",
     *     tags={"Users"},
     *     summary="Reset the Password",
     *     operationId="resetPassword",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="token",
     *                     description="Token",
     *                     type="string",
     *                     example=""
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="User password",
     *                     type="string",
     *                     example=""
     *                 ),
     *                 @OA\Property(
     *                    property="password_confirmation",
     *                    description="Confirm Password",
     *                    type="string",
     *                    example=""
     *                 ),
     *                 required={"token", "password", "password_confirmation"}
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

    public function resetPassword(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'password' => 'required|between:8,16|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->failedResponse(['error' => $validator->errors()->all()], 'Validation issue', 403);
            }

            $temp = $this->resetPasswordChecker($request);

            if ($temp->isSuccessful()) {

                $token_info = PasswordResetToken::where('token', $request->token)->first();

                $user = User::where('passcode', $token_info->token)->first();

                if($user) {
                    $user->update(['password' => $request->password, 'passcode' => NULL]);
                    DB::table('password_reset_tokens')->where('token', $request->token)->delete();

                    return $this->successResponse([], 'Password changed successfully', 200);
                } else {
                    return $this->failedResponse([], 'Data not found', 200);
                }
            } else {
                return $this->failedResponse([], 'Unauthorized', 401);
            }
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
