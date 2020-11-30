<?php

namespace Citsk\Controllers;

use Citsk\Controllers\Controller;
use Citsk\Interfaces\Controllerable;
use Citsk\Library\Identity;
use Citsk\Models\CommonModel;

final class CommonController extends Controller implements Controllerable
{

    /**
     * @var CommonModel
     */
    protected $model;

    /**
     * @var Identity
     */
    protected $user;

    public function initializeController(): void
    {

        $this->model = new CommonModel;
        $this->user  = new Identity;
    }

    /**
     * @return void
     */
    public function getStates(): void
    {

        $this->checkIsAuthorized();
        $payload = $this->model->getPublishStates();
        $this->dataResponse($payload);

    }

    /**
     * @return void
     */
    public function setState(): void
    {
        if ($_POST['stateId'] == 1) {
            $this->checkAdminAccess();
        }

        $entity = $this->getEntityName();

        if (is_array($_POST['id'])) {
            array_walk($_POST['id'], function ($id) use (&$entity) {
                $this->model->setPublishState($entity, $id, $_POST['stateId']);
            });
        } else {
            $this->model->setPublishState($entity, $_POST['id'], $_POST['stateId']);
        }

        $this->successResponse();
    }

}
