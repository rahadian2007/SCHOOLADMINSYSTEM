<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Product;
use App\Models\Settings;
use App\Models\ProductCategory;
use App\Models\ProductVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(10);

        // Settings for default commissions
        $commissionPercent = Settings::where('key', 'commission_percent')->first();

        $data = compact('products', 'commissionPercent');

        return view('products.index', $data);
    }

    public function show($id)
    {
        $product = Product::find($id);
        $data = compact('product');

        return view('products.detail', $data);
    }

    public function create()
    {
        $product = new Product();
        $product->base_price = 0;
        $product->selling_price = 0;
        $productCategories = ProductCategory::pluck('name', 'id');
        $productVendors = ProductVendor::pluck('name', 'id');

        return view('products.form', compact('product', 'productCategories', 'productVendors'));
    }

    public function store()
    {
        $resourceId = $this->handleImageUpload();
        $payload = request()->except('_token');
        $payload['feat_product_img_url'] = $resourceId;

        Product::create($payload);

        return redirect('/products')
            ->with('message', 'Berhasil menambahkan produk baru');
    }

    public function edit(Product $product)
    {
        $productCategories = ProductCategory::pluck('name', 'id');
        $productVendors = ProductVendor::pluck('name', 'id');

        return view('products.form', compact('product', 'productCategories', 'productVendors'));
    }

    public function update(Product $product)
    {
        $resourceId = $this->handleImageUpload();
        $payload = request()->except('_token');

        if ($resourceId) {
            $payload['feat_product_img_url'] = $resourceId;
        }

        if (!$payload['commission_percent']) {
            $payload['commission_percent'] = null;
        }

        $product->update($payload);

        return redirect('/products')
            ->with('message', 'Berhasil mengubah info produk');
    }

    private function handleImageUpload()
    {
        $resourceId = null;

        if (request()->hasFile('img')) {
            $file = request()->file('img');
            $path = $file->path();
            $originalName = $file->getClientOriginalName();
            $mediaFolder = Folder::where('resource', '=', 1)->first();

            if (!empty($mediaFolder)) {
                $mediaFolder->addMedia($path)
                    ->usingFileName(date('YmdHis') . $originalName)
                    ->usingName($originalName)
                    ->toMediaCollection();
                $resourceId = DB::getPdo()->lastInsertId(); 
            }
        }

        return $resourceId;
    }
}
