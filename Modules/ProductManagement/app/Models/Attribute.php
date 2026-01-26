<?php

namespace Modules\ProductManagement\Models;

use App\Models\Base\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

// use Modules\ProductManagement\Database\Factories\AttributeFactory;

class Attribute extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'meta',
        'scope',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'meta' => 'array'
    ];

    /**
     * Summary of values
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<AttributeValue, Attribute>
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }

    /**
     * Summary of creator
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Attribute>
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Summary of variants
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<ProductVariant, Attribute, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function variants()
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'variant_values',
            'attribute_id',
            'product_variant_id'
        );
    }

    /**
     * Summary of getNameAttribute
     * @param mixed $value
     * @return string
     */
    public function getNameAttribute($value){
        return Str::ucfirst($value);
    }





    // protected static function newFactory(): AttributeFactory
    // {
    //     // return AttributeFactory::new();
    // }
}
