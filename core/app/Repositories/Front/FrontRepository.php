<?php

namespace App\Repositories\Front;

use App\{
    Models\Post,
    Models\Page,
    Models\Order,
};
use App\Helpers\PriceHelper;
use App\Models\Bcategory;
use Illuminate\Support\Facades\Auth;

class FrontRepository
{

    public function displayPosts($request){
        if ($request->filled('category')) {
            $cat = Bcategory::where('slug', $request->category)->first();
            if ($cat) {
                return Post::with('category')->whereCategoryId($cat->id)->latest('id')->paginate(6);
            }
            return Post::with('category')->whereRaw('1 = 0')->paginate(6);
        } else if ($request->filled('search')) {
            $search = $request->search;
            return Post::with('category')->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('details', 'like', '%' . $search . '%');
            })->latest('id')->paginate(6);
        } else if ($request->filled('tag')) {
            return Post::with('category')->where('tags', 'like', '%' . $request->tag . '%')->latest('id')->paginate(6);
        } else {
            return Post::with('category')->latest('id')->paginate(6);
        }
    }

    public function displayPost($slug){
        $tagz = '';
        $tags = null;
        $name = Post::pluck('tags')->toArray();
        foreach($name as $nm)
        {
            $tagz .= $nm.',';
        }
        $tags = array_unique(explode(',',$tagz));
        return [
            'posts'       => Post::orderby('id','desc')->take(4)->get(),
            'post'       => Post::whereSlug($slug)->first(),
            'categories' => Bcategory::withCount('posts')->whereStatus(1)->get(),
            'tags'       => array_filter($tags)
        ];
    }

    public function displayPage($slug){
        return Page::whereSlug($slug)->firstOrFail();
    }

    public function reviewSubmit($request)
    {
        $user = Auth::user();
    
        // Check if the user already has a review for this item
        $existingReview = $user->reviews()->where('item_id', $request->item_id)->first();
    
        if ($existingReview) {
            // Update the existing review
            $existingReview->update([
                'subject' => $request->subject,
                'rating' => $request->rating,
                'review' => $request->review,
                'status' => 1,
            ]);
            return __('Your Review Updated Successfully.');
        }
    
        // Check if the user has purchased the product
        $orders = Order::where('user_id', $user->id)->get();
        $isProductPurchased = false;
    
        foreach ($orders as $order) {
            $cart = json_decode($order->cart, true);
            foreach ($cart as $key => $product) {
                if ($request->item_id == PriceHelper::GetItemId($key)) {
                    $isProductPurchased = true;
                    break 2; // Exit both loops
                }
            }
        }
    
        if (!$isProductPurchased) {
            return [
                'errors' => [
                    0 => __("Buy This Product First"),
                ],
            ];
        }
    
        // Create a new review
        $user->reviews()->create([
            'item_id' => $request->item_id,
            'subject' => $request->subject,
            'rating' => $request->rating,
            'review' => $request->review,
            'status' => 1,
        ]);
        return __('Your Review Submitted Successfully.');
    }
    


}
