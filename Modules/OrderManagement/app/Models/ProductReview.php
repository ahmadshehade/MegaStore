<?php

namespace Modules\OrderManagement\Models;

use App\Enum\UserRoles;
use App\Models\Base\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductManagement\Models\Product;

// use Modules\OrderManagement\Database\Factories\ProductReviewFactory;

class ProductReview extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'comment',
        'is_approved',
    ];

    /**
     * Summary of product
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, ProductReview>
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Summary of user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, ProductReview>
     */
    public  function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Summary of order
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order, ProductReview>
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }


    /**
     * Summary of scopeVisibleFor
     * @param mixed $query
     * @param mixed $user
     */
    public  function scopeVisibleFor($query, $user)
    {
        if ($user->hasRole(UserRoles::SuperAdmin->value)) {
            return $query;
        }
        if ($user->hasRole(UserRoles::Seller->value)) {
            return  $query->whereHas('product', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }
        if($user->hasRole(UserRoles::Customer->value)){
            return $query->where('user_id',$user->id);
        }
         return $query->whereRaw('0 = 1');
    }

    // protected static function newFactory(): ProductReviewFactory
    // {
    //     // return ProductReviewFactory::new();
    // }
}
