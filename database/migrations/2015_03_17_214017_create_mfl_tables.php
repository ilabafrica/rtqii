<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMflTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		//	Facility Types
		Schema::create('facility_types', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('name');
			$table->string('description', 100);
			$table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');

            $table->softDeletes();
			$table->timestamps();
		});
		//	Facility Owners
		Schema::create('facility_owners', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('name');
			$table->string('description', 100);
			$table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');

            $table->softDeletes();
			$table->timestamps();
		});
		//	Counties
		Schema::create('counties', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('name');
			$table->string('hq', 100);
			$table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');

            $table->softDeletes();
			$table->timestamps();
		});
		//	Constituencies
		Schema::create('constituencies', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('name');
			$table->integer('county_id')->unsigned();
			$table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('county_id')->references('id')->on('counties');

            $table->softDeletes();
			$table->timestamps();
		});		
		//	Facilities
		Schema::create('facilities', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('code', 20);
			$table->string('name', 100);
			$table->integer('county_id')->unsigned();
			$table->integer('facility_type_id')->unsigned();
			$table->integer('facility_owner_id')->unsigned();
			$table->string('reporting_site', 100);
			$table->string('nearest_town', 50);
			$table->string('landline', 50);
			$table->string('mobile', 50);
			$table->string('email', 50);
			$table->string('address', 50);
			$table->string('in_charge', 50);
			$table->string('operational_status', 2);
			$table->integer('longitude')->unsigned();
			$table->integer('latitude')->unsigned();
			$table->integer('user_id')->unsigned();

            $table->foreign('facility_type_id')->references('id')->on('facility_types');
            $table->foreign('facility_owner_id')->references('id')->on('facility_owners');
            $table->foreign('county_id')->references('id')->on('counties');
            $table->foreign('user_id')->references('id')->on('users');

            $table->softDeletes();
			$table->timestamps();
		});
		//	Site Types
		Schema::create('site_types', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('name');
			$table->string('description', 100);
			$table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');

            $table->softDeletes();
			$table->timestamps();
		});
		//	Facilities
		Schema::create('sites', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->integer('facility_id')->unsigned();
			$table->integer('site_type_id')->unsigned();
			$table->string('local_id', 100);
			$table->string('name', 100);
			$table->string('department', 50);
			$table->string('mobile', 50);
			$table->string('email', 50);
			$table->string('in_charge', 50);
			$table->integer('user_id')->unsigned();

            $table->foreign('facility_id')->references('id')->on('facilities');
            $table->foreign('site_type_id')->references('id')->on('site_types');
            $table->foreign('user_id')->references('id')->on('users');

            $table->softDeletes();
			$table->timestamps();
		});
		//	Approval agencies
		Schema::create('agencies', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('name');
			$table->string('description', 100);
			$table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');

            $table->softDeletes();
			$table->timestamps();
		});
		//	Test kits
		Schema::create('test_kits', function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('full_name', 100);
			$table->string('short_name', 100);
			$table->string('manufacturer', 100);
			$table->integer('approval_status')->unsigned();
			$table->integer('approval_agency_id')->unsigned();
			$table->tinyInteger('incountry_approval');
			$table->integer('user_id')->unsigned();

			$table->foreign('approval_agency_id')->references('id')->on('agencies');
            $table->foreign('user_id')->references('id')->on('users');

            $table->softDeletes();
			$table->timestamps();
		});		
		//	Site test kits
		Schema::create('site_test_kits', function(Blueprint $table)

			{
			$table->increments('id')->unsigned();
			$table->integer('site_id')->unsigned();
			$table->integer('kit_id')->unsigned();
			$table->string('lot_no', 100);
			$table->date('expiry_date')->nullable();
			$table->string('comments', 100);
			$table->tinyInteger('stock_available')->unsigned();
			$table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('site_id')->references('id')->on('sites');
            $table->foreign('test_kit_id')->references('id')->on('test_kits');


            $table->softDeletes();
			$table->timestamps();
		});
		//	Algorithm data
		Schema::create('htc', function(Blueprint $table)
		{

			$table->increments('id')->unsigned();
			$table->integer('site_test_kit_id')->unsigned();
			$table->integer('book_no')->unsigned();
			$table->integer('page_no')->unsigned();
			$table->date('start_date')->nullable();
			$table->date('end_date')->nullable();
			$table->integer('reactive')->unsigned();
			$table->integer('non_reactive')->unsigned();
			$table->tinyInteger('test_kit_no');
			$table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('test_site_id')->references('id')->on('sites');
            $table->foreign('test_kit1_id')->references('id')->on('test_kits');
            $table->foreign('test_kit2_id')->references('id')->on('test_kits');
            $table->foreign('test_kit3_id')->references('id')->on('test_kits');
            $table->softDeletes();
			$table->timestamps();
		});
		//	Totals as counted by data officer
		Schema::create('totals', function(Blueprint $table)
		{

			$table->increments('id')->unsigned();
			$table->string('htcs', 10);
			$table->integer('positive');
			$table->integer('negative');
			$table->integer('indeterminate');
			$table->integer('user_id')->unsigned();

            $table->foreign('user_id')->references('id')->on('users');
            $table->softDeletes();
			$table->timestamps();
		});		
	}
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('facilities');
		Schema::dropIfExists('facility_types');
		Schema::dropIfExists('facility_owners');
		Schema::dropIfExists('constituencies');
		Schema::dropIfExists('counties');
		Schema::dropIfExists('site_types');
		Schema::dropIfExists('sites');
		Schema::dropIfExists('test_kits');
		Schema::dropIfExists('agencies');
		Schema::dropIfExists('site_test_kits');
		Schema::dropIfExists('htc');
		Schema::dropIfExists('totals');
	}
}