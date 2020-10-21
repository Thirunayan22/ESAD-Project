<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PrProductInfo
 * 
 * @property int $id
 * @property int $product_id
 * @property float $price
 * @property float $quatity
 * @property string|null $default_image
 * @property string|null $product_images
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property PrProduct $pr_product
 *
 * @package App\Models
 */
class PrProductInfo extends Model
{
	protected $table = 'pr_product_info';

	protected $casts = [
		'product_id' => 'int',
		'price' => 'float',
		'quatity' => 'float'
	];

	protected $fillable = [
		'product_id',
		'price',
		'quatity',
		'default_image',
		'product_images'
	];

	public function pr_product()
	{
		return $this->belongsTo(PrProduct::class, 'product_id');
	}
}
