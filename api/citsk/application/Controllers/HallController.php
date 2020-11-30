<?php

namespace Citsk\Controllers;

use Citsk\Controllers\Controller;
use Citsk\Interfaces\Controllerable;
use Citsk\Interfaces\IController;
use Citsk\Library\Identity;
use Citsk\Library\Shared;
use Citsk\Models\Hall;

final class HallController extends Controller implements Controllerable, IController
{

    /**
     * @var Hall
     */
    protected $model;

    /**
     * @var Identity
     */
    protected $user;

    /**
     * @return void
     */
    public function initializeController(): void
    {
        $this->model = new Hall;
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
    public function geoLabels(): void
    {
        $payload = $this->model->getGeoLabels();
        $this->successResponse($payload);
    }

    /**
     * @return void
     */
    public function getList(): void
    {
        $this->checkIsItemDraft();

        $payload = $this->model->getHalls();
        $this->dataResponse($payload);
    }

    /**
     * @return void
     */
    public function add(): void
    {

        $hallId = $this->model->addHall();
        $this->model->addProperty($hallId);

        $this->successResponse(['id' => $hallId]);

    }

    /**
     * @return void
     */
    public function update(): void
    {

        $this->model->database->startTransaction();
        $this->model
            ->updateHall()
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
