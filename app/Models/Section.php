<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Revisionable\Laravel\RevisionableTrait; // trait
use Sofa\Revisionable\Revisionable; // interface

class Section extends Model implements Revisionable {
	use SoftDeletes;
    protected $dates = ['deleted_at'];
	protected $table = 'sections';
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
	 * Questions relationship
	 */
	public function questions()
	{
		return $this->hasMany('App\Models\Question');
	}
	/**
	 * Checklist relationship
	 */
	public function checklist()
	{
		return $this->belongsTo('App\Models\Checklist');
	}
	/**
	 * Check if section has scorable questions
	 */
	public function isScorable()
	{
		$array = array();
		foreach ($this->questions as $question) {
			$answers = array();
			if($question->answers->count()>0)
			{	
				foreach ($question->answers as $response) 
				{
					if($response->score>0)
						array_push($answers, $response->id);
				}
			}
			if(count($answers)>0)
				array_push($array, $question->id);
		}
		if(count($array)>0)
			return true;
		else
			return false;
	}
	/**
	 * Function to calculate scores per section
	 */
	public function spider($sdp = NULL, $site = NULL, $sub_county = NULL, $county = NULL, $from = NULL, $to = NULL, $year = 0, $month = 0, $date = 0)
	{
        //  Check dates
        $theDate = "";
        if ($year > 0) {
            $theDate .= $year;
            if ($month > 0) {
                $theDate .= "-".sprintf("%02d", $month);
                if ($date > 0) {
                    $theDate .= "-".sprintf("%02d", $date);
                }
            }
        }
        //	Start optimization
		$checklist = Checklist::idByName('SPI-RT Checklist');

		//  Get data to be used
        $values = Survey::where('checklist_id', $checklist);
        if($from && $to)
        {
            $values = $values->whereBetween('date_submitted', [$from, $to]);
        }
        if($county || $sub_county || $site)
        {
            $values = $values->whereHas('facility', function($q) use($county, $sub_county, $site)
            {
                if($sub_county || $site)
                {
                    if($site)
                        $q->where('facility_id', $site);
                    else
                        $q->where('facilities.sub_county_id', $sub_county);
                }
                else
                {
                    $q->whereHas('subCounty', function($q) use($county){
                        $q->where('county_id', $county);
                    });
                }
                
            });
        }
        $values = $values->lists('surveys.id');
        $ssdps = SurveySdp::whereIn('survey_id', $values);
        if($sdp)
            $ssdps = $ssdps->where('sdp_id', $sdp);
        $ssdps = $ssdps->get();
        //  Define variables for use
        $counter = 0;
        $total_counts = count($ssdps);
        $total_checklist_points = Checklist::find($checklist)->sections->sum('total_points');
        $unwanted = array(Question::idById('providersenrolled'), Question::idById('correctiveactionproviders')); //  do not contribute to total score
        $notapplicable = Question::idById('dbsapply');  //  dbsapply will reduce total points to 65 if corresponding answer = 0
        $reductions = 0;
        $calculated_points = 0.00;
        $percentage = 0.00;
        //  Begin processing
        foreach ($ssdps as $key => $value)
        {
            $sqtns = $value->sqs()->whereNotIn('question_id', $unwanted)    //  remove non-contributive questions
                                  ->join('survey_data', 'survey_questions.id', '=', 'survey_data.survey_question_id')
                                  ->whereIn('question_id', $this->questions->lists('id'))
                                  ->whereIn('survey_data.answer', Answer::lists('score'));
            $calculated_points+=$sqtns->whereIn('question_id', array_unique(DB::table('question_responses')->lists('question_id')))->sum('answer');
            if($sq = SurveyQuestion::where('survey_sdp_id', $value->id)->where('question_id', $notapplicable)->first())
            {
                if($sq->sd->answer == '0')
                    $reductions++;
            }
        }
        return $total_counts>0?round(($calculated_points*100)/($this->total_points*$total_counts), 2):$percentage;
		//	End optimization
	}
	/**
	 * Function to calculate the snapshot given section
	 */
	public function snapshot($county = null, $sub_county = null, $site = null, $sdp = null, $from = NULL, $to = NULL)
	{
       		//	Initialize variables
		$counter = 0;
		$checklist = Checklist::idByName('M & E Checklist');
		$values = SurveySdp::join('surveys', 'surveys.id', '=', 'survey_sdps.survey_id')
                            ->where('checklist_id', $checklist);
                            if($from && $to)
                            {
                                $values = $values->whereBetween('date_submitted', [$from, $to]);
                            }
                            if($county || $sub_county || $site || $sdp)
                            {
                                if($sub_county || $site || $sdp)
                                {
                                	if($site || $sdp)
                                	{
                                    	if($sdp)
                                        {
                                             $values = $values->where('facility_id', $site)->where('sdp_id', $sdp);
                                        }
                                        else
                                        {
                                            $values = $values->where('facility_id', $site);
                                        }
                                    }
                                    else
                                    {
                                        $values = $values->join('facilities', 'facilities.id', '=', 'surveys.facility_id')
                                                         ->where('sub_county_id', $sub_county);
                                    }
                                }
                                else
                                {
                                    $values = $values->join('facilities', 'facilities.id', '=', 'surveys.facility_id')
                                                     ->join('sub_counties', 'sub_counties.id', '=', 'facilities.sub_county_id')
                                                     ->where('county_id', $county);
                                }
                            }
                            else
                            {
                                $values = $values->join('facilities', 'facilities.id', '=', 'surveys.facility_id')
                                                 ->join('sub_counties', 'sub_counties.id', '=', 'facilities.sub_county_id')
                                                 ->join('counties', 'counties.id', '=', 'sub_counties.county_id');
                            }
                           
        $total_counts = count($values->get(array('survey_sdps.*')));
        
        $calculated_points = $values->join('survey_questions', 'survey_sdps.id', '=', 'survey_questions.survey_sdp_id')
        							->join('survey_data', 'survey_questions.id', '=', 'survey_data.survey_question_id')
        							->whereIn('question_id', $this->questions->lists('id'))
        							->whereIn('survey_data.answer', Answer::lists('score'))
        							->sum('answer');
        return $total_counts>0?round(($calculated_points*100)/($this->total_points*$total_counts), 2):$percentage;
        //  End optimization
    }
	/**
	 * Function to return color code given the percent value
	 */
	public static function color($percent)
	{
		$class = '';
		if($percent >= 0 && $percent <25)
			$class = 'does-not-exist';
		else if($percent >=25 && $percent <50)
			$class = 'in-development';
		else if($percent >=50 && $percent <75)
			$class = 'being-implemented';
		else if($percent >=75 && $percent <100)
			$class = 'completed';
		return $class;
	}
	/**
	 * Function to calculate number of specific responses
	 */
	public function column($option, $county = null, $sub_county = null, $site = null, $sdp = null, $from = NULL, $to = NULL)
	{
		//	Initialize variables
		$checklist = Checklist::idByName('M & E Checklist');
		$values = SurveySdp::join('surveys', 'surveys.id', '=', 'survey_sdps.survey_id')
                            ->where('checklist_id', $checklist);
                            if($from && $to)
                            {
                                $values = $values->whereBetween('date_submitted', [$from, $to]);
                            }
                            if($county || $sub_county || $site || $sdp)
                            {
                                if($sub_county || $site || $sdp)
                                {
                                	if($site || $sdp)
                                	{
                                    	if($sdp)
                                        {
                                            $values = $values->where('facility_id', $site)->where('sdp_id', $sdp);
                                        }
                                        else
                                        {
                                            $values = $values->where('facility_id', $site);
                                        }
                                    }
                                    else
                                    {
                                        $values = $values->join('facilities', 'facilities.id', '=', 'surveys.facility_id')
                                                         ->where('sub_county_id', $sub_county);
                                    }
                                }
                                else
                                {
                                    $values = $values->join('facilities', 'facilities.id', '=', 'surveys.facility_id')
                                                     ->join('sub_counties', 'sub_counties.id', '=', 'facilities.sub_county_id')
                                                     ->where('county_id', $county);
                                }
                            }
                            else
                            {
                                $values = $values->join('facilities', 'facilities.id', '=', 'surveys.facility_id')
                                                 ->join('sub_counties', 'sub_counties.id', '=', 'facilities.sub_county_id')
                                                 ->join('counties', 'counties.id', '=', 'sub_counties.county_id');
                            }
        $response = Answer::find(Answer::idByName($option))->score;
        $counter = $this->questions->where('score', '!=', 0)->count();
        $total_counts = count($values->get(array('survey_sdps.*')));
        $calculated_counts = $values->join('survey_questions', 'survey_sdps.id', '=', 'survey_questions.survey_sdp_id')
                                  	->join('survey_data', 'survey_questions.id', '=', 'survey_data.survey_question_id')
                                  	->whereIn('survey_data.answer', Answer::lists('score'))
                                  	->whereIn('question_id', $this->questions->lists('id'))
                                  	->where('answer', $response)
                                  	->count();
        return $total_counts>0?round(($calculated_counts*100)/($total_counts*$counter), 2):$percentage;
	}
	/**
	 * Function to calculate percentage based on quarters
	 */
	public function quarter($period)
	{
		$checklist = $this->checklist;
		$survey_sdp_ids = NULL;
		$counter = 0;
		if($period === 'Baseline')
		{
			$survey_sdp_ids = SpirtInfo::lists('survey_sdp_id');
			$counter = count($survey_sdp_ids);
		}
		else
		{
			$start_date = NULL;
			$end_date = NULL;
			if(substr($period, 8) == '1')
			{
				$start_date = date('Y-10-01');
				$end_date = date('Y-12-31');
			}
			else if(substr($period, 8) === '2')
			{
				$start_date = date('Y-01-01');
				$end_date = date('Y-03-31');	
			}
			else if(substr($period, 8) === '3')
			{
				$start_date = date('Y-04-01');
				$end_date = date('Y-06-30');	
			}
			else if(substr($period, 8) === '3')
			{
				$start_date = date('Y-07-01');
				$end_date = date('Y-09-30');	
			}
			$survey_sdp_ids = SpirtInfo::join('survey_sdps', 'survey_sdps.id', '=', 'survey_spirt_info.survey_sdp_id')
										->join('surveys', 'surveys.id', '=', 'survey_sdps.survey_id')
										->whereBetween('date_started', [$start_date, $end_date])
										->lists('survey_spirt_info.survey_sdp_id');
			$counter = count($survey_sdp_ids);
		}
		if($this->isScorable())
		{
			$total = 0.0;		
			foreach ($this->questions as $question)
			{
				if($question->answers->count()>0)
				{
					foreach ($question->sqs as $sq) 
					{
						if(in_array($sq->survey_sdp_id, $survey_sdp_ids))
							$total+=$sq->sd->answer;
					}
				}
			}
			return $counter!=0?round(($total*100)/($this->total_points*$counter), 2):0.00;
		}
	}
	/**
	* Return Section ID given the name
	* @param $name the name of the section
	*/
	public static function idByName($name=NULL)
	{
		if($name!=NULL){
			try 
			{
				$section = Section::where('name', $name)->orderBy('name', 'asc')->firstOrFail();
				return $section->id;
			} catch (ModelNotFoundException $e) 
			{
				Log::error("The section ` $name ` does not exist:  ". $e->getMessage());
				//TODO: send email?
				return null;
			}
		}
		else{
			return null;
		}
	}
    /**
     * Function to calculate percentage of sites in each domain/pillar per level
     */
    public function level($checklist, $response, $county = NULL, $sub_county = NULL, $site = NULL, $sdp = NULL, $from = NULL, $to = NULL)
    {
        //  Get answer object from $response
        $level = Answer::find(Answer::idByName($response));
        //  Get data to be used
        $values = SurveySdp::join('surveys', 'surveys.id', '=', 'survey_sdps.survey_id')
                            ->where('checklist_id', $checklist);
                            if($from && $to)
                            {
                                $values = $values->whereBetween('date_submitted', [$from, $to]);
                            }
                            if($county || $sub_county || $site || $sdp)
                            {
                                if($sub_county || $site || $sdp)
                                {
                                    if($site || $sdp)
                                    {
                                        if($sdp)
                                        {
                                            $values = $values->where('facility_id', $site)->where('sdp_id', $sdp);
                                        }
                                        else
                                        {
                                            $values = $values->where('facility_id', $site);
                                        }
                                    }
                                    else
                                    {
                                        $values = $values->join('facilities', 'facilities.id', '=', 'surveys.facility_id')
                                                         ->where('sub_county_id', $sub_county);
                                    }
                                }
                                else
                                {
                                    $values = $values->join('facilities', 'facilities.id', '=', 'surveys.facility_id')
                                                     ->join('sub_counties', 'sub_counties.id', '=', 'facilities.sub_county_id')
                                                     ->where('county_id', $county);
                                }
                            }
                            else
                            {
                                $values = $values->join('facilities', 'facilities.id', '=', 'surveys.facility_id')
                                                 ->join('sub_counties', 'sub_counties.id', '=', 'facilities.sub_county_id')
                                                 ->join('counties', 'counties.id', '=', 'sub_counties.county_id');
                            }
                            $values = $values->get(array('survey_sdps.*'));
        //  Define variables for use
        $counter = 0;
        $total_counts = count($values);
        //  Begin processing
        foreach ($values as $key => $value)
        {
            $calculated_points = 0.00;
            $percentage = 0.00;
            $sqtns = $value->sqs()->whereIn('question_id', $this->questions->lists('id'))    //  Get questions belonging to the section
                                  ->join('survey_data', 'survey_questions.id', '=', 'survey_data.survey_question_id')
                                  ->whereIn('survey_data.answer', array_filter(Answer::lists('score')));
            $calculated_points = $sqtns->sum('answer');
            $percentage = round(($calculated_points*100)/$this->total_points, 2);
            //  Check and increment counter
            if(($percentage>$level->range_lower) && ($percentage<=$level->range_upper) || (($level->range_lower==0.00) && ($percentage==$level->range_lower)))
            {
                $counter++;
            }
        }
        return $counter;
    }
}
