<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class productController extends Controller
{
    // Menampilkan semua produk
    public function index(Request $request)
    {
        $search = $request->input('search');
        $products = Product::when($search, function ($query, $search) {
            return $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('price', 'like', '%' . $search . '%');
            });
        })
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $roles = role::join('user_role', 'user_role.role_id', '=', 'roles.id')->join('users', 'users.id', '=', 'user_role.user_id')->where('users.id', auth()->id())->pluck('roles.role_name')->toArray();

        return view('pages.product.index', compact('products', 'roles', 'search'));
    }

    // Menampilkan form untuk membuat produk baru
    public function create()
    {
        return view('pages.product.create');
    }

    // Menyimpan produk baru ke database
    public function store(Request $request)
    {
        $rules = [
            'image' => 'nullable|image',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
        ];

        $validate = Validator::make($request->all(), $rules);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
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

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product successfully created');
    }

    // Menampilkan detail produk
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('pages.product.show', compact('product'));
    }

    // Menampilkan form untuk mengedit produk
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return view('pages.product.edit', compact('product'));
    }

    // Memperbarui produk yang ada
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $rules = [
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric',
            'image' => 'nullable|image',
        ];

        $validate = Validator::make($request->all(), $rules);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
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

        return redirect()->route('products.index')->with('success', 'Product successfully updated');
    }

    // Menghapus produk
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            Storage::delete('public/productImage/' . $product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product successfully deleted');
    }
}
