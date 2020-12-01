<?php
namespace Citsk\Controllers;

use Citsk\Exceptions\DataBaseException;
use Exception;

class Controller
{

    /**
     * @return object|null
     */
    public function callRequestedMethod(): ?array
    {

        global $ROUTE;

        $action = $ROUTE['action'];

        //controller methods
        if (method_exists($this, $action)) {

            try {
                return call_user_func([$this, $action]);
            } catch (DataBaseException | Exception $e) {

                DB_DEBUG
                ? $this->errorResponse($e->getCode(), $e->getMessage())
                : $this->errorResponse();

            }
        }

        //model methods
        if (method_exists($this->model, $action)) {

            try {
                $data = call_user_func([$this->model, $action]);
                $this->dataResponse($data);
            } catch (DataBaseException | Exception $e) {

                DB_DEBUG
                ? $this->errorResponse($e->getCode(), $e->getMessage())
                : $this->errorResponse();
            }
        }

        die(http_response_code(404));
    }

    /**
     * @return bool
     */
    protected function isUserHasPermission(): bool
    {
        if ($this->user->isAdmin || $this->user->isManager) {
            return true;
        }

        return false;

        die(http_response_code(403));
    }

    /**
     * @return void
     */
    protected function checkIsItemDraft(): void
    {
        if (isset($_GET['id'])) {
            if ($this->model->isItemDraft($_GET['id']) && !$this->user->isAdmin) {
                die(http_response_code(404));
            }
        }
    }

    /**
     * @return void
     */
    protected function checkIsAuthorized(): void
    {

        if (!$this->user->isAuthorized) {
            die(http_response_code(403));
        }
    }

    /**
     * @return void
     */
    protected function checkAdminAccess(): void
    {
        if (!$this->user->isAdmin) {
            die(http_response_code(403));
        }
    }

    /**
     * @param object $data
     *
     * @return void
     */
    protected function dataResponse(?object $data): void
    {
        $structure = [];

        if (is_object($data)) {

            if ($data->structure) {
                $structure = count($data->structure) > 1
                ? $data->structure
                : $data->structure[0];
            }
        }

        die(json_encode($structure));
    }

    /**
     * @param array|null $data
     * @param int $status
     *
     * @return void
     */
    protected function successResponse(?array $data = null, int $status = 1): void
    {
        $response = [
            "status" => $status,
        ];

        if ($data) {
            $response['data'] = $data;
        }

        die(json_encode($response));
    }

    /**
     * @param int $status
     * @param array|null $data
     *
     * @return void
     */
    protected function errorResponse(int $status = 0, ?string $errorMessage = null): void
    {
        $response = [
            "status" => $status,
        ];

        if ($errorMessage) {
            $response['error'] = $errorMessage;
        }

        die(json_encode($response));
    }

    /**
     * @return string|null
     */
    protected function getEntityName(): ?string
    {

        if (!isset($_POST['entity']) && !isset($_GET['entity'])) {
            return null;
        }

        $entity = $_POST['entity'] ?? $_GET['entity'];

        if ($entity[strlen($entity) - 1] == "s") {
            $entity = substr($entity, 0, -1);
        }

        if ($entity == 'geoobject') {
            $entity = 'geo';
        }

        if ($entity == 'route') {
            $entity = 'multiroute';
        }

        return $entity;
    }
}
