<?php
namespace Citsk\Models;

use Citsk\Exceptions\DataBaseException;
use Citsk\Library\GeoFilter;
use Citsk\Models\Structure\GeoobjectStructure;

final class Geoobject extends CommonModel
{

    use GeoFilter;

    /**
     * @var string
     */
    private $entity = "geo";

    public function __construct()
    {
        parent::__construct($this->entity);
    }

    /**
     * @return array
     */
    /**
     * @return GeoobjectStructure
     */
    public function getPlacemarks(): GeoobjectStructure
    {

        $placemarks = [];

        $select = [
            "geo.id",
            "geo.label",
            "geo.latitude",
            "geo.longitude",
        ];

        $filter = $this->getFilterQueryString();

        $placemarks = $this->database
            ->setDbTable('geo_items geo')
            ->getList($select, $filter['filter'], $filter['args'], $filter['join'])
            ->getRows();

        foreach ($placemarks as $key => $value) {
            $placemarks[$key]['properties']    = $this->getProperties($value['id'], 5)->structure;
            $placemarks[$key]['preview_image'] = $this->getPreviewImage($value['id'])->structure;
            $placemarks[$key]['categories']    = $this->getCategoryById($value['id'])->structure;

        }

        return new GeoobjectStructure($placemarks, 'thePlacemark');
    }

    /**
     * @return GeoobjectStructure
     */
    public function getGeoobjects(): GeoobjectStructure
    {
        $select = [
            "gi.id",
            "gi.latitude",
            "gi.longitude",
            "gi.label",
            'gi.created',
            'gi.published',
            'gi.deleted',
            "district.id"    => "district_id",
            "district.label" => "district_label",
            "state.id"       => "state_id",
            "state.label"    => "state_label",
        ];

        $filter = null;
        $args   = null;

        $join = [
            "districts district"   => "district.id = gi.district_id",
            "publish_states state" => "state.id = gi.publish_state_id",
        ];

        if (isset($_GET['id'])) {
            $filter["gi.id"] = ":id";
            $args[":id"]     = $_GET['id'];

        }

        if ($this->user->isManager) {
            $filter["district_id"] = $this->user->districtId;
        }

        $result = $this->database
            ->setDbTable("geo_items gi")
            ->getList($select, $filter, $args, $join)
            ->getRows();

        foreach ($result as $key => $value) {
            $geoId = $_GET['id'] ?? $value['id'];

            $result[$key]['categories']    = $this->getCategoryById($geoId)->structure;
            $result[$key]['properties']    = $this->getProperties($geoId, null)->structure;
            $result[$key]['preview_image'] = $this->getPreviewImage($geoId)->structure;
            $result[$key]['photogallery']  = $this->getPhotogallery($geoId)->structure;
            $result[$key]['videogallery']  = $this->getVideogallery($geoId)->structure;
        }

        return new GeoobjectStructure($result, "theGeoobject");
    }

    /**
     * @return int
     */
    public function addGeoobject(): int
    {

        $coords = explode(",", $_POST['coords']);

        $insert = [
            "label"            => ":label",
            "latitude"         => ":latitude",
            "longitude"        => ":longitude",
            "publish_state_id" => $this->user->isAdmin ? 1 : 2,
            "district_id"      => $this->user->districtId,
        ];

        if ($this->user->isAdmin) {
            $insert['published'] = "CURRENT_TIMESTAMP()";
        }

        $args = [
            "label"     => $_POST['label'],
            "latitude"  => $coords[0],
            "longitude" => $coords[1],
        ];

        $geoId = $this->database
            ->setDbTable("geo_items")
            ->add($insert, $args, true)
            ->getInsertedId();

        if (!boolval($geoId)) {
            throw new DataBaseException("Insert failed");
        }

        return $geoId;
    }

    /**
     * @return Geoobject
     */
    public function updateGeoobject(): Geoobject
    {

        $updates = [
            "label"     => ":label",
            "latitude"  => ":latitude",
            "longitude" => ":longitude",
        ];

        $filter = [
            "id" => ":id",
        ];

        $coords = explode(",", $_POST['coords']);

        $updateArgs = [
            ":id"        => $_POST['id'],
            ":label"     => trim($_POST['label']),
            ":latitude"  => $coords[0],
            ":longitude" => $coords[1],
        ];

        $this->database
            ->setDbTable("geo_items")
            ->update($updates, $filter, $updateArgs);

        return $this;
    }

    /**
     * @param int|null $id
     * @param array|null $categoriesToInsert
     *
     * @return Geoobject
     */
    public function addCategory(?int $id = null, ?array $categoriesToInsert = null): Geoobject
    {

        if (!$categoriesToInsert && !($_POST['categories'])) {
            return $this;
        }

        $this->database->setDbTable('category_items');

        $insert = [
            "geo_id"      => ":geoId",
            "category_id" => ":catId",

        ];

        $args = [
            ":geoId" => $id ?? $_POST['id'],
        ];

        $addGeo = function ($id) use ($insert, $args) {
            $args[':catId'] = $id;

            $this->database->add($insert, $args);
        };

        $tmp = $categoriesToInsert ?? $_POST['categories'];

        array_walk($tmp, $addGeo);

        return $this;
    }

