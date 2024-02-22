<?php

namespace App\Http\Controllers\api;

use App\Models\Adaptation;
use App\Models\AdaptationKey;
use App\Http\Controllers\Controller;
use App\Models\AdaptationKeyI18n;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;

class AdaptationKeyController extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/adaptations",
     *     tags={"Adaptations"},
     *     summary="All the adaptations.",
     *     operationId="getAllAdaptations",
     *     security={{"bearer_token":{}}},
     *
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

    public function getAllAdaptations()
    {

        $adaptations = Adaptation::all();
        if ($adaptations->count() > 0) {
            return $this->successResponse($adaptations, 'Adaptations retrieved successfully', 200);
        }

        return $this->failedResponse([], 'Data Not Found', 200);
    }

    /**
     * @OA\GET(
     *     path="/api/keys",
     *     tags={"Adaptations"},
     *     summary="All the keys related to the adaptation.",
     *     operationId="getAllKeys",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="adaptation_id",
     *         in="query",
     *         description="Adaptation ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
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

    public function getAllKeys(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'adaptation_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->failedResponse(['error' => $validator->errors()->all()], 'Validation issue', 403);
        }

        if ($request->lang) {
            App::setLocale($request->lang);
        }

        $keys = AdaptationKey::where([
            'parent_id' => null,
            'adaptation_id' => $request->adaptation_id
        ])->with('children')->get();
        
        $keys->transform(function ($key) {
            $relatedKeys = $key->relatedKeys();
            $key->related_keys = $relatedKeys;
            return $key;
        });

        if ($keys->count() > 0) {
            return $this->successResponse($keys, 'Keys retrieved successfully', 200);
        }

        return $this->failedResponse([], 'Data Not Found', 200);
    }

    /**
     * @OA\POST(
     *     path="/api/create",
     *     tags={"Adaptations"},
     *     summary="Create a key in an adaptation.",
     *     operationId="createKey",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="adaptation_id",
     *                     description="Adaptation ID",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="parent_id",
     *                     description="Parent ID",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     description="Title of the key",
     *                     type="string",
     *                     example="title"
     *                 ),
     *                 @OA\Property(
     *                     property="lang",
     *                     description="Language",
     *                     type="string",
     *                     example="en"
     *                 ),
     *                 required={"adaptation_id", "title", "parent_id"}
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
    public function createKey(Request $request)
    {

        if ($request->lang) {
            App::setLocale($request->lang);
        }

        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'adaptation_id' => 'required',
                'parent_id' => 'required'
            ]);

            if ($validator->fails()) {

                return $this->failedResponse(['error' => $validator->errors()->all()], 'Validation issue', 403);
            }

            $adp_key = AdaptationKey::create([
                'title' => $request->title,
                'adaptation_id' => $request->adaptation_id,
                'parent_id' => $request->parent_id
            ]);

            return $this->successResponse(['key_details' => $adp_key], 'Key created successfully', 200);
        } catch (\Exception $e) {
            return $this->failedResponse([], $e->getMessage(), 200);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/update",
     *     tags={"Adaptations"},
     *     summary="Update the data of a key.",
     *     operationId="updateKey",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     description="Key ID",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="key",
     *                     description="Unique key",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="code",
     *                     description="Code of the key",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     description="Title of the key",
     *                     type="string",
     *                     example="Dashboards"
     *                 ),
     *                 @OA\Property(
     *                     property="desc",
     *                     description="Description of the key",
     *                     type="string",
     *                     example="Description"
     *                 ),
     *                 @OA\Property(
     *                     property="purpose",
     *                     description="Purpose of the key",
     *                     type="string",
     *                     example="Purpose"
     *                 ),
     *                 @OA\Property(
     *                     property="lang",
     *                     description="Language",
     *                     type="string",
     *                     example="en"
     *                 ),
     *                 required={"id", "key", "code", "title", "desc", "purpose"}
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
    public function updateKey(Request $request)
    {
        //
        if ($request->lang) {
            App::setLocale($request->lang);
        }

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                // 'adaptation_id' => 'required',
                'key' => 'required',
                'code' => 'required|max:10',
                'title' => 'required',
                'desc' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->failedResponse(['error' => $validator->errors()->all()], 'Validation issue', 403);
            }

            $adaptation_key = AdaptationKey::where('key', $request->key)->first();

            // if key already exists in DB
            if($adaptation_key) {
                if($request->id == $adaptation_key->id) {
                    $relatedKeys = str_replace(' ', '', $request->related_keys);
                    $adaptation_key->update([
                        // 'adaptation_id' => $request->adaptation_id,
                        'key' => $request->key,
                        'code' => $request->code,
                        'title' => $request->title,
                        'desc' => $request->desc,
                        'purpose' => $request->purpose,
                        'related_keys' => $relatedKeys
                        // 'parent_id' => $request->parent_id
                    ]);

                    return $this->successResponse(['key_details' => $adaptation_key], 'Key updated successfully', 200);
                } else  {
                    return $this->failedResponse(['error' => 'Key already exists. Choose a different one.'], 'Validation issue', 403);
                }
            } else  {
                $adaptation_key = AdaptationKey::where('id', $request->id)->first();

                $relatedKeys = str_replace(' ', '', $request->related_keys);

                $adaptation_key->update([
                    // 'adaptation_id' => $request->adaptation_id,
                    'key' => $request->key,
                    'code' => $request->code,
                    'title' => $request->title,
                    'desc' => $request->desc,
                    'purpose' => $request->purpose,
                    'related_keys' => $relatedKeys
                    // 'parent_id' => $request->parent_id
                ]);

                return $this->successResponse(['key_details' => $adaptation_key], 'Key updated successfully', 200);
            }
        } catch (\Exception $e) {
            return $this->failedResponse([], $e->getMessage(), 200);
        }
    }


    /**
     * @OA\GET(
     *     path="/api/keydata/{key}",
     *     tags={"Adaptations"},
     *     summary="All the details related to the key.",
     *     operationId="getKeyData",
     *     security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         description="Key ID (can be integer or string)",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
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
    public function getKeyData($key)
    {
        try {
            $adp_key = AdaptationKey::where('key', $key)->orWhere('id', $key)->first();

            if ($adp_key) {

                $adp_key->makeVisible(['translations']);

                $relatedKeys = $adp_key->relatedKeys();
                $parentKey = $adp_key->parent ? $adp_key->parent->key : null;

                $adp_key->related_keys = $relatedKeys;
                $adp_key->parentKey = $parentKey;

                // dd($adp_key);
                return $this->successResponse($adp_key, 'Key Data retrieved successfully', 200);
            }
            return $this->failedResponse([], 'Data Not Found', 200);
        } catch (\Exception $e) {
            return $this->failedResponse([], $e->getMessage(), 200);
        }
    }

    /**
     * @OA\PUT(
     *     path="/api/delete",
     *     tags={"Adaptations"},
     *     summary="Delete a key.",
     *     operationId="deleteKey",
     *     security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *            @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="id",
     *                     description="Key ID",
     *                     type="integer"
     *                 ),
     *                 required={"key"}
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
    public function deleteKey(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->failedResponse(['error' => $validator->errors()->all()], 'Validation issue', 403);
            }

            $adp_key = AdaptationKey::where('id', $request->id)->first();

            if ($adp_key) {

                $adp_key->delete();
                return $this->successResponse(['key_details' => $adp_key], 'Key deleted successfully', 200);
            }
            return $this->failedResponse([], 'Data Not Found', 200);

        } catch (\Exception $e) {
            return $this->failedResponse([], $e->getMessage(), 200);
        }
    }

    private function failedResponse($error, $message, $status)
    {
        $response = [
            'success' => false,
            'data' => $error,
            'message' => $message,
            'status' => $status,
        ];

        return response()->json($response, $status);
    }

    private function successResponse($data, $message, $status)
    {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
            'status' => $status,
        ];

        return response($response, $status);
    }
}
