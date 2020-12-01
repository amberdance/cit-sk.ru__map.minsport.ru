<?php

namespace Citsk\Models;

use Citsk\Models\Structure\CommonStructure;
use Citsk\Models\Structure\HallStructure;

final class Hall extends CommonModel
{
    /**
     * @var string
     */
    private $entity = "hall";

    public function __construct()
    {
        parent::__construct($this->entity);

    }

    /**
     * @return HallStructure
     */
    public function getHalls(): HallStructure
    {
        $select = [
            "hi.id",
            "hi.created",
            "hi.label",
            "hi.geo_id",
            'hi.created',
            'hi.published',
            'hi.deleted',
            "gi.district_id",
            "district.label" => "district_label",
            "gi.label"       => "geo_label",
            "state.id"       => "state_id",
            "state.label"    => "state_label",
        ];

        $filter = null;

        $join = [
            "geo_items gi"         => "gi.id = hi.geo_id",
            "publish_states state" => "state.id = hi.publish_state_id",
            "districts district"   => "district.id = gi.district_id",
        ];

        /* public filter */

        if (isset($_GET['id'])) {
            $filter = [
                "gi.id" => $_GET['id'],
            ];
        }

        /* only for authorized users filter */

        if ($this->user->isManager) {
            $filter["gi.district_id"] = $this->user->districtId;
        }

        $result = $this->database
            ->setDbTable("hall_items hi")
            ->getList($select, $filter, $join)
            ->getRows();

        foreach ($result as $key => $value) {
            $result[$key]['properties']    = $this->getProperties($value['id'])->structure;
            $result[$key]['preview_image'] = $this->getPreviewImage($value['id'])->structure;
            $result[$key]['photogallery']  = $this->getPhotogallery($value['id'])->structure;
            $result[$key]['videogallery']  = $this->getVideoGallery($value['id'])->structure;
        }

        return new HallStructure($result, "theHall");
    }

    /**
     * @return CommonStructure
     */
    public function getGeoLabels(): CommonStructure
    {
        $select = [
            "id",
            "label",
        ];

        $filter = null;

        if ($this->user->isManager) {
            $filter = [
                'district_id' => $this->user->districtId,
            ];
        }

        $rows = $this->database
            ->setDbTable("geo_items")
            ->getList($select, $filter)
            ->getRows();

        return new CommonStructure($rows);
    }

    /**
     * @return int
     */
    public function addHall(): int
    {

        $insert = [
            "geo_id"           => $_POST['geoId'],
            "label"            => $_POST['label'],
            "publish_state_id" => $this->user->isAdmin ? 1 : 2,
        ];

        $hallId = $this->database
            ->setDbTable("hall_items")
            ->add($insert)
            ->getInsertedId();

        return $hallId;

    }

    /**
     * @param int $hallId
     * @param array $params
     *
     * @return void
     */
    public function updateHall(int $hallId, array $params): void
    {

        $update = [
            "label"  => $params['label'],
            "geo_id" => $params['geo_id'],
        ];

        $filter = [
            "id" => $hallId,
        ];

        $this->database
            ->setDbTable("hall_items")
            ->update($update, $filter);

    }

    /**
     * @param int $geoId
     *
     * @return int
     */
    public function getHallIdByGeoId(int $geoId): int
    {

        $id = $this->database
            ->setDbTable("hall_items")
            ->getList("id", ["geo_id" => $geoId])
            ->setLimit(1)
            ->getColumn();

        return intval($id);

    }
}
