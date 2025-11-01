<?php

namespace Modules\ProductManagment\Models;

use App\Models\BaseModel;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ProductManagment\Database\Factories\CategoryFactory;

class Category extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'description', 'parent_id'];

    protected $table = 'categories';



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


    /**
     * Summary of getNameAttribute
     * @param mixed $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }
    /**
     * Summary of getDescriptionAttribute
     * @param mixed $value
     * @return string
     */
    public function getDescriptionAttribute($value)
    {
        return ucwords($value);
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
     * Summary of setDescriptionAttribute
     * @param mixed $value
     * @return void
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = strtolower($value);
    }

    /**
     * Summary of newFactory
     * @return CategoryFactory
     */
    protected  static function newFactory()
    {
        return new CategoryFactory();
    }
}
