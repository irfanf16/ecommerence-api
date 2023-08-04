<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\FeatureCategoryResource;
use App\Http\Resources\FeaturedSellersResource;
use App\Http\Resources\FeaturedUserStoreResource;
use App\Http\Resources\HomeScreenResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\AppSetting;
use App\Models\Brand;
use App\Models\SubCategory;
use App\Models\WebsiteBanner;
use Illuminate\Http\Request;

use App\Models\Category;
use App\Models\MobileCover;
use App\Models\Product;
use App\Models\Store;
use App\Models\UserStore;

class AppHomeScreenController extends Controller
{
    /*
    |=======================================================================
    | Get Listing of Mobile App Home Screen All Sections
    |=======================================================================
    */
    public function index(Request $request)
    {
        $store_id = $request->store_id;

//        try {
        $covers = WebsiteBanner::where('status', 1)
//            ->select('id', 'image')
            ->inRandomOrder()
            ->get();

        $categories = Category::where('status', 1)
            ->with('subcategories', function ($query) {
                $query->with('childcategories', function ($query) {
                    $query->inRandomOrder()->where('status', 1)->limit(10);
                })->where('status', 1)->limit(8);
            })
            ->select('id', 'title', 'title_ar', 'slug', 'logo_image', 'mobile_image')
            ->take(20)
            ->get();

        $categories = CategoryResource::collection($categories);

        $brands = Brand::select('id', 'name', 'name_ar', 'logo_image', 'slug')
            ->limit(12)
            ->where(['status'=> 1,'featured'=>1])
            ->inRandomOrder()
            ->get();
        $brands = BrandResource::collection($brands);

        $random_banner= WebsiteBanner::where('status', 1)
            ->inRandomOrder()
            ->first();

        $top_featured_subcategories=SubCategory::where(['status'=>1,'featured'=>1])->take(3)->inRandomOrder()->get();

        $top_featured_subcategories=SubCategoryResource::collection($top_featured_subcategories);

        $featured_products = Product::with('firstVariant','image')
            ->whereHas('firstVariant', function ($query) {
                $query->where('quantity', '>=', 1)->where('availability', 1);
            })
            ->select('id', 'name', 'name_ar', 'slug', 'primary_image', 'brand_id', 'likes', 'views', 'sales', 'reports', 'total_reviews', 'avg_rating')
            ->where([
                'status' => 1,
                'featured' => 1
            ])
            ->where('store_id', $store_id)

//                ->whereRelation('store', 'is_verified', '=', 1)->whereRelation('store', 'status', '=', 1)
            ->inRandomOrder()
            ->take(8)
            ->get();

        $featured_products = ProductResource::collection($featured_products);


        $popular_products = Product::with('firstVariant','image')
            ->whereHas('firstVariant', function ($query) {
                $query->where('quantity', '>=', 1)->where('availability', 1);
            })
//                ->whereRelation('store', 'is_verified', '=', 1)->whereRelation('store', 'status', '=', 1)
            ->where('status', 1)
            ->where('store_id', $store_id)
            ->select('id', 'name', 'name_ar', 'slug', 'primary_image', 'brand_id', 'likes', 'views', 'sales', 'reports', 'total_reviews', 'avg_rating')
            ->take(8)
            ->inRandomOrder()
            ->get();

        $popular_products = ProductResource::collection($popular_products);

        $new_added_products = Product::with('firstVariant','image')
            ->whereHas('firstVariant', function ($query) {
                $query->where('quantity', '>=', 1)->where('availability', 1);
            })
            ->select('id', 'name', 'name_ar', 'slug', 'primary_image', 'brand_id', 'likes', 'views', 'sales', 'reports', 'total_reviews', 'avg_rating')
            ->where([
                'status' => 1,
            ])
            ->where('store_id', $store_id)
//                ->whereRelation('store', 'is_verified', '=', 1)->whereRelation('store', 'status', '=', 1)
            ->inRandomOrder()
            ->take(8)
            ->get();
        $new_added_products = ProductResource::collection($new_added_products);

        $new_arrival_products = Product::with('firstVariant','image')
            ->whereHas('firstVariant', function ($query) {
                $query->where('quantity', '>=', 1)->where('availability', 1);
            })
            ->select('id', 'name', 'name_ar', 'slug', 'primary_image', 'brand_id', 'likes', 'views', 'sales', 'reports', 'total_reviews', 'avg_rating')
            ->where([
                'status' => 1,
            ])
            ->where('store_id', $store_id)
//                ->whereRelation('store', 'is_verified', '=', 1)->whereRelation('store', 'status', '=', 1)
            ->inRandomOrder()
            ->take(8)
            ->get();

        $new_arrival_products = ProductResource::collection($new_arrival_products);




        return response()->json([
            'status' => 200,
            'covers' => $covers,
            'categories' => $categories,
            'brands' => $brands,
            'random_banner' => $random_banner,
            'top_featured_subcategories' => $top_featured_subcategories,
            'featured_products' => $featured_products,
            'popular_products' => $popular_products,
            'new_added_products' => $new_added_products,
            'new_arrival_products' => $new_arrival_products,
        ]);

//        } catch (\Throwable $th) {
//
//            //throw $th;
//            return response()->json([
//                "status" => 100,
//                "message" => "Sorry! Something Went Wrong.",
//                "exceptions" => $th
//            ]);
//        }

    }


