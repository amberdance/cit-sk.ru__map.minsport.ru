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

        $geoId        = $this->model->addGeoobject();
        $properties   = $_POST['properties'];
        $videogallery = $properties['videogallery'] ?? null;

        if (isset($properties['videogallery'])) {
            unset($properties['videogallery']);
        }

        if ($_POST['categories']) {
            $this->model->addCategory($geoId, $_POST['categories']);
        }

        if (!empty($properties)) {
            $this->model->addProperty($geoId, $properties);
        }

        if ($videogallery) {
            $this->model->addVideogallery($geoId, $videogallery);
        }

        $this->successResponse(['id' => $geoId]);
    }

    /**
     * @return void
     */
    public function update(): void
    {

        $properties   = $_POST['properties'];
        $videogallery = $properties['videogallery'] ?? null;
        $geoId        = $_POST['id'];

        if (isset($properties['videogallery'])) {
            unset($properties['videogallery']);
        }

        $coords = explode(",", $_POST['coords']);
        $params = [
            "label"     => trim($_POST['label']),
            "latitude"  => $coords[0],
            "longitude" => $coords[1],
        ];

        $this->model->updateGeoobject($geoId, $params);

        if ($_POST['categories']) {
            $this->model->updateCategory($geoId, $_POST['categories']);
        }

        if ($properties) {
            $this->model->updateProperty($geoId, $properties);
        }

        $this->model->updateVideogallery($geoId, $videogallery);

        $this->successResponse();
    }

    /**
     * @return void
     */
    public function remove(): void
    {

        if (is_array($_POST['id'])) {
            array_walk($_POST['id'], function ($id) {
                $this->removeCallback($id);
            });
        } else {
            $this->removeCallback($_POST['id']);
        }

        $this->successResponse();
    }

    /**
     * @return void
     */
    public function uploadFile(): void
    {
        $this->checkIsAuthorized();

        $fileMeta = Shared::uploadFiles();
        $this->model->writeFilePathToDatabase($_POST['id'], $fileMeta, $_POST['propertyCode']);
        $this->successResponse();
    }

    /**
     * @return void
     */
    public function detachFile(): void
    {
        $this->checkIsAuthorized();

        $this->model->removeFileById($_POST['id']);
        $this->successResponse();
    }

    /**
     * @param int $id
     *
     * @return void
     */
    private function removeCallback(int $id): void
    {

        if ($this->model->hasHall($id)) {
            $this->model->removeHall($id);
        }

        if ($this->model->hasFiles($id)) {
            $this->model->removeFileByEntityId($id);
        };

        $this->model->removeItem($id);

    }
}
