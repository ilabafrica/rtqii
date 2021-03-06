<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Revisionable\Laravel\RevisionableTrait; // trait
use Sofa\Revisionable\Revisionable; // interface

class Survey extends Model implements Revisionable {
    use SoftDeletes;
    protected $dates = ['deleted_at'];
	protected $table = 'surveys';
	use RevisionableTrait;

    /*
     * Set revisionable whitelist - only changes to any
     * of these fields will be tracked during updates.
     */
    protected $revisionable = [
        'qa_officer',
        'facility_id',
        'longitude',
        'latitude',
        'checklist_id',
        'comment',
        'user_id',
    ];
	/**
	 * Checklist relationship
	 */
	public function checklist()
	{
		return $this->belongsTo('App\Models\Checklist');
	}
	/**
	 * Users relationship
	 */
	public function user()
	{
		return $this->belongsTo('App\Models\User');
	}
	/**
	 * Facility relationship
	 */
	public function facility()
	{
		return $this->belongsTo('App\Models\Facility');
	}
	/**
	 * Sdp relationship
	 */
	public function sdp()
	{
		return $this->hasMany('App\Models\SurveySdp');
	}
	/**
	 * SurveyQuestions relationship
	 */
	public function questions()
	{
		return $this->hasMany('App\Models\SurveyQuestion');
	}
	/**
	 * Count number of questionnaires given qa officer filled
	 */
	public static function questionnaires($officer, $checklist_id)
	{
		return Survey::where('qa_officer', $officer)->where('checklist_id', $checklist_id)->count();
	}
	/**
	* Calculation of positive percent[ (Total Number of Positive Results/Total Number of Specimens Tested)*100 ]
	*/
	public function positivePercent(){
		return round($this->positive*100/($this->positive+$this->negative+$this->indeterminate), 2);
	}
	/**
	* Calculation of overall agreement[ ((Total Tested - Total # of Invalids on Test 1 and Test 2) – (ABS[Reactives from Test 2 –Reactives from Test 1] +ABS [ Non-reactive from Test 2- Non-reactive  from Test 1)/Total Tested – Total Number of Invalids)*100 ]
	*/
	public function overallAgreement(){
		$total = $this->positive+$this->negative+$this->indeterminate;
		$invalid = $this->htcData->where('test_kit_no', Htc::TESTKIT1)->first()->invalid + $this->htcData->where('test_kit_no', Htc::TESTKIT2)->first()->invalid;
		$absReactive = abs($this->htcData->where('test_kit_no', Htc::TESTKIT2)->first()->reactive - $this->htcData->where('test_kit_no', Htc::TESTKIT1)->first()->reactive);
		$absNonReactive = abs($this->htcData->where('test_kit_no', Htc::TESTKIT2)->first()->non_reactive - $this->htcData->where('test_kit_no', Htc::TESTKIT1)->first()->non_reactive);
		return round((($total - $invalid) - ($absReactive + $absNonReactive)) * 100 / ($total - $invalid), 2);
	}
	/**
	 * survey-me-info relationship
	 */
	public function me()
	{
		return $this->hasOne('App\Models\MeInfo');
	}
	/**
	 * survey-spirt-info relationship
	 */
	public function spirt()
	{
		return $this->hasMany('App\Models\SpirtInfo');
	}
	/**
	 * survey-question relationship
	 */
	public function sqs()
	{
		return $this->hasMany('App\Models\SurveyQuestion');
	}
	/**
	 * SurveySdps relationship
	 */
	public function sdps()
	{
		return $this->hasMany('App\Models\SurveySdp');
	}
    /**
     * Get sdps in a list e.g. VCT, PMTC, OPD...
     */
    public function ssdps()
    {
        $ssdps = array();
        foreach ($this->sdps as $key => $value)
        {
            $sdp = Sdp::find($value->sdp_id);
            $data = $sdp->name;
            if($value->comment)
            	$data = $sdp->name.' ('.$value->comment.')';
            array_push($ssdps, $data);
        }
        return $ssdps;
    }

    /**
     * Get register-start-dates for all pages of the survey
     */
    public function dates()
    {
    	$question_id = Question::idByName('Register Page Start Date');
    	$dates = SurveySdp::where('survey_id', $this->id)
        					->join('htc_survey_pages', 'survey_sdps.id', '=', 'htc_survey_pages.survey_sdp_id')
        					->join('htc_survey_page_questions', 'htc_survey_pages.id', '=', 'htc_survey_page_questions.htc_survey_page_id')
        					->join('htc_survey_page_data', 'htc_survey_page_questions.id', '=', 'htc_survey_page_data.htc_survey_page_question_id')
        					->where('question_id', $question_id)
        					->lists('answer');
        if(!count($dates)>0)
        {
        	$dates = [$this->date_submitted];
        }
        usort($dates, function($a, $b) {
		    $dateTimestamp1 = strtotime($a);
		    $dateTimestamp2 = strtotime($b);

		    return $dateTimestamp1 < $dateTimestamp2 ? -1: 1;
		});
        return json_encode(['min' => $dates[0], 'max' => $dates[count($dates)-1]]);
    }
}