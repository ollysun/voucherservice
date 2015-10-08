<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;



class TestCase extends Laravel\Lumen\Testing\TestCase
{
    protected $authHeader;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    public function setUp()
    {
        parent::setUp();
        Input::server('HTTP_ACCEPT', '*/*');
        $this->authHeader = [
            'HTTP_X_API_KEY' => 'd78efa0e-4ea2-426d-90da-ac5fe06d956f',
            'HTTP_ACCEPT' => 'application/json',
        ];
        DB::beginTransaction();

    }

    /**
     * Tear down for the tests and rollback the db transaction
     *
     * @return void
     */
    public function tearDown()
    {
        DB::rollBack();
        parent::tearDown();
    }
}
