<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PrCategory
 * 
 * @property int $id
 * @property int|null $super_cat_id
 * @property string $category_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property PrCategory $pr_category
 * @property Collection|PrCategory[] $pr_categories
 * @property Collection|PrProduct[] $pr_products
 *
 * @package App\Models
 */
class PrCategory extends Model
{
	protected $table = 'pr_category';

	protected $casts = [
		'super_cat_id' => 'int'
	];

	protected $fillable = [
		'super_cat_id',
		'category_name'
	];

	public function pr_category()
	{
		return $this->belongsTo(PrCategory::class, 'super_cat_id');
	}

	public function pr_categories()
	{
		return $this->hasMany(PrCategory::class, 'super_cat_id');
	}

	public function pr_products()
	{
		return $this->hasMany(PrProduct::class, 'category_id');
	}
}
