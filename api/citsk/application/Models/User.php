<?php

namespace Citsk\Models;

use Citsk\Library\Identity;
use Citsk\Library\MySQLHelper;
use Citsk\Library\Shared;
use stdClass;

final class User extends MySQLHelper
{

    /**
     * @var Identity
     */
    public $identity;

    public function __construct()
    {
        $this->identity = new Identity;
    }

    /**
     * @return stdClass|null
     */
    public function getUserData(string $login): ?stdClass
    {

        $select = [
            "id",
            "password",
            "role",
            "responsible_id",
            "district_id",
        ];

        $filter = [
            "login"      => ":login",
            "is_blocked" => 0,
        ];

        $args = [
            ":login" => $login,
        ];

        $userData = $this->setDbTable("users")
            ->getList($select, $filter, $args)
            ->getRow(5);

        return $userData;

    }
    /**
     * @return void
     */
    public function setUserConnectionDate(): void
    {
        $ipAddress = Shared::getIpAdress();

        $fields = [
            "created"    => "CURRENT_TIMESTAMP()",
            "user_id"    => $this->identity->userId,
            "ip_address" => "'$ipAddress'",
        ];

        $this->setDbTable("connections")->add($fields);
    }

    /**
     * @param int $responsibleId
     *
     * @return void
     */
    public function addUserByResponsibleId(int $responsibleId): void
    {
        $insert = [
            "login"          => ":login",
            "password"       => ":password",
            "role"           => ":role",
            "responsible_id" => ":responsibleId",
        ];

        $args = [
            ":login"         => null,
            ":password"      => null,
            ":role"          => null,
            ":responsibleId" => null,
        ];
    }

}
