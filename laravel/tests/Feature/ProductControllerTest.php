<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user dan token
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('TestToken')->plainTextToken;

        // Sertakan header Authorization di setiap request
        $this->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    /** @test */
    public function it_can_list_products()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer ' . $token);
        $products = Product::factory()->count(13)->create();
        $response = $this->getJson('/api/products');
        $response->assertStatus(200)->assertJson([
            'status' => true,
            'message' => 'Successfully get products',
        ]);

        $responseData = $response->json();
        $this->assertEquals($products->count(), count($responseData['data']));
    }

    /** @test */
    public function it_can_store_a_product()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token);

        Storage::fake('public');

        $image = UploadedFile::fake()->image('product.jpg');

        $data = [
            'name' => 'Test Product',
            'description' => 'This is a test product.',
            'price' => 99.99,
            'image' => $image,
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(200)->assertJson([
            'status' => true,
            'message' => 'Successfully create Product',
        ]);

        if (!Storage::disk('public')->exists('productImage/' . $data['image']->hashName())) {
            dump(Storage::disk('public')->allFiles());
        }

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'This is a test product.',
            'price' => 99.99,
            'image' => $image->hashName(),
        ]);
    }

    public function test_it_can_show_a_product()
    {
        // Buat produk
        $product = Product::factory()->create();

        // Kirim permintaan GET untuk melihat detail produk
        $response = $this->getJson('/api/products/' . $product->id);

        // Asseri bahwa status respons adalah 200 (OK)
        $response->assertStatus(200)->assertJson([
            'status' => true,
            'message' => 'Successfully get detail product',
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
            ],
        ]);
    }

    /** @test */
    public function it_can_update_a_product()
    {
        Storage::fake('public');

        $product = Product::factory()->create();
        $newImage = UploadedFile::fake()->image('new-product.jpg');

        $data = [
            'name' => 'Updated Product Name',
            'description' => 'Updated Product Description',
            'price' => 199.99,
            'image' => $newImage,
        ];

        $response = $this->patchJson('/api/products/' . $product->id, $data);

        $response->assertStatus(200)->assertJson([
            'status' => true,
            'message' => 'Successfully updated Product',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'description' => 'Updated Product Description',
            'price' => 199.99,
            'image' => $newImage->hashName(),
        ]);

        if (!Storage::disk('public')->exists('productImage/' . $data['image']->hashName())) {
            dump(Storage::disk('public')->allFiles());
        }
        Storage::disk('public')->assertMissing('productImage/' . $product->image); // Pastikan gambar lama dihapus
    }

    /** @test */
    public function it_can_delete_a_product()
    {
        Storage::fake('public');

        $product = Product::factory()->create([
            'image' => 'product-image.jpg',
        ]);

        Storage::disk('public')->put('productImage/' . $product->image, 'dummy content');

        $response = $this->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200)->assertJson([
            'status' => true,
            'message' => 'Successfully delete product',
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
