<?php

namespace Citsk\Models;

use Citsk\Exceptions\DataBaseException;
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
        $args   = null;

        if ($districtId) {
            $filter = [
                "district_id" => ":id",
            ];

            $args = [
                ":id" => $districtId,
            ];
        }

        $rows = $this->getList($select, $filter, $args)->getRows();

        return new DistrictStructure($rows, "theDistrict");
    }

    /**
     * @return int|null
     */
    public function addDistrict(array $params): ?int
    {

        $insert = [
            "label"       => ":label",
            "regional_id" => ":id",
        ];

        $args = [
            ":label" => "'{$params['label']}'",
            ":id"    => $params['regional_id'] ?? null,
        ];

        $districtId = $this->add($insert, $args, true)->getInsertedId();

        if (!$districtId) {
            throw new DataBaseException("Insert failed");
        }

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
            "label"       => ":label",
            "regional_id" => ":regionalId",
        ];

        $filter = [
            ":id" => $params['id'],
        ];

        $args = [
            ":id"         => $params['id'],
            ":label"      => "'{$params['label']}'",
            ":regionalId" => $params['regional_id'] ?? null,
        ];

        $isUpdated = $this->update($update, $filter, $args)->getRowCount();

        if (!boolval($isUpdated)) {
            throw new DataBaseException("Update failed");
        }

    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function removeDistrict(int $districtId): void
    {

        $filter = [
            ":id" => $districtId,
        ];

        $args = [
            "id" => ":id",
        ];

        $isDeleted = $this->delete(null, $filter, $args)->getRowCount();

        if (!boolval($isDeleted)) {
            throw new DataBaseException("Delete failed");
        }
    }

}
