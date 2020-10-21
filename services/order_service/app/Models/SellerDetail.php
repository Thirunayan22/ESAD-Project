<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SellerDetail
 * 
 * @property int $id
 * @property int $user_id
 * @property bool $verify_status
 * @property string $document
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class SellerDetail extends Model
{
	protected $table = 'seller_details';

	protected $casts = [
		'user_id' => 'int',
		'verify_status' => 'bool'
	];

	protected $fillable = [
		'user_id',
		'verify_status',
		'document'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
