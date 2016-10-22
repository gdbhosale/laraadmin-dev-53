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
		
		$this->del_test_cruds();
	}

	private function del_test_cruds()
	{
		unlink(base_path('/app/Http/Controllers/LA/StudentsController.php'));
		unlink(base_path('/app/Models/Student.php'));
		unlink(base_path('/resources/views/la/students/edit.blade.php'));
		unlink(base_path('/resources/views/la/students/index.blade.php'));
		unlink(base_path('/resources/views/la/students/show.blade.php'));

		// Find existing test migration file and delete it
        $mfiles = scandir(base_path('database/migrations/'));
        foreach ($mfiles as $mfile) {
            if(str_contains($mfile, "students")) {
                $mgr_file = base_path('database/migrations/'.$mfile);
                if(file_exists($mgr_file)) {
                    $templateDirectory = __DIR__.'/../vendor/dwij/laraadmin/src/stubs';
					$migrationData = file_get_contents($templateDirectory."/migration_removal.stub");
					$migrationData = str_replace("__migration_class_name__", "CreateStudentsTable", $migrationData);
					$migrationData = str_replace("__db_table_name__", "students", $migrationData);
					file_put_contents(base_path('database/migrations/'.$mfile), $migrationData);
                    break;
                }
            }
        }

        if(LAHelper::laravel_ver() == 5.3) {
            exec('git checkout '.'routes/admin_routes.php');
        } else {
            exec('git checkout '.'app/Http/admin_routes.php');
        }

		$this->artisan('migrate:refresh');
		$this->artisan('db:seed');
	}

	/**
     * A basic test example.
     *
     * @return void
     */
    public function testCreatedMigrationDeletion()
    {
		// Find existing test migration file and delete it
        $mfiles = scandir(base_path('database/migrations/'));
        foreach ($mfiles as $mfile) {
            if(str_contains($mfile, "students")) {
                $mgr_file = base_path('database/migrations/'.$mfile);
                unlink($mgr_file);
            }
        }
    }
}
