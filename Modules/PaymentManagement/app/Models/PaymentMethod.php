<?php

namespace Modules\PaymentManagement\Models;

use App\Models\Base\BaseModel;
use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class PaymentMethod extends BaseModel
{
    use HasFactory;

    /**
     * Summary of table
     * @var string
     */
    protected $table = "payment_methods";

    /**
     * Summary of fillable
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'fee',
        'security_features',
        'integration_details',
        'is_active'
    ];
    /**
     * Summary of casts
     * @var array
     */
    protected $casts = [
        'fee' => 'decimal:2',
        'security_features' => 'array',
        'integration_details' => 'array',
        'is_active' => 'boolean'
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

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = strtolower($value);
    }

    /**
     * Summary of getNameAttribute
     * @param mixed $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        return Str::ucwords($value);
    }

    /**
     * Summary of setNameAttribute
     * @param mixed $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    /**
     * Summary of newFactory
     * @return PaymentMethodFactory
     */
    protected static  function newFactory()
    {
        return  new PaymentMethodFactory();
    }

    /**
     * Summary of payments
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Payment, PaymentMethod>
     */
    public function  payments()
    {
        return $this->hasMany(Payment::class, 'payment_method_id');
    }
}
