<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ParallelRequest;
use App\Models\AssignTestKit;
//use App\Models\TestKit;
use App\Models\Site;
use App\Models\Parallel;
use Response;
use Auth;

class ParallelController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		
		$assignedtestkits= AssignTestKit::lists('kit_name_id', 'id');
		$sites= Site::lists('site_name', 'id');
		
		
		return view('dataentry.parallel', compact('assignedtestkits', 'sites'));
		//return view('dataentry.parallel', compact('testkits', 'sites'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(ParallelRequest $request)
	{
		$town = new Parallel;
		$town->test_site_id = $request->test_site;
        $town->book_no = $request->book_no;
        $town->page_no = $request->page_no;
        $town->start_date = $request->start_date;
        $town->end_date = $request->end_date;
        $town->test_kit1_id = $request->test_kit1;
        $town->test_kit2_id = $request->test_kit2;
        $town->test_kit3_id = $request->test_kit3;
        $town->test_kit1R = $request->test_kit1R;
        $town->test_kit1NR = $request->test_kit1NR;
        $town->test_kit1Inv = $request->test_kit1Inv;
        $town->test_kit2R = $request->test_kit2R;
        $town->test_kit2NR = $request->test_kit2NR;
        $town->test_kit2Inv = $request->test_kit2Inv;
        $town->test_kit3R = $request->test_kit3R;
        $town->test_kit3NR = $request->test_kit3NR;
        $town->test_kit3Inv = $request->test_kit3Inv;
        $town->positive = $request->positive;
        $town->negative = $request->negative;
        $town->indeterminate = $request->indeterminate;
        $town->user_id = Auth::user()->id;
        $town->save();

        $assignedtestkits= AssignTestKit::lists('kit_name_id', 'id');
		$sites= Site::lists('site_name', 'id');


		return view('dataentry.parallel', compact('assignedtestkits', 'sites'))->with('message', 'Successfully Saved.');
		


        

        
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		
		//show the view and pass the $town to it
		return view('dataentry.parallel', compact('parallel'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{

		//	Get parallel
		$parallel = Parallel::find($id);
		$assignedtestkits= AssignTestKit::lists('kit_name_id', 'id');
		$assignedtestkit1=$parallel->test_kit1_id;
		$assignedtestkit2=$parallel->test_kit2_id;
		$assignedtestkit3=$parallel->test_kit3_id;
		$sites= Site::lists('site_name', 'id');
		$site= $parallel->test_site_id;
		 return view('dataentry.editparallel', compact('parallel', 'assignedtestkits','assignedtestkit1','assignedtestkit2','assignedtestkit3', 'sites', 'site'));
	

	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(ParallelRequest $request, $id)
	{
		$town = new Parallel;
		$town->test_site_id = $request->test_site;
        $town->book_no = $request->book_no;
        $town->page_no = $request->page_no;
        $town->start_date = $request->start_date;
        $town->end_date = $request->end_date;
        $town->test_kit1_id = $request->test_kit1;
        $town->test_kit2_id = $request->test_kit2;
        $town->test_kit3_id = $request->test_kit3;
        $town->test_kit1R = $request->test_kit1R;
        $town->test_kit1NR = $request->test_kit1NR;
        $town->test_kit1Inv = $request->test_kit1Inv;
        $town->test_kit2R = $request->test_kit2R;
        $town->test_kit2NR = $request->test_kit2NR;
        $town->test_kit2Inv = $request->test_kit2Inv;
        $town->test_kit3R = $request->test_kit3R;
        $town->test_kit3NR = $request->test_kit3NR;
        $town->test_kit3Inv = $request->test_kit3Inv;
        $town->positive = $request->positive;
        $town->negative = $request->negative;
        $town->indeterminate = $request->indeterminate;
        $town->user_id = Auth::user()->id;
        $town->save();

        return redirect('result')->with('message', 'Updated successfully.');
       
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */

		public function delete($id)
	{
		
	}

	public function destroy($id)
	{
		//
	}

}
