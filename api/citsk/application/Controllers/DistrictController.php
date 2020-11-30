<?php
namespace Citsk\Controllers;

use Citsk\Interfaces\Controllerable;
use Citsk\Interfaces\IController;
use Citsk\Library\Identity;
use Citsk\Models\District;

final class DistrictController extends Controller implements Controllerable, IController
{
    /**
     * @var District
     */
    protected $model;

    /**
     * @var Identity
     */
    protected $user;

    public function initializeController(): void
    {

        $this->model = new District;
        $this->user  = new Identity;
    }

    public function getList(): void
    {

        $payload = $this->model->getDistricts();
        $this->dataResponse($payload);
    }

    public function add(): void
    {

        $this->checkAdminAccess();

        $districtId = $this->model->addDistrict($this->bindDistrictParams());
        $this->successResponse(['id' => $districtId]);
    }

    /**
     * @return void
     */
    public function update(): void
    {

        $this->checkAdminAccess();

        $this->model->updateDistrict($this->bindDistrictParams());
        $this->successResponse();
    }

    /**
     * @return void
     */
    public function remove(): void
    {

        $this->checkAdminAccess();

        $this->model->removeDistrict($_POST['id']);
        $this->successResponse();
    }

    /**
     * @return array
     */
    private function bindDistrictParams(): array
    {

        $districtParams = [
            "label"       => $_POST['label'],
            "regional_id" => $_POST['regionalId'] ?? null,
        ];

        return $districtParams;
    }

}
