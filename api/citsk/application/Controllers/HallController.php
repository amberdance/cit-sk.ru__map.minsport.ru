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
        $this->dataResponse($payload);
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

        if (!empty($_POST['properties'])) {
            $this->model->addProperty($hallId, $_POST['properties']);
        }

        $this->successResponse(['id' => $hallId]);
    }

    /**
     * @return void
     */
    public function update(): void
    {
        $hallId       = $_POST['id'];
        $properties   = $_POST['properties'];
        $videogallery = $properties['videogallery'] ?? null;

        if (isset($properties['videogallery'])) {
            unset($properties['videogallery']);
        }

        $params = [
            "label"  => trim($_POST['label']),
            "geo_id" => $_POST['geoId'],
        ];

        $this->model->updateHall($hallId, $params);

        if ($properties) {
            $this->model->updateProperty($hallId, $properties);
        }

        $this->model->updateVideogallery($hallId, $videogallery);

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

        if ($this->model->hasFiles($id)) {
            $this->model->removeFileByEntityId($id);
        };

        $this->model->removeItem($id);
    }
}
