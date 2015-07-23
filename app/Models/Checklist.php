<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checklist extends Model {
    use SoftDeletes;
    protected $dates = ['deleted_at'];
	protected $table = 'checklists';

	/**
	 * Surveys relationship
	 */
	public function surveys()
	{
		return $this->hasMany('App\Models\Survey');
	}
	/**
	 * Sections relationship
	 */
	public function sections()
	{
		return $this->hasMany('App\Models\Section');
	}	
	/**
	* Get sdps in period given
	*/
	public function sdps($from = NULL, $to = NULL)
	{
		return $sdps = Survey::select('sdp_id')
					  		 ->where('checklist_id', $this->id)
					  		 ->get();
	}
	/**
	* Return Checklist ID given the name
	* @param $name the name of the user
	*/
	public static function idByName($name=NULL)
	{
		if($name!=NULL){
			try 
			{
				$checklist = Checklist::where('name', $name)->orderBy('name', 'asc')->firstOrFail();
				return $checklist->id;
			} catch (ModelNotFoundException $e) 
			{
				Log::error("The checklist ` $name ` does not exist:  ". $e->getMessage());
				//TODO: send email?
				return null;
			}
		}
		else{
			return null;
		}
	}
}
