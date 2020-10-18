<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PrProduct
 * 
 * @property int $id
 * @property int $category_id
 * @property int $owner_id
 * @property string $product_name
 * @property string $short_desc
 * @property string $long_desc
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property PrCategory $pr_category
 * @property Collection|PrProductInfo[] $pr_product_infos
 *
 * @package App\Models
 */
class PrProduct extends Model
{
	protected $table = 'pr_product';

	protected $casts = [
		'category_id' => 'int',
		'owner_id' => 'int'
	];

	protected $fillable = [
		'category_id',
		'owner_id',
		'product_name',
		'short_desc',
		'long_desc'
	];

	public function pr_category()
	{
		return $this->belongsTo(PrCategory::class, 'category_id');
	}

	public function pr_product_infos()
	{
		return $this->hasMany(PrProductInfo::class, 'product_id');
	}
}
