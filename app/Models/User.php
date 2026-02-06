<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enum\UserRoles;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\OrderManagement\Models\Discount;
use Modules\OrderManagement\Models\Order;
use Modules\OrderManagement\Models\ProductReview;
use Modules\PaymentManagement\Models\LedgerEntry;
use Modules\PaymentManagement\Models\Payment;
use Modules\ProductManagement\Models\Attribute;
use Modules\ProductManagement\Models\Product;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Summary of getCreatedAtAttribute
     * @param mixed $value
     * @return string
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format("y-m-d");
    }


    /**
     * Summary of getUpdatedAtAttribute
     * @param mixed $value
     * @return string
     */
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format("y-m-d");
    }

    /**
     * Summary of products
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Product, User>
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    /**
     * Summary of attributes
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Attribute, User>
     */
    public function attributes()
    {
        return $this->hasMany(Attribute::class, 'created_by');
    }

    /**
     * Summary of discounts
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Discount, User>
     */
    public function discounts()
    {
        return $this->hasMany(Discount::class, 'created_by');
    }

    /**
     * Summary of orders
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Order, User>
     */
    public function orders()
    {
        return  $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Summary of payments
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Payment, User>
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'customer_id');
    }

    /**
     * Summary of ledgerEntries
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LedgerEntry, User>
     */
    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class, 'customer_id');
    }

    /**
     * Summary of scopeAdmins
     * @param mixed $query
     */
    public function scopeAdmins($query)
    {
        return $query->whereHas(
            'roles',
            fn($q) =>
            $q->where('name', UserRoles::SuperAdmin->value)
        );
    }

    /**
     * Summary of productReviews
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductReview, User>
     */
    public function productReviews()
    {
        return $this->hasMany(ProductReview::class);
    }
}
