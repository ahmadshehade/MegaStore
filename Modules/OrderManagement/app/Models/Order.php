<?php

namespace Modules\OrderManagement\Models;

use App\Enum\UserRoles;
use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentManagement\Models\PaymentMethod;
use Modules\ProductManagment\Models\Product;

// use Modules\OrderManagement\Database\Factories\OrderFactory;

class Order extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'payment_method_id', 'status', 'tot_amount', 'notes', 'shipping_id'];

    // protected static function newFactory(): OrderFactory
    // {
    //     // return OrderFactory::new();
    // }

    /**
     * Summary of user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Order>
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Summary of shipping
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Shipping, Order>
     */
    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_id');
    }

    /**
     * Summary of paymetMethod
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<PaymentMethod, Order>
     */
    public function paymetMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }


    /**
     * Summary of getNoteAttribute
     * @param mixed $value
     * @return string
     */
    public function getNoteAttribute($value)
    {
        return ucwords($value);
    }

    /**
     * Summary of setNoteAttribute
     * @param mixed $value
     * @return void
     */
    public function setNoteAttribute($value)
    {
        $this->attributes['note'] = strtolower($value);
    }


    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }


    /**
     * Summary of booted
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('roleOrdersScope', function (Builder $builder) {
            if (!Auth::check()) {
                return;
            }
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->hasRole(UserRoles::SuperAdmin->value)) {
                return;
            }
            if ($user->hasRole(UserRoles::Customer->value)) {
                $builder->where('user_id', $user->id);
                return;
            }
            if ($user->hasRole(UserRoles::Seller->value)) {
                $builder->whereHas('items.product', function (Builder $q) use ($user) {
                    $q->where('seller_id', $user->id);
                });
                return;
            }
        });
    }
}
