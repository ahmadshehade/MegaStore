<?php

namespace Modules\OrderManagement\Models;

use App\Models\Base\BaseModel;
use App\Models\User;
use Database\Factories\DiscountFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;


// use Modules\OrderManagement\Database\Factories\DiscountFactory;

class Discount extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'type',
        'value',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by'
    ];

    protected $casts = [
        'value'       => 'decimal:2',
        'status'      => 'boolean',
        'start_date'  => 'datetime:Y-m-d',
        'end_date'    => 'datetime:Y-m-d',
        'created_by'  => 'integer',
    ];


    /**
     * Summary of getDescriptionAttribute
     * @param mixed $value
     * @return string
     */
    public function getDescriptionAttribute($value)
    {
        return Str::ucwords($value);
    }


    /**
     * Summary of setDescriptionAttribute
     * @param mixed $value
     * @return void
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = strtolower($value);
    }

    /**
     * Summary of getStartDateAttribute
     * @param mixed $value
     * @return string
     */
    public function getStartDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }


    /**
     * Summary of getEndDateAttribute
     * @param mixed $value
     * @return string
     */
    public function getEndDateAttribute($value)
    {
        return Carbon::parse($value)->format('y-m-d');
    }

    /**
     * Summary of creator
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Discount>
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Summary of newFactory
     * @return DiscountFactory
     */
    protected static function newFactory(): DiscountFactory
    {
        return new DiscountFactory();
    }

    /**
     * Summary of orders
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Order, Discount, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function orders()
    {
        return $this->belongsToMany(
            Order::class,
            'order_discounts',
            'discount_id',
            'order_id'
        );
    }

    /**
     * Summary of histories
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OrderDiscountHistory, Discount>
     */
    public function histories(){
        return $this->hasMany(OrderDiscountHistory::class,'discount_id');
    }
}
