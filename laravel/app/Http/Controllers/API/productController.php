<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="API Documentation",
 *         version="1.0.0",
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT"
 *         )
 *     )
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get all products",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="total_data", type="integer", example=10),
     *             @OA\Property(property="message", type="string", example="Successfully get products"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Product Name"),
     *                     @OA\Property(property="price", type="number", format="float", example=99.99),
     *                     @OA\Property(property="description", type="string", example="Product Description")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve products",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed get Product: Error message")
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $data = Product::orderBy('name', 'asc')->get();
            $total = $data->count();
            return response()->json(
                [
                    'status' => true,
                    'total data' => $total,
                    'message' => 'Successfully get products',
                    'data' => $data,
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('Failed get Product: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Failed get Product: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

        /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "price", "image"},
     *                 @OA\Property(property="name", type="string", example="Product Name"),
     *                 @OA\Property(property="description", type="string", example="Product Description"),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Product image file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully created Product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created Product"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Product Name"),
     *                 @OA\Property(property="description", type="string", example="Product Description"),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="image", type="string", example="image_name.jpg"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create Product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create Product: Error message")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        try {
            $rules = [
                'image' => 'nullable|image',
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
            ];

            $validate = Validator::make($request->all(), $rules);

            if ($validate->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Fail Create Data Product',
                        'errors' => $validate->errors(),
                    ],
                    400,
                );
            }

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
            ];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $image->storeAs('public/productImage', $image->hashName());
                $data['image'] = $image->hashName();
            }

            $response = Product::create($data);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Successfully create Product',
                    'data' => $response,
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('Failed to create Product: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Failed to create Product: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

        /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get a product by ID",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Product ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully get detail product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully get detail product"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Product Name"),
     *                 @OA\Property(property="description", type="string", example="Product Description"),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="image", type="string", example="image_name.jpg"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to get detail Product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to get detail Product: Error message")
     *         )
     *     )
     * )
     */

    public function show(string $id)
    {
        try {
            $data = Product::findOrFail($id);
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Successfully get detail product',
                    'data' => $data,
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('Failed to get Product: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Failed to get detail Product: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

        /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update a product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Product ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Updated Product Name"),
     *                 @OA\Property(property="description", type="string", example="Updated Product Description"),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Product image file"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated Product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully updated Product"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Product Name"),
     *                 @OA\Property(property="description", type="string", example="Updated Product Description"),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="image", type="string", example="updated_image_name.jpg"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update Product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update Product: Error message")
     *         )
     *     )
     * )
     */

    public function update(Request $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $rules = [
                'name' => 'string|max:255',
                'description' => 'string',
                'price' => 'numeric',
                'image' => 'nullable|image',
            ];

            $validate = Validator::make($request->all(), $rules);

            if ($validate->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Fail Update Data Product',
                        'errors' => $validate->errors(),
                    ],
                    400,
                );
            }

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
            ];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $image->storeAs('public/productImage', $image->hashName());

                if ($product->image) {
                    Storage::delete('public/productImage/' . $product->image);
                }
                $data['image'] = $image->hashName();
            }

            $product->update($data);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Successfully updated Product',
                    'data' => $product,
                ],
                200,
            );
        } catch (\Exception $e) {
            Log::error('Failed to update Product: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Failed to update Product: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

        /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     tags={"Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Product ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully delete product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully delete product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete Product",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete Product: Error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $data = Product::findOrFail($id);
        if ($data->image) {
            Storage::delete('public/productImage/' . $data->image);
        }
        $data->delete();
        return response()->json(
            [
                'status' => true,
                'message' => 'Successfully delete product',
            ],
            200
        );
        } catch (\Exception $e) {
            Log::error('Failed to get Product: ' . $e->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Failed to delete Product: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }
}
