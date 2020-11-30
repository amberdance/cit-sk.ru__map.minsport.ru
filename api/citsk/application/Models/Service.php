<?php
namespace Citsk\Models;

use Citsk\Exceptions\DataBaseException;
use Citsk\Library\MySQLHelper;
use Citsk\Library\Shared;

/**
 * API class for service usage only
 */
class Service extends MySQLHelper
{

    public function __construct()
    {
        set_time_limit(666);

        parent::__construct();
    }

    /**
     * @param string $label
     *
     * @return int|null
     */
    public function addDistrict(string $label): ?int
    {

        $insert = [
            "label" => "'$label'",
        ];

        $rowId = $this->setDbTable("districts")
            ->add($insert, null, true)
            ->getInsertedId();

        if (!$rowId) {
            throw new DataBaseException("Insert failed");
        }

        return $rowId;
    }

    /**
     * @return void
     */
    public function addDistrictFromFile(): void
    {

        $districts = file($_SERVER['DOCUMENT_ROOT'] . "/api/citsk/application/districts.txt");

        array_walk($districts, function ($label) {
            $this->addDistrict(trim($label));
        });
    }

    /**
     * @param string $loginPrefix
     *
     * @return void
     */
    public function addManagersByDistrict(string $loginPrefix = "district"): void
    {

        $district = new District;

        $rows       = $district->getDistricts()->structure;
        $accessList = "";

        array_walk($rows, function ($value) use (&$loginPrefix, &$accessList) {

            $responsibleId      = $this->addResponsibleByDistrictName(trim($value['label']));
            $passwordHash       = Shared::getRandomPasswordHash();
            $passwordDoubleHash = password_hash($passwordHash, PASSWORD_DEFAULT);
            $login              = "{$loginPrefix}_$responsibleId";

            $insert = [
                "district_id"    => $value['id'],
                "login"          => "'$login'",
                "password"       => "'$passwordDoubleHash'",
                "responsible_id" => $responsibleId,
                "role"           => 2,
            ];

            $userId = $this->setDbTable("users")
                ->add($insert, null, true)
                ->getInsertedId();

            if (!$userId) {
                throw new DataBaseException("Insert failed");
            }

            $accessList .= "district: {$value['label']}, login: $login, password: $passwordHash \n";

        });

        $this->createAccessList($accessList);
    }

    /**
     * @param array $params
     *
     * @return int|null
     */
    private function addResponsibleByDistrictName(string $name): ?int
    {

        $insert = [
            "name" => "'$name'",
        ];

        $responsibleId = $this->setDbTable("responsibles")
            ->add($insert, null, true)
            ->getInsertedId();

        if (!$responsibleId) {
            throw new DataBaseException("Insert failed");
        }

        return $responsibleId;
    }

    /**
     * @return void
     */
    private function createAccessList(string $inputData): void
    {

        $fileStream = fopen($_SERVER['DOCUMENT_ROOT'] . "/api/citsk/application/access-list.txt", 'w');

        fwrite($fileStream, $inputData);
        fclose($fileStream);
    }

}
