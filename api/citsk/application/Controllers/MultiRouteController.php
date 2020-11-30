<?php

namespace Citsk\Controllers;

use Citsk\Controllers\Controller;
use Citsk\Interfaces\Controllerable;
use Citsk\Interfaces\IController;
use Citsk\Library\Identity;
use Citsk\Library\Shared;
use Citsk\Models\MultiRoute;
use Exception;

final class MultiRouteController extends Controller implements Controllerable, IController
{

    /**
     * @var MultiRoute
     */
    protected $model;

    /**
     * @var Identity
     */
    protected $user;

    public function initializeController(): void
    {
        $this->model = new MultiRoute;
        $this->user  = new Identity;

    }

    /**
     * @return void
     */
    public function getProperties(): void
    {
        if ($this->isUserHasPermission()) {
            $properties = $this->model->getServiceProperties();

            echo json_encode($properties);
        }
    }

    /**
     * @return void
     */
    public function getList(): void
    {

        $this->checkIsItemDraft();

        $payload = $this->model->getRoutes();
        $this->dataResponse($payload);
    }

    /**
     * @return void
     */
    public function add(): void
    {
        try {

            $routeId = $this->model->addRoute();

            $this->model->database->startTransaction();

            $this->model
                ->addWayPoint($routeId)
                ->addProperty($routeId);

            $this->model
                ->database
                ->executeTransaction();

            $this->successResponse(['id' => $routeId]);

        } catch (Exception $e) {
            $this->errorResponse();
        }
    }

    /**
     * @return void
     */
    public function update(): void
    {

        $this->model->database->startTransaction();
        $this->model
            ->updateRoute()
            ->deleteWaypoint()
            ->addWayPoint()
            ->updateProperty()
            ->database
            ->executeTransaction();

        $this->successResponse();
    }

    public function remove(): void
    {

        $this->model
            ->removeFileByEntityId()
            ->removeItem();

        $this->successResponse();
    }

    /**
     * @return void
     */
    public function uploadFile(): void
    {

        $fileMeta = Shared::uploadFiles();
        $this->model->writeFilePathToDatabase($fileMeta);

        $this->successResponse();
    }

    /**
     * @return void
     */
    public function detachFile(): void
    {

        $this->model->removeFileById();

        $this->successResponse();
    }
}
