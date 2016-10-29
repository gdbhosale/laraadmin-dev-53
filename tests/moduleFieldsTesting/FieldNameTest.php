<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FieldNameTest extends TestCase
{
	use DatabaseMigrations;

	var $probable_module_id = 9;

	/**
	 * Basic setup before testing
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();
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

	
	/**
	 * Test Module Field - Name
	 *
	 * @return void
	 */
	public function testModuleFieldName()
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
			->type('10', 'minlength')
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

		// Generate CRUD's
		$response = $this->call('GET', '/admin/module_generate_migr_crud/'.$this->probable_module_id);
		$this->assertEquals(200, $response->status());
		$this->visit('/admin/modules/'.$this->probable_module_id)
			->see('Module Generated')
			->see('Update Module')
			->see('StudentsController');
	}

	/**
	 * Test Module Field - Name - Part 2
	 *
	 * @return void
	 */
	public function testModuleFieldName2()
	{
		// Create a Row with Name Field
		$this->visit('/admin/students')
			->see('Students listing')
			->type('John Doe', 'name')
			->press('Submit')
			->see("John Doe");
		
		// Edit a Row with Name Field

		// Delete a Row with Name Field
		
		// Delete Name Field
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
		// Delete CRUD's Data
		unlink(base_path('/app/Http/Controllers/LA/StudentsController.php'));
		unlink(base_path('/app/Models/Student.php'));
		unlink(base_path('/resources/views/la/students/edit.blade.php'));
		unlink(base_path('/resources/views/la/students/index.blade.php'));
		unlink(base_path('/resources/views/la/students/show.blade.php'));

        if(LAHelper::laravel_ver() == 5.3) {
            exec('git checkout '.'routes/admin_routes.php');
        } else {
            exec('git checkout '.'app/Http/admin_routes.php');
        }

		// Delete migration table
		$this->artisan('migrate:reset');
		$tables = LAHelper::getDBTables([-1]);
		DB::statement("DROP TABLE migrations");
		
		// Delete migration file
		$mgr_file = LAHelper::get_migration_file("students_table");
		if($mgr_file != "") {
			unlink($mgr_file);
		}

		$this->artisan('migrate');
    }
}