    /*
    |=============================================================================
    | Get List of All Active Categories with Subcategories
    |=============================================================================
    */
    public function categoriesWithSubcategories()
    {
        try {
            $categories = Category::with('subcategories')
                ->where('status', 1)
                ->orderBy('order', 'asc')
                ->select('id', 'title', 'title_ar', 'slug', 'mobile_image')
                ->inRandomOrder()
                ->with(['subcategories' => function ($query) {
                    $query->withCount('products');
                }])
                ->withCount('products')
                ->get();

            $categories = FeatureCategoryResource::collection($categories);
            return response()->json([
                'status' => 200,
                'categories' => $categories,
            ]);

        } catch (\Throwable $th) {

            //throw $th;
            return response()->json([
                "status" => 100,
                "message" => "Sorry! Something Went Wrong.",
                "exceptions" => $th
            ]);
        }

    }


    /*
    |=============================================================================
    | Get List of All Active Categories with Subcategories
    |=============================================================================
    */
    public function topSellingProducts()
    {
        try {
            $top_selling_products = Product::with('firstVariant:id,price,special_price,product_id')
                ->with('brand:id,name,slug')
                ->where('status', 1)
                ->whereRelation('store', 'is_verified', '=', 1)->whereRelation('store', 'status', '=', 1)
                ->select('id', 'name', 'slug', 'primary_image', 'brand_id', 'likes', 'views', 'sales', 'reports', 'total_reviews', 'avg_rating')
//                ->orderBy('id', 'desc')
                ->inRandomOrder()
                ->paginate(14);

            // dd($categories->toArray());

            return response()->json([
                'status' => 200,
                'top_selling_products' => $top_selling_products,
            ]);

        } catch (\Throwable $th) {

            //throw $th;
            return response()->json([
                "status" => 100,
                "message" => "Sorry! Something Went Wrong.",
                "exceptions" => $th
            ]);
        }
    }

    public function mostSellingProducts()
    {
        try {
            $top_selling_products = Product::with('firstVariant:id,price,special_price,product_id')
                ->with('category')
                ->with('brand:id,name,slug,name_ar')
                ->where('status', 1)
                ->whereRelation('store', 'is_verified', '=', 1)->whereRelation('store', 'status', '=', 1)
                ->select('id', 'name', 'name_ar', 'slug', 'primary_image', 'brand_id', 'likes', 'views', 'sales', 'reports', 'total_reviews', 'avg_rating')
                ->paginate(14);
            $top_selling_products = ProductResource::collection($top_selling_products);
            return response()->json([
                'status' => 200,
                'top_selling_products' => $top_selling_products->response()->getData(),
            ]);

        } catch (\Throwable $th) {

            //throw $th;
            return response()->json([
                "status" => 100,
                "message" => "Sorry! Something Went Wrong.",
                "exceptions" => $th
            ]);
        }
    }

    public function brands(Request $request)
    {
        try {

            $brands = Brand::where('status', 1)->paginate(10);
//            $brands = BrandResource::collection($brands);
            return response()->json([
                'status' => 200,
                'brands' => $brands,
//                'brands' => $brands->response()->getData(),
            ]);

        } catch (\Throwable $th) {

            //throw $th;
            return response()->json([
                "status" => 100,
                "message" => "Sorry! Something Went Wrong.",
                "exceptions" => $th
            ]);
        }
    }


}
