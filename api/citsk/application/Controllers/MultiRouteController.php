<?php

namespace Citsk\Controllers;

use Citsk\Controllers\Controller;
use Citsk\Interfaces\Controllerable;
use Citsk\Interfaces\IController;
use Citsk\Library\Identity;
use Citsk\Library\Shared;
use Citsk\Models\MultiRoute;

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

        $addParams = [
            "district_id"      => $this->user->districtId,
            "publish_state_id" => $this->user->isManager ? 2 : 1,
        ];

        $params = array_merge($this->bindParams(), $addParams);

        $routeId      = $this->model->addRoute($params);
        $properties   = $_POST['properties'];
        $videogallery = $properties['videogallery'] ?? null;

        $this->model->addWayPoint($routeId, $_POST['waypoints']);

        if (isset($properties['videogallery'])) {
            unset($properties['videogallery']);
        }

        if (!empty($properties)) {
            $this->model->addProperty($routeId, $properties);
        }

        if ($videogallery) {
            $this->model->addVideogallery($routeId, $videogallery);
        }

        $this->successResponse(['id' => $routeId]);

    }

    /**
     * @return void
     */
    public function update(): void
    {

        $routeId = $_POST['id'];

        $this->model->database->startTransaction();
        $this->model
            ->updateRoute($routeId, $this->bindParams())
            ->deleteWaypoint($routeId)
            ->addWayPoint($routeId, $_POST['waypoints']);

        $properties   = $_POST['properties'];
        $videogallery = $properties['videogallery'] ?? null;

        if (isset($properties['videogallery'])) {
            unset($properties['videogallery']);
        }

        $this->model->updateProperty($routeId, $properties);
        $this->model->updateVideogallery($routeId, $videogallery);

        $this->model->database->executeTransaction();
        $this->successResponse();
    }

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

    /**
     * @return array
     */
    private function bindParams(): array
    {
        $coords = $_POST['waypoints'][0]['coordinates'];

        $params = [
            "label"     => trim($_POST['label']),
            "mode"      => $_POST['routingMode'],
            "longitude" => $coords[0],
            "latitude"  => $coords[1],
            "duration"  => $_POST['duration'],
            "distance"  => $_POST['distance'],

        ];

        return $params;
    }
}
