<?php

namespace Citsk\Controllers;

use Citsk\Interfaces\Controllerable;
use Citsk\Interfaces\IController;
use Citsk\Library\Identity;
use Citsk\Library\Shared;
use Citsk\Models\Geoobject;

final class GeoobjectController extends Controller implements Controllerable, IController
{

    /**
     * @var Geoobject
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
        $this->model = new Geoobject;
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
        
        $payload = $this->model->getGeoobjects();
        $this->dataResponse($payload);
    }

    /**
     * @return void
     */
    public function add(): void
    {

        $geoobjectId = $this->model->addGeoobject();
        $this->model->database->startTransaction();

        $this->model
            ->addCategory($geoobjectId)
            ->addProperty($geoobjectId);

        $this->model->database->executeTransaction();
        $this->successResponse(['id' => $geoobjectId]);

    }

    /**
     * @return void
     */
    public function update(): void
    {

        $this->model->database->startTransaction();

        $this->model
            ->updateGeoobject()
            ->updateCategory()
            ->updateProperty();

        $this->model->database->executeTransaction();
        $this->successResponse();
    }

    /**
     * @return void
     */
    public function remove(): void
    {

        $this->model
            ->removeHall()
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
