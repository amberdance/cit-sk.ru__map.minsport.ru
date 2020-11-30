<?php

namespace Citsk\Library;

use Exception;
use Firebase\JWT\JWT;
use stdClass;

/**
 * Identity
 * @property int $userId
 * @property int $responsibleId
 * @property int $role
 * @property bool $isAuthorized
 * @property bool $isAdmin
 * @property bool $isManager
 * @property bool $isObserver
 * @property bool $isUserHasPermission
 */
final class Identity
{

    /**
     * @var int
     */
    private $userId;

    /**
     * @var int
     */
    private $responsibleId;

    /**
     * @var int
     */
    private $districtId;

    /**
     * @var int
     */
    private $role;

    /**
     * @var bool
     */
    private $isAuthorized = false;

    /**
     * @var bool
     */
    private $isAdmin = false;

    /**
     * @var bool
     */
    private $isManager = false;

    /**
     * @var bool
     */
    private $isObserver = false;

    /**
     * @var bool
     */
    private $isUserHasPermission = false;

    /**
     * @var int
     */
    private $JWTLifeTime = 86400;

    public function __construct()
    {

        if (!$this->getAuthorizationToken()) {
            return;
        }

        $this->validateJWT();
    }

    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * @param stdClass $params
     *
     * @return array|int
     */
    public function setJWT(stdClass $params)
    {

        try {
            $token = [
                "iss"    => JWT['iss'],
                "aud"    => JWT['aud'],
                'exp'    => time() + $this->JWTLifeTime,
                'params' => $this->formatUserParams($params),
            ];

            $this->setUserParams($params);

            return JWT::encode($token, JWT['secret_key']);
        } catch (Exception $e) {
            return http_response_code(500);
        }
    }

    /**
     * @return void
     */
    public function validateJWT(): void
    {
        try {
            $JWT = JWT::decode($this->getAuthorizationToken(), JWT['secret_key'], ['HS256']);
            $this->setUserParams($JWT->params);
        } catch (Exception $e) {
            $this->isAuthorized = false;
            die(http_response_code(401));
        }
    }

    /**
     * @return array|false
     */
    private function getAuthorizationToken()
    {
        return (isset(apache_request_headers()['Authorization'])) ? explode(' ', apache_request_headers()['Authorization'])[1] : false;
    }

    /**
     * @param stdClass $params
     *
     * @return void
     */
    private function setUserParams(stdClass $params): void
    {
        $this->userId              = (int) $params->id;
        $this->responsibleId       = (int) $params->responsible_id;
        $this->districtId          = (int) $params->district_id;
        $this->role                = (int) $params->role;
        $this->isAdmin             = (int) $params->role === 1 ? true : false;
        $this->isManager           = (int) $params->role === 2 ? true : false;
        $this->isAuthorized        = true;
        $this->isUserHasPermission = $this->isAdmin || $this->isManager;
    }

    /**
     * @param stdClass $params
     *
     * @return array
     */
    private function formatUserParams(stdClass $params): array
    {
        return [
            'id'             => (int) $params->id,
            'role'           => (int) $params->role,
            'district_id'    => (int) $params->district_id,
            'responsible_id' => (int) $params->responsible_id,
        ];
    }
}
