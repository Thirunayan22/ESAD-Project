<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class InvInvoice
 * 
 * @property int $id
 * @property int $buyer_id
 * @property int $complete_status
 * @property float $total_amount
 * @property int $payment_procedure
 * @property int $paid_status
 * @property Carbon|null $delivery_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Collection|InvItem[] $inv_items
 *
 * @package App\Models
 */
class InvInvoice extends Model
{
	protected $table = 'inv_invoice';

	protected $casts = [
		'buyer_id' => 'int',
		'complete_status' => 'int',
		'total_amount' => 'float',
		'payment_procedure' => 'int',
		'paid_status' => 'int'
	];

	protected $dates = [
		'delivery_date'
	];

	protected $fillable = [
		'buyer_id',
		'complete_status',
		'total_amount',
		'payment_procedure',
		'paid_status',
		'delivery_date'
	];

	public function inv_items()
	{
		return $this->hasMany(InvItem::class, 'inv_id');
	}
}