    /**
     * @return Geoobject
     */
    public function updateCategory(): Geoobject
    {

        $select = [
            "category_id",
        ];

        $filter = [
            "geo_id" => ":id",
        ];

        $args = [
            ":id" => $_POST['id'],
        ];

        $currentCategories = $this->database
            ->setDbTable("category_items")
            ->getList($select, $filter, $args)
            ->getRows(7);

        $categoriesToInsert = array_diff($_POST['categories'], $currentCategories);
        $categoriesToDelete = array_diff($currentCategories, $_POST['categories']);

        if (empty($categoriesToDelete)) {
            return $this->addCategory($_POST['id'], $categoriesToInsert);
        }

        if (empty($categoriesToInsert)) {
            return $this->deleteCategory($categoriesToDelete);
        }

        return $this;
    }

    public function getCategories(): GeoobjectStructure
    {

        $result = [];

        $select = [
            "root.id",
            "root.label",
            "root.code",
        ];

        $mainCategories = $this->database
            ->setDbTable('root_category root')
            ->getList($select)
            ->getRows();

        foreach ($mainCategories as $category) {

            $result[$category['code']] = [
                "id"    => $category["id"],
                "label" => $category["label"],
                "code"  => $category['code'],
                "items" => $this->getSubCategories($category['id']),
            ];
        }

        return new GeoobjectStructure($result, 'theCategory');
    }

    /**
     * @param int|null $id
     *
     * @return Geoobject
     */
    public function removeHall(?int $id = null): Geoobject
    {

        $this->database->setDbTable("hall_items hi");

        $delete = [
            "hi, hp, fs",
        ];

        $filter = [
            "hi.geo_id" => ":id",
        ];

        $args = [
            ":id" => $id ?? $_POST['id'],
        ];

        $join = [
            "hall_property hp" => "hp.hall_id = hi.id",
            "files fs"         => "fs.id = hp.value",
        ];

        if (is_array($_POST['id'])) {
            $values              = implode(",", $_POST['id']);
            $filter["hi.geo_id"] = "()" . $values;
            $args                = null;

        }

        $this->database
            ->delete($delete, $filter, $args, $join)
            ->getRowCount();

        return $this;
    }

    private function getSubCategories(int $id): array
    {
        $select = [
            "sub.id",
            "sub.label",
        ];

        $filter = ["sub.parent_id" => $id];

        return $this->database
            ->setDbTable('sub_category sub')
            ->getList($select, $filter)
            ->getRows();

    }

    /**
     * @param array $categories
     *
     * @return Geoobject
     */
    private function deleteCategory(array $categories): Geoobject
    {

        array_walk($categories, function ($categoryId) {
            $filter = [
                "category_id" => ":id",
            ];

            $args = [
                ":id" => $categoryId,
            ];

            $this->database
                ->setDbTable("category_items")
                ->delete(null, $filter, $args);
        });

        return $this;
    }

    /**
     * @param int $id
     *
     * @return GeoobjectStructure
     */
    private function getCategoryById(int $id): GeoobjectStructure
    {

        $select = [
            "sub.id",
            "sub.label",
            "root.code",
            "root.label" => "root",
        ];

        $filter = [
            "geo.id" => $id,
        ];

        $join = [
            "geo_items geo"      => "geo.id = cat.geo_id",
            "sub_category sub"   => "sub.id = cat.category_id",
            "root_category root" => "root.id = sub.parent_id",
        ];

        $rows = $this->database
            ->setDbTable('category_items cat')
            ->getList($select, $filter, null, $join)
            ->getRows();

        return new GeoobjectStructure($rows, 'theCategory');
    }

    /**
     * @return array
     */
    private function getFilterQueryString(): array
    {

        $result = [
            'filter' => [
                'state.id' => 1,
            ],

            'args'   => [],

            'join'   => [
                "publish_states state" => "state.id = geo.publish_state_id",
            ],

        ];

        if (isset($_POST['switcher'])) {
            $result['join'] = array_merge($result['join'], $this->getSwitcherValue());

        }

        if (isset($_POST['slider'])) {
            $result['join'] = array_merge($result['join'], $this->getSliderValue());

        }

        if (isset($_POST['category'])) {
            $result['filter']['( cat.category_id'] = $this->getCategoryValue() . " ) ";
            $result['join']["category_items cat "] = "cat.geo_id = geo.id";
        }

        if (isset($_POST['districts'])) {
            $districtsImplode = implode(",", $_POST['districts']);

            $filter = [
                "geo.district_id" => "() $districtsImplode",
            ];

            $result['filter'] = array_merge($result['filter'], $filter);
        }

        return $result;
    }
}
