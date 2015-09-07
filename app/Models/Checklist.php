<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Revisionable\Laravel\RevisionableTrait; // trait
use Sofa\Revisionable\Revisionable; // interface

class Checklist extends Model implements Revisionable {
    use SoftDeletes;
    protected $dates = ['deleted_at'];
	protected $table = 'checklists';
	use RevisionableTrait;

    /*
     * Set revisionable whitelist - only changes to any
     * of these fields will be tracked during updates.
     */
    protected $revisionable = [
        'name',
        'description',
        'user_id',
    ];

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
	/**
	 * Function to calculate level
	 */
	public function level($level)
	{
		$data = SurveySdp::join('surveys', 'surveys.id', '=', 'survey_sdps.survey_id')
						 ->where('surveys.checklist_id', $this->id)
						 ->get();
		$counter = 0;
		$total = 0.00;
		$total_points = $this->sections->sum('total_points');
		foreach ($data as $datum)
		{
			foreach ($datum->sqs as $sq)
			{
				if($sq->question->answers->count()>0)
					$total+=$sq->ss->score;
			}
			if(($total*100/$total_points >= $level->range_lower) && ($total*100/$total_points <= $level->range_upper))
			{
				$counter++;
			}
		}
		return round($counter/count($data), 2);
	}
	/**
	 * Count unique officers who participated in survey
	 */
	public function officers()
	{
		return $this->surveys->groupBy('qa_officer')->count();
	}
}
