<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvItem
 * 
 * @property int $id
 * @property int $inv_id
 * @property int $product_id
 * @property float $quantity
 * @property float $unit_price
 * @property float|null $discount_amount
 * @property float $total_price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property InvInvoice $inv_invoice
 *
 * @package App\Models
 */
class InvItem extends Model
{
	protected $table = 'inv_items';

	protected $casts = [
		'inv_id' => 'int',
		'product_id' => 'int',
		'quantity' => 'float',
		'unit_price' => 'float',
		'discount_amount' => 'float',
		'total_price' => 'float'
	];

	protected $fillable = [
		'inv_id',
		'product_id',
		'quantity',
		'unit_price',
		'discount_amount',
		'total_price'
	];

	public function inv_invoice()
	{
		return $this->belongsTo(InvInvoice::class, 'inv_id');
	}
}
