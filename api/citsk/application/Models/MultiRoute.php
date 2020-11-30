<?php

namespace Citsk\Models;

use Citsk\Models\Structure\MultiRouteStructure;

class MultiRoute extends CommonModel
{

    /**
     * @var string
     */
    private $entity = "multiroute";

    public function __construct()
    {
        parent::__construct($this->entity);

    }

    /**
     * @return MultiRouteStructure
     */
    public function getPlacemarks(): MultiRouteStructure
    {
        $select = [
            "mi.id",
            "mi.label",
            "mi.mode",
            "mi.latitude",
            "mi.longitude",
        ];

        $filter = $this->getQueryFilter();

        $routes = $this->database
            ->setDbTable("multiroute_items mi")
            ->getList($select, $filter['filter'], $filter['args'], $filter['join'])
            ->getRows();

        foreach ($routes as $key => $value) {
            $routes[$key]['properties']    = $this->getProperties($value['id'])->structure;
            $routes[$key]['preview_image'] = $this->getPreviewImage($value['id'])->structure;
        }

        return new MultiRouteStructure($routes, 'thePlacemark');
    }

    /**
     * @return MultiRouteStructure
     */
    public function getRoutes(): MultiRouteStructure
    {

        $select = [
            "mi.id",
            "mi.district_id",
            "mi.created",
            "mi.label",
            "mi.mode",
            "mi.distance",
            "mi.duration",
            "mi.created",
            "mi.published",
            "mi.deleted",
            "state.id"       => "state_id",
            "state.label"    => "state_label",
            "district.id"    => "district_id",
            "district.label" => "district_label",
        ];

        $filter = $this->getQueryFilter(true);

        $routes = $this->database
            ->setDbTable("multiroute_items mi")
            ->getList($select, $filter['filter'], $filter['args'], $filter['join'])
            ->getRows();

        foreach ($routes as $key => $value) {
            $routes[$key]['waypoints']     = $this->getWayPoints($value['id']);
            $routes[$key]['properties']    = $this->getProperties($value['id'])->structure;
            $routes[$key]['preview_image'] = $this->getPreviewImage($value['id'])->structure;
            $routes[$key]['photogallery']  = $this->getPhotogallery($value['id'])->structure;
            $routes[$key]['videogallery']  = $this->getVideogallery($value['id'])->structure;
        }

        return new MultiRouteStructure($routes, "theRoute");
    }

    /**
     *
     * @return int
     */
    public function addRoute(): int
    {

        $coords = $_POST['waypoints'][0]['coordinates'];

        $insert = [
            "label"       => ":label",
            "mode"        => ":mode",
            "longitude"   => ":longitude",
            "latitude"    => ":latitude",
            "duration"    => ":duration",
            "distance"    => ":distance",
            "district_id" => $this->user->districtId,
        ];

        $args = [
            ":label"     => $_POST['label'],
            ":mode"      => $_POST['routingMode'],
            ":longitude" => $coords[0],
            ":latitude"  => $coords[1],
            ":duration"  => $_POST['duration'],
            ":distance"  => $_POST['distance'],
        ];

        if ($this->user->isManager) {
            $insert['publish_state_id'] = 2;
        }

        $routeId = $this->database
            ->setDbTable("multiroute_items")
            ->add($insert, $args, true)
            ->getInsertedId();

        return $routeId;

    }

    /**
     * @return MultiRoute
     */
    public function updateRoute(): MultiRoute
    {
        $coords = $_POST['waypoints'][0]['coordinates'];

        $updateField = [
            "label"     => ":label",
            "mode"      => ":mode",
            "longitude" => ":longitude",
            "latitude"  => ":latitude",
            "duration"  => ":duration",
            "distance"  => ":distance",

        ];

        $filter = [
            "id" => ":id",
        ];

        $updateArgs = [
            ":id"        => $_POST['id'],
            ":label"     => trim($_POST['label']),
            ":mode"      => $_POST['routingMode'],
            ":longitude" => $coords[0],
            ":latitude"  => $coords[1],
            ":duration"  => $_POST['duration'],
            ":distance"  => $_POST['distance'],
        ];

        $this->database
            ->setDbTable("multiroute_items")
            ->update($updateField, $filter, $updateArgs);

        return $this;
    }

    /**
     * @param int|null $id
     *
     * @return MultiRoute
     */
    public function addWayPoint(?int $id = null): MultiRoute
    {

        if (empty($_POST['waypoints'])) {
            return $this;
        }

        $this->database->setDbTable("multiroute_geo");

        $addWaypoint = function ($waypoint, $key, $id) {

            $insert = [
                "multiroute_id" => ":id",
                "coords"        => ":coords",
            ];

            $args = [
                ":id"     => $id ?? $_POST['id'],
                ":coords" => implode(",", array_reverse($waypoint['coordinates'])),
            ];

            $this->database->add($insert, $args);
        };

        array_walk($_POST['waypoints'], $addWaypoint, $id);

        return $this;
    }

    /**
     * @return MultiRoute
     */
    public function deleteWaypoint(): MultiRoute
    {
        if (!isset($_POST['waypoints'])) {
            return $this;
        }

        $delete = [
            "multiroute_id" => ":id",
        ];

        $args = [
            ":id" => $_POST['id'],
        ];

        $this->database
            ->setDbTable("multiroute_geo")
            ->delete(null, $delete, $args);

        return $this;
    }

    /**
     * @param int
     *
     * @return array
     */
    private function getWayPoints(int $id): array
    {
        $select = [
            "coords",
        ];

        $filter = [
            "multiroute_id" => $id,
        ];

        $sort = [
            "id" => "DESC",
        ];

        $coords = $this->database
            ->setDbTable("multiroute_geo")
            ->getList($select, $filter, null, null, null, $sort)
            ->getRows(7);

        return $coords;
    }

    /**
     * @return array
     */
    /**
     * @param bool $isShowDraft
     *
     * @return array
     */
    private function getQueryFilter(bool $isShowDraft = false): array
    {
        $filter = [];

        $join = [
            "publish_states state" => "state.id = mi.publish_state_id",
            "districts district"   => "district.id = mi.district_id",
        ];

        $args = null;

        if (!$isShowDraft) {
            $filter['state.id'] = 1;
        }

        if ($this->user->isManager) {
            $filter["district_id"] = $this->user->districtId;
        }

        if (isset($_GET['id'])) {
            $filter = ["mi.id" => ":id"];
            $args   = [":id" => $_GET['id']];

            return [
                'filter' => $filter,
                'args'   => $args,
            ];
        }

        if (isset($_POST)) {

            if ($_POST['routingMode']) {
                $filter["mi.mode"] = ":mode";
                $args[":mode"]     = $_POST['routingMode'];
            }
        }

        return [
            'filter' => $filter,
            'args'   => $args,
            'join'   => $join,
        ];
    }
}
