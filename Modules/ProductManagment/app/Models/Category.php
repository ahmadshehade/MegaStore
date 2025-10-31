<?php

namespace Modules\ProductManagment\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ProductManagment\Database\Factories\CategoryFactory;

class Category extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name','description','parent_id'];

    protected  $table='categories';



    /**
     * Summary of parent
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category, Category>
     */
     public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Summary of children
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Category, Category>
     */
     public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }


   

    // protected static function newFactory(): CategoryFactory
    // {
    //     // return CategoryFactory::new();
    // }
}
