<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Sofa\Revisionable\Laravel\RevisionableTrait; // trait
use Sofa\Revisionable\Revisionable; // interface
use DB;

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
	public function ssdps($from = NULL, $to = NULL, $county = NULL, $sub_county = NULL, $site = NULL, $sdp = NULL, $list = NULL, $year = 0, $month = 0, $date = 0)
	{
		$values = null;
		//	Check dates
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
		$ssdps = $this->surveys()->select('surveys.id');
		if (strlen($theDate)>0 || ($from && $to))
		{
			if($from && $to)
				$ssdps = $ssdps->whereBetween('date_submitted', [$from, $to]);
			else
				$ssdps = $ssdps->where('date_submitted', 'LIKE', $theDate."%");
		}
		if($county || $sub_county || $site)
		{
			$ssdps = $ssdps->whereHas('facility', function($q) use($county, $sub_county, $site)
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
		$ssdps = $ssdps->lists('surveys.id');
		if($ssdps)
		{
			$values = SurveySdp::whereIn('survey_id', $ssdps);
			if($list)
			{
				if($sdp)
					$values = $values->where('sdp_id', $sdp);
				$values = $values->get();
			}
			else
			{
				if($sdp)
					$values = $values->where('sdp_id', $sdp);
				$values = $values->count();

			}
		}
		return $values;
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
	public function level($categories, $county = NULL, $sub_county = NULL, $site = NULL, $sdp = NULL, $from = NULL, $to = NULL)
	{
		//	Get scores for each section
		$counter = count($categories);
		$percentage = 0.00;
		foreach ($categories as $section)
		{
			$percentage+=$section->spider($sdp, $site, $sub_county, $county, $from, $to);
		}
		return $percentage/$counter;
	}
	/**
	 * Count unique officers who participated in survey
	 */
	public function officers($county = null, $subCounty = null)
	{
		$data = null;
		if($county || $subCounty)
		{
			$data = $this->surveys()->whereHas('facility', function($q) use($county, $subCounty){
				if($subCounty)
				{
					$q->where('facilities.sub_county_id', $subCounty->id);
				}
				else
				{
					$q->whereHas('subCounty', function($q) use($county){
						$q->where('county_id', $county->id);
					});
				}
				
			});
		}
		else
		{
			$data = $this->surveys;
		}
		return $data->groupBy('qa_officer')->count();
	}
	/**
	 * Return distinct facilities with submitted data in surveys
	 */
	public function distFac()
	{
		$facilities = $this->surveys->lists('facility_id');
		return array_unique($facilities);
	}
	/**
	 * Return distinct sub-counties with submitted data in surveys
	 */
	public function distSub()
	{
		$subs = array();
		$facilities = $this->distFac();
		foreach ($facilities as $facility)
		{
			array_push($subs, Facility::find($facility)->subCounty->id);
		}
		return array_unique($subs);
	}
	/**
	 * Return counties with submitted data in surveys
	 */
	public function distCount()
	{
		$counties = array();
		$subs = $this->distSub();
		foreach ($subs as $sub)
		{
			array_push($counties, SubCounty::find($sub)->county->id);
		}
		return array_unique($counties);
	}
	/**
	 * Function to return level given the score
	 */
	public function levelCheck($score)
	{
		$levels = Level::all();
		foreach ($levels as $level)
		{
			if(($score<=$level->range_upper) && ($score>=$level->range_lower))
				return $level->name.' ('.$level->range_lower.'-'.$level->range_upper.'%)';
		}
	}
	/**
	 * Function to return percent of sites in each range - percentage
	 */
	public function overallAgreement($percentage, $sdps, $kit, $site = NULL, $sub_county = NULL, $jimbo = NULL, $year = 0, $month = 0, $date = 0, $from = null, $to = null)
	{
		//	Get scores for each section
		$counter = 0;
		$range = $this->corrRange($percentage);
		$total_sites = count($sdps);	
		foreach ($sdps as $sdp)
		{
			$agreement = Sdp::find($sdp)->overallAgreement($kit, $site, $sub_county, $jimbo, $year, $month, $date, $from, $to);
			if($agreement == 0)
				$total_sites--;
			if(($agreement>$range['lower']) && ($agreement<=$range['upper']) || (($range['lower']==0.00) && ($agreement==$range['lower'])))
				$counter++;
		}
		return $total_sites>0?round($counter*100/$total_sites, 2):0.00;
	}
	/**
	 * Function to return corresponding range given the percentage
	 */
	public function corrRange($percentage)
	{
		$range = array();
		if($percentage === '<95%')
		{
			$range['lower'] = 1;
			$range['upper'] = 95;
		}
		else if($percentage === '95-98%')
		{
			$range['lower'] = 95;
			$range['upper'] = 98;
		}
		else if($percentage === '>98%')
		{
			$range['lower'] = 98;
			$range['upper'] = 100;
		}
		return $range;
	}
	/**
	 * Function to return sdp with corresponding percentage
	 */
	public function sdpOverAgreement($label, $sdps, $kit, $site = NULL, $sub_county = NULL, $jimbo = NULL, $year = 0, $month = 0, $date = 0)
	{
		//	Split label to create variables
		$array = explode("_", $label);
		//	Get scores for each section
		$counter = 0;
		$range = $this->corrRange($array[0]);
		$year = $array[2];
		$month = $array[1];
		$total_sites = count($sdps);
		$matched = array();
		foreach ($sdps as $sdp)
		{
			$point = Sdp::find($sdp);
			$agreement = $point->overallAgreement($kit, $site, $sub_county, $jimbo, $year, $month);
			if(($agreement>$range['lower']) && ($agreement<=$range['upper']) || (($range['lower']==0.00) && ($agreement==$range['lower'])))
				$matched[$point->name] = $agreement;
				//$matched = array_merge($matched, ["sdp"=>$point->name, "per"=>$agreement]);
		}
		return $matched;
	}
	/**
	 * Function to return percent of sites in each range - percentage
	 */
	public function positivePercent($percentage, $sdps, $site = NULL, $sub_county = NULL, $jimbo = NULL, $year = 0, $month = 0, $date = 0, $from = null, $to = null)
	{
		//	Get scores for each section
		$counter = 0;
		$range = $this->corrRange($percentage);
		$total_sites = count($sdps);	
		foreach ($sdps as $sdp)
		{
			$agreement = Sdp::find($sdp)->positivePercent($site, $sub_county, $jimbo, $year, $month);
			if($agreement == 0)
				$total_sites--;
			if(($agreement>$range['lower']) && ($agreement<=$range['upper']) || (($range['lower']==0.00) && ($agreement==$range['lower'])))
				$counter++;
		}
		return $total_sites>0?round($counter*100/$total_sites, 2):0.00;
	}
	/**
	 * Function to return sdp with corresponding percentage
	 */
	public function sdpPosPercent($label, $sdps, $site = NULL, $sub_county = NULL, $jimbo = NULL, $year = 0, $month = 0, $date = 0)
	{
		//	Split label to create variables
		$array = explode("_", $label);
		//	Get scores for each section
		$counter = 0;
		$range = $this->corrRange($array[0]);
		$year = $array[2];
		$month = $array[1];
		$total_sites = count($sdps);
		$matched = array();
		foreach ($sdps as $sdp)
		{
			$point = Sdp::find($sdp);
			$agreement = $point->positivePercent($site, $sub_county, $jimbo, $year, $month);
			if(($agreement>$range['lower']) && ($agreement<=$range['upper']) || (($range['lower']==0.00) && ($agreement==$range['lower'])))
				$matched[$point->name] = $agreement;
				//$matched = array_merge($matched, ["sdp"=>$point->name, "per"=>$agreement]);
		}
		return $matched;
	}
	/**
	 * Function to return percent of sites in each range - percentage
	 */
	public function positiveAgreement($percentage, $sdps, $kit, $site = NULL, $sub_county = NULL, $jimbo = NULL, $year = 0, $month = 0, $date = 0)
	{
		//	Get scores for each section
		$counter = 0;
		$range = $this->corrRange($percentage);
		$total_sites = count($sdps);	
		foreach ($sdps as $sdp)
		{
			$agreement = Sdp::find($sdp)->positiveAgreement($kit, $site, $sub_county, $jimbo, $year, $month);
			if($agreement>100)
				$agreement=100.00;
			if($agreement == 0)
				$total_sites--;
			if(($agreement>$range['lower']) && ($agreement<=$range['upper']) || (($range['lower']==0.00) && ($agreement==$range['lower'])))
				$counter++;
		}
		return $total_sites>0?round($counter*100/$total_sites, 2):0.00;
	}
	/**
	 * Function to return sdp with corresponding percentage
	 */
	public function sdpPosAgreement($label, $sdps, $kit, $site = NULL, $sub_county = NULL, $jimbo = NULL, $year = 0, $month = 0, $date = 0)
	{
		//	Split label to create variables
		$array = explode("_", $label);
		//	Get scores for each section
		$counter = 0;
		$range = $this->corrRange($array[0]);
		$year = $array[2];
		$month = $array[1];
		$total_sites = count($sdps);
		$matched = array();
		foreach ($sdps as $sdp)
		{
			$point = Sdp::find($sdp);
			$agreement = $point->positiveAgreement($kit, $site, $sub_county, $jimbo, $year, $month);
			if(($agreement>$range['lower']) && ($agreement<=$range['upper']) || (($range['lower']==0.00) && ($agreement==$range['lower'])))
				$matched[$point->name] = $agreement;
				//$matched = array_merge($matched, ["sdp"=>$point->name, "per"=>$agreement]);
		}
		return $matched;
	}
	/**
	 * Function to return percent of sites in each range - percentage - for spirt levels
	 */
	public function spirtLevel($ssdps, $level)
	{
		// dd($ssdps);
		//	Get scores for each section
		$counter = 0;
		$total_sites = count($ssdps);
		if($total_sites>0)
		{
			foreach ($ssdps as $ssdp)
			{
				$lvl = $level->spirtLevel($this->id, $ssdp);
				if(($lvl>=$level->range_lower) && ($lvl<$level->range_upper+1) || (($level->range_lower==0.00) && ($lvl==$level->range_lower)))
					$counter++;
			}
		}
		return $total_sites>0?round($counter*100/$total_sites, 2):0.00;
	}
}
