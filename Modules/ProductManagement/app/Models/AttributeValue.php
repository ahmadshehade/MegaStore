<?php

namespace Modules\ProductManagement\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ProductManagement\Database\Factories\AttributeValueFactory;

class AttributeValue extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'attribute_id',
        'value',
        'label',
    ];

    /**
     * Summary of attribute
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Attribute, AttributeValue>
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }

    // protected static function newFactory(): AttributeValueFactory
    // {
    //     // return AttributeValueFactory::new();
    // }
}
