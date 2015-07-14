<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Survey_data extends Model {
    use SoftDeletes;
    protected $dates = ['deleted_at'];
	protected $table = 'survey_data';
}