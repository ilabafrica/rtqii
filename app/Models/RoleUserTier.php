<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleUserTier extends Model {
    use SoftDeletes;
    protected $dates = ['deleted_at'];
	protected $table = 'user_tiers';
}