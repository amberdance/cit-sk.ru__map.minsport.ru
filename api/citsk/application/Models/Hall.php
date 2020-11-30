<?php

namespace Citsk\Models;

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
        $args   = null;

        $join = [
            "geo_items gi"         => "gi.id = hi.geo_id",
            "publish_states state" => "state.id = hi.publish_state_id",
            "districts district"   => "district.id = gi.district_id",
        ];

        /* public filter */

        if (isset($_GET['id'])) {
            $filter = [
                "gi.id" => ":id",
            ];

            $args = [
                ":id" => $_GET['id'],
            ];
        }

        /* only for authorized users filter */

        if ($this->user->isManager) {
            $filter["gi.district_id"] = $this->user->districtId;
        }

        $result = $this->database
            ->setDbTable("hall_items hi")
            ->getList($select, $filter, $args, $join)
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
     * @param int $id
     *
     * @return array
     */
    public function getGeoLabels(): array
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

        return $this->database
            ->setDbTable("geo_items")
            ->getList($select, $filter)
            ->getRows();
    }

    /**
     * @return int
     */
    public function addHall(): int
    {

        $insert = [
            "geo_id"           => ":id",
            "label"            => ":label",
            "publish_state_id" => $this->user->isAdmin ? 1 : 2,
        ];

        $args = [
            ":id"    => $_POST['geoId'],
            ":label" => $_POST['label'],
        ];

        $hallId = $this->database
            ->setDbTable("hall_items")
            ->add($insert, $args, true)
            ->getInsertedId();

        return $hallId;

    }

    /**
     * @return Hall
     */
    public function updateHall(): Hall
    {

        $update = [
            "label"  => ":label",
            "geo_id" => ":geoId",
        ];

        $filter = [
            "id" => ":id",
        ];

        $args = [
            ":id"    => $_POST['id'],
            ":label" => trim($_POST['label']),
            ":geoId" => $_POST['geoId'],
        ];

        $this->database
            ->setDbTable("hall_items")
            ->update($update, $filter, $args);

        return $this;

    }
}
