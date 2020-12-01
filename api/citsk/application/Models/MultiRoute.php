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
            ->skipArgs()
            ->getList($select, $filter['filter'], $filter['join'])
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

        $filter = $this->getQueryFilter();

        $routes = $this->database
            ->setDbTable("multiroute_items mi")
            ->getList($select, $filter['filter'], $filter['join'])
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
     * @param array $params
     *
     * @return int
     */
    public function addRoute(array $params): int
    {

        return $this->database
            ->setDbTable("multiroute_items")
            ->add($params)
            ->getInsertedId();
    }

    /**
     * @param int $id
     * @param array $params
     *
     * @return MultiRoute
     */
    public function updateRoute(int $id, array $params): MultiRoute
    {

        $filter = [
            "id" => $id,
        ];

        $this->database
            ->setDbTable("multiroute_items")
            ->update($params, $filter);

        return $this;
    }

    /**
     * @param int $id
     * @param array $waypoints
     *
     * @return void
     */
    public function addWayPoint(int $id, array $waypoints): void
    {

        $this->database->setDbTable("multiroute_geo");

        $addWaypoint = function ($waypoint, $key, $id) {

            $insert = [
                "multiroute_id" => $id,
                "coords"        => implode(",", array_reverse($waypoint['coordinates'])),
            ];

            $this->database->add($insert);
        };

        array_walk($waypoints, $addWaypoint, $id);
    }

    /**
     * @return MultiRoute
     */
    public function deleteWaypoint(int $id): MultiRoute
    {

        $delete = [
            "multiroute_id" => $id,
        ];

        $this->database
            ->setDbTable("multiroute_geo")
            ->delete(null, $delete);

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
            ->getList($select, $filter, null, null, $sort)
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
        $filter = [
            // "mi.district_id" => "(1,2)",
        ];

        $join = [
            "publish_states state" => "state.id = mi.publish_state_id",
            "districts district"   => "district.id = mi.district_id",
        ];

        if (!$isShowDraft) {
            $filter['state.id'] = 1;
        }

        if ($this->user->isManager) {
            $filter["district_id"] = $this->user->districtId;
        }

        if (isset($_POST['districts']) && !empty($_POST['districts'])) {
            $districtsImplode = implode(",", $_POST['districts']);

            $filter['mi.district_id'] = "() $districtsImplode";
        }

        if (isset($_GET['id'])) {
            $filter = [
                "mi.id" => $_GET['id'],
            ];

            return [
                'filter' => $filter,
                'join'   => $join,
            ];
        }

        if (isset($_POST)) {
            if ($_POST['routingMode']) {
                $filter["mi.mode"] = "'{$_POST['routingMode']}'";
            }
        }

        return [
            'filter' => $filter,
            'join'   => $join,
        ];
    }
}
