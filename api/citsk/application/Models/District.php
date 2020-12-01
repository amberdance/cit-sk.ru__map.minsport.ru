<?php

namespace Citsk\Models;

use Citsk\Library\MySQLHelper;
use Citsk\Models\Structure\DistrictStructure;

class District extends MySQLHelper
{

    public function __construct()
    {
        parent::__construct();

        $this->setDbTable("districts");
    }

    /**
     * @param int $districtId
     *
     * @return DistrictStructure
     */
    public function getDistricts(?int $districtId = null): DistrictStructure
    {
        $select = [
            "id",
            "label",
            "regional_id",
        ];

        $filter = null;

        if ($districtId) {
            $filter = [
                "district_id" => $districtId,
            ];

        }

        $rows = $this->getList($select, $filter)->getRows();

        return new DistrictStructure($rows, "theDistrict");
    }

    /**
     * @return int|null
     */
    public function addDistrict(array $params): ?int
    {

        $insert = [
            "label"       => $params['label'],
            "regional_id" => $params['regional_id'] ?? null,
        ];

        $districtId = $this->add($insert)->getInsertedId();

        return $districtId;
    }

    /**
     * @param array $params
     *
     * @return void
     */
    public function updateDistrict(array $params): void
    {
        $update = [
            "label"       => $params['label'],
            "regional_id" => $params['regional_id'] ?? null,
        ];

        $filter = [
            "id" => $params['id'],
        ];

        $this->update($update, $filter)->getRowCount();
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function removeDistrict(int $districtId): void
    {

        $filter = [
            "id" => $districtId,
        ];

        $this->delete(null, $filter)->getRowCount();

    }

}
