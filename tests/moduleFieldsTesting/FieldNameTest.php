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
     * Delete Current Test Data
     *
     * @return void
     */
	public function testDeleteData()
    {
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

		$this->artisan('migrate');
    }
}
