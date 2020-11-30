<?php

namespace Citsk\Controllers;

use Citsk\Controllers\Controller;
use Citsk\Interfaces\Controllerable;
use Citsk\Models\User;

final class UserController extends Controller implements Controllerable
{

    /**
     * @var User
     */
    protected $model;

    /**
     * @var Identity
     */
    private $identity;

    public function initializeController(): void
    {

        $this->model    = new User;
        $this->identity = $this->model->identity;

    }

    /**
     * @return void
     */
    public function login(): void
    {
        $_POST = json_decode(file_get_contents('php://input'), true);

        $password = $_POST['password'];

        if (empty($_POST['login']) || empty($password)) {
            die(http_response_code(401));
        }

        $userData = $this->model->getUserData($_POST['login']);

        if ($userData && password_verify($password, $userData->password)) {

            $JWTToken = $this->identity->setJWT($userData);
            $this->model->setUserConnectionDate($userData->id);

            $clientData = [
                'jwt'        => $JWTToken,
                'role'       => (int) $userData->role,
                'districtId' => (int) $userData->district_id,
            ];

            $this->successResponse($clientData);
        } else {
            die(http_response_code(401));
        }
    }

    /**
     * Todo: write something here
     * @return void
     */
    public function logout(): void
    {
        die();
    }
}
