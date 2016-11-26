<?php
/**
 * Code generated using LaraAdmin
 * Help: http://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: http://dwijitsolutions.com
 */

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NameTest extends TestCase
{
	use DatabaseMigrations;
	// use DatabaseTransactions;

	var $probable_module_id = 9;

	/**
	 * Basic setup before testing
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();

		// $this->refreshApplication();

		// Generate Seeds
		$this->artisan('db:seed');

		// Register Super Admin
		$this->visit('/register')
			->type('Taylor Otwell', 'name')
			->type('test@example.com', 'email')
			->type('12345678', 'password')
			->type('12345678', 'password_confirmation')
			->press('Register')
			->seePageIs('/');
	}

	public function testCreateModuleAndField()
	{
		// Create Students Module
		$this->visit('/admin/modules')
			->dontSee("StudentsController")
			->see('modules listing')
			->type('Students', 'name')
			->type('fa-user-plus', 'icon')
			->press('Submit')
			->see("StudentsController");
		
		// Create Name Field
		$this->see("StudentsController")
			->type('Name', 'label')
			->type('name', 'colname')
			->select('16', 'field_type')
			->check('unique')
			->type('', 'defaultvalue')
			->type('5', 'minlength')
			->type('100', 'maxlength')
			->check('required')
			->press('Submit')
			->see("StudentsController")
			->see('view_col_name')
			->click('view_col_name')
			->dontSee('view_col_name')
			->see('generate_migr_crud');
		
		// Edit Name Field - As it is
		$this->see("StudentsController")
			->click('edit_name')
			->see('from Student module')
			->press('Update')
			->see("StudentsController");
		
		$rowCount = DB::table("module_fields")->where("module", $this->probable_module_id)->count();

		echo "\n\nmodule fields count: ".$rowCount."\n\n";

		// Generate CRUD's
		
		Log::info("Generate CRUD's");

		$response = $this->call('GET', '/admin/module_generate_migr_crud/'.$this->probable_module_id);
		Log::info($response->content()." - ".$response->status());
		$this->assertEquals(200, $response->status());
		
		$this->visit('/admin/modules/'.$this->probable_module_id)
			->see('Module Generated')
			->see('Update Module')
			->see('StudentsController');
	}

	public function testUseField()
	{
		// Create a Row
		$this->visit('/admin/students')
			->see('Students listing')
			->type('John Doe', 'name')
			->see('add_record7')
			->press('Add')
			->seePageIs('/admin/students')
			->see("Students listing");
		
		$rowCount = DB::table("students")->count();

		echo "\n\nstudents: ".$rowCount."\n\n";

		$this->seeInDatabase('students', [ 'name' => 'John Doe' ]);
		
		// View a Row
		$this->visit('/admin/students/1')
			->seePageIs('/admin/students/1')
			->see('Test Description in one line')
			->see("John Doe");
		
		// Edit a Row As it is
		$this->visit('/admin/students/1')
			->see('John Doe')
			->click('edit_this_record')
			->seePageIs('/admin/students/1/edit')
			->press("Update");
		
		// Edit a Row - Value Change
		$this->visit('/admin/students/1')
			->see('John Doe')
			->click('edit_this_record')
			->seePageIs('/admin/students/1/edit')
			->type('John Wick', 'name')
			->press("Update")
			->seePageIs('/admin/students')
			->visit('/admin/students/1')
			->dontSee("John Doe")
			->see("John Wick");
			
		// Delete a Row
		$this->visit('/admin/students/1')
			->see('John Wick')
			->click('delete_this_record')
			->seePageIs('/admin/students');
		
		// Test deleted record
		$this->visit('/admin/students/1')
			->see('Student with id 1 not found');
	}

	public function testDeleteField()
	{
		// Delete Field
		$this->visit('/admin/modules/'.$this->probable_module_id)
			->see("StudentsController")
			->click('delete_name');
	}

	/**
	 * Delete Current Test Data
	 *
	 * @return void
	 */
	public function testDeleteData()
	{
		return;

		// Delete CRUD's Data
		LAHelper::deleteFile(base_path('/app/Http/Controllers/LA/StudentsController.php'));
		LAHelper::deleteFile(base_path('/app/Models/Student.php'));
		LAHelper::deleteFile(base_path('/resources/views/la/students/edit.blade.php'));
		LAHelper::deleteFile(base_path('/resources/views/la/students/index.blade.php'));
		LAHelper::deleteFile(base_path('/resources/views/la/students/show.blade.php'));

		if(LAHelper::laravel_ver() == 5.3) {
			exec('git checkout '.'routes/admin_routes.php');
		} else {
			exec('git checkout '.'app/Http/admin_routes.php');
		}

		// Delete migration table
		$this->artisan('migrate:reset');
		DB::statement("DROP TABLE migrations");
		
		// Delete migration file
		$mgr_file = LAHelper::get_migration_file("students_table");
		if($mgr_file != "") {
			unlink($mgr_file);
		}

		// dump autoload
		$composer_path = "composer";
		if(PHP_OS == "Darwin") {
			$composer_path = "/usr/bin/composer.phar";
		} else if(PHP_OS == "Linux") {
			$composer_path = "/usr/bin/composer";
		} else if(PHP_OS == "Windows") {
			$composer_path = "composer";
		}
		$this->artisan('clear-compiled');
		$this->artisan('cache:clear');
		$this->artisan('view:clear');
		// Log::info(exec($composer_path.' dump-autoload'));

		$this->refreshApplication();
		$this->artisan('migrate');
	}
}
