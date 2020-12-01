<?php
namespace Citsk\Models;

use Citsk\Library\Identity;
use Citsk\Library\MySQLHelper;
use Citsk\Library\Shared;
use Citsk\Models\Structure\CommonStructure;

class CommonModel
{

    /**
     * @var MySQLHelper
     */
    public $database;

    /**
     * @var Identity
     */
    protected $user;

    /**
     * @var string
     */
    private $entity;

    /**
     * @param string|null $entity
     */
    public function __construct(?string $entity = null)
    {
        $this->database = new MySQLHelper;
        $this->user     = new Identity;
        $this->entity   = $entity;
    }

    /**
     * @return CommonStructure
     */
    public function getPublishStates(): CommonStructure
    {
        $select = [
            "id",
            "label",
        ];

        $rows = $this->database
            ->setDbTable("publish_states")
            ->getList($select)
            ->getRows();

        return new CommonStructure($rows);
    }

    /**
     * @param int $itemId
     * @param int $stateId
     *
     * @return void
     */
    public function setPublishState(string $entity, int $itemId, int $stateId): void
    {
        $update = [
            "publish_state_id" => $stateId,
        ];

        $filter = [
            "id" => $itemId,
        ];

        if ($stateId == 1) {
            $update['published'] = Shared::currentTimestamp();
        }

        if ($stateId == 4) {
            $update['deleted'] = Shared::currentTimestamp();
        }

        $this->database
            ->setDbTable("{$entity}_items")
            ->update($update, $filter);
    }

    /**
     * @param int $entityId
     *
     * @return bool
     */
    public function hasFiles(int $entityId): bool
    {
        $filter = [
            "prop.{$this->entity}_id" => $entityId,
        ];

        $join = [
            "{$this->entity}_property prop" => "prop.value = fs.id",
        ];

        $hasFile = $this->database
            ->setDbTable("files fs")
            ->getList("fs.id", $filter, $join)
            ->getRowCount();

        return boolval($hasFile);
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function removeItem(int $id): void
    {

        $this->database->setDbTable("{$this->entity}_items item");

        $delete = "item, prop";

        $filter = [
            "item.id" => $id,
        ];

        $join = [
            "{$this->entity}_property prop" => "prop.{$this->entity}_id = item.id",
        ];

        if ($this->entity == 'geo') {
            $delete .= ' ,cat';
            $join['category_items cat'] = "cat.geo_id = item.id";
        }

        if ($this->entity == 'multiroute') {
            $delete .= ' ,geo';
            $join['multiroute_geo geo'] = "geo.multiroute_id = item.id";
        }

        $this->database->delete($delete, $filter, $join);

    }

    /**
     * @param int $entityId
     *
     * @return void
     */
    public function removeFileByEntityId(int $entityId): void
    {

        $delete = "fs, prop";

        $filter = [
            "prop.{$this->entity}_id" => $entityId,
        ];

        $join = [
            "{$this->entity}_property prop" => "prop.value = fs.id",
        ];

        $this->database->setDbTable("files fs");
        $fileDir = $this->getFileDirectory($entityId);

        array_walk($fileDir, function ($dir) {
            Shared::removeDirectory(UPLOADS_DIR . $dir);
        });

        $this->database->delete($delete, $filter, $join);
    }

    /**
     * @param int $id
     * @param array $fileMeta
     * @param string $propertyCode
     *
     * @return void
     */
    public function writeFilePathToDatabase(int $id, array $fileMeta, string $propertyCode): void
    {

        $fileId = null;

        foreach ($fileMeta as $file) {

            // file table insert
            $insert = [
                "file_name"     => $file['file_name'],
                "original_name" => $file['original_name'],
                "sub_dir"       => $file['sub_dir'],
                "mime_type"     => $file['type'],
                "size"          => $file['size'],
            ];

            $fileId = $this->database
                ->setDbTable("files")
                ->add($insert)
                ->getInsertedID();

            // property table insert
            $insert = [
                "{$this->entity}_id" => $id,
                "value"              => $fileId,
                "meta_id"            => $this->getEntityMetaIdByPropertyCode($propertyCode),
            ];

            $this->database->setDbTable("{$this->entity}_property")->add($insert);
        }
    }

    /**
     * @param int $fileId
     *
     * @return void
     */
    public function removeFileById(int $fileId): void
    {

        $fileMeta   = $this->getFileMetaById($fileId);
        $directory  = UPLOADS_DIR . $fileMeta['sub_dir'];
        $filesCount = count(scandir($directory)) - 2;

        if ($filesCount > 1) {
            unlink("$directory/{$fileMeta['file_name']}");
        }

        if ($filesCount == 1) {
            Shared::removeDirectory($directory);
        }

        $delete = "fs, prop";

        $filter = [
            "fs.id" => $fileId,
        ];

        $join = [
            "files fs" => "fs.id = prop.value",
        ];

        $this->database
            ->setDbTable("{$this->entity}_property prop")
            ->delete($delete, $filter, $join);
    }

    /**
     * @param int|null $id
     * @param int|null $limit
     *
     * @return CommonStructure
     */
    public function getProperties(?int $id = null, ?int $limit = null): CommonStructure
    {

        $tableName = $id
        ? "{$this->entity}_property prop"
        : "{$this->entity}_meta meta";

        $select = [
            "meta.label",
            "meta.data_type",
            "meta.code",
        ];

        $filter = [
            "meta.is_active"    => 1,
            "meta.content_type" => "!= media",
        ];

        $join = null;

        if ($id) {
            $select = array_merge($select, [
                "prop.id",
                "prop.value",
            ]);

            $filter["prop.{$this->entity}_id"] = $id;
            $filter["prop.value"]              = "!= 0";

            $join = [
                "{$this->entity}_meta meta" => "meta.id = prop.meta_id",
            ];
        }

        $rows = $this->database
            ->setDbTable($tableName)
            ->getList($select, $filter, $join)
            ->setSorting(["meta.sort" => "ASC"])
            ->setLimit($limit)
            ->getRows();

        return new CommonStructure($rows, 'theProperty');
    }

    /**
     * @return array
     */
    public function getServiceProperties(): array
    {
        $result = [
            "string" => $this->getPropertyByDataType('string')->structure,
            "array"  => $this->getPropertyByDataType('array')->structure,
            "bool"   => $this->getPropertyByDataType('bool')->structure,
            "number" => $this->getPropertyByDataType('number')->structure,
        ];

        return $result;
    }

    /**
     * @param int $id
     * @param array $rawProperties
     *
     * @return void
     */
    public function addProperty(int $id, array $rawProperties): void
    {

        $properties = $rawProperties;
        ksort($properties);

        $insert     = [];
        $entityMeta = $this->getEntityMeta($properties);

        $this->database->setDbTable("{$this->entity}_property");

        for ($i = 0; $i < count($entityMeta); $i++) {

            $value  = array_values($properties)[$i];
            $insert = [
                "{$this->entity}_id" => $id,
                "meta_id"            => $entityMeta[$i]['id'],
                "value"              => $value,
            ];

            $this->database->add($insert);
        }
    }

    /**
     * @param int $id
     * @param array $properties
     *
     * @return void
     */
    public function updateProperty(int $id, array $properties): void
    {

        $select = "prop.value";

        $join = [
            "{$this->entity}_meta meta" => "meta.id = prop.meta_id",
        ];

        foreach ($properties as $propertyCode => $propertyValue) {

            if (gettype($propertyValue) == 'boolean') {
                $propertyValue = $propertyValue ? '1' : '0';
            }

            $update = [
                "prop.value" => $propertyValue,
            ];

            $filter = [
                "prop.{$this->entity}_id" => $id,
                "meta.code"               => $propertyCode,
            ];
            $this->database->setDbTable("{$this->entity}_property prop");
            $isFieldExists = $this->database->getList($select, $filter, $join)->getRowCount();

            if (boolval($isFieldExists)) {
                $this->database->update($update, $filter, $join);
            } else {
                $this->addPropertyByMetaCode($id, $propertyCode, $propertyValue);
            }
        }
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function isItemDraft(int $id): bool
    {

        $filter = [
            "id" => $id,
        ];

        if ($this->entity == 'hall') {
            $filter = [
                "geo_id" => $id,
            ];
        }

        $isDraft = $this->database
            ->setDbTable("{$this->entity}_items")
            ->getList("publish_state_id", $filter)
            ->getColumn();

        return intval($isDraft > 1) ? true : false;

    }

    /**
     * @param int $id
     * @param array|null $videoGallery
     *
     * @return void
     */
    public function updateVideogallery(int $id, ?array $videoGallery): void
    {
        $filter = [
            "{$this->entity}_id" => $id,
            "meta_id"            => $this->getEntityMetaIdByPropertyCode('videogallery'),
        ];

        $this->database
            ->setDbTable("{$this->entity}_property")
            ->delete(null, $filter);

        if (is_array($videoGallery)) {
            $this->addVideogallery($id, $videoGallery);
        }
    }

    /**
     * @param int $id
     *
     * @return CommonStructure
     */
    protected function getPreviewImage(int $id): CommonStructure
    {

        $select = [
            "fs.id",
            "CONCAT(fs.sub_dir, '/', fs.file_name)" => 'src',
        ];

        $join = [
            "{$this->entity}_meta meta"  => "meta.id = prop.meta_id",
            "{$this->entity}_items item" => "item.id = prop.{$this->entity}_id",
            "files fs"                   => "fs.id = prop.value",
        ];

        $filter = [
            "prop.{$this->entity}_id" => $id,
            "meta.code"               => "preview_image",
        ];

        $rows = $this->database
            ->setDbTable("{$this->entity}_property prop")
            ->getList($select, $filter, $join)
            ->setSorting(["id" => "DESC"])
            ->getRows();

        return new CommonStructure($rows, 'thePhotogallery');
    }

    /**
     * @param int $id
     *
     * @return CommonStructure
     */
    protected function getPhotogallery(int $id): CommonStructure
    {
        $select = [
            "fs.id",
            "CONCAT(fs.sub_dir, '/', fs.file_name)" => "src",
        ];

        $filter = [
            "prop.{$this->entity}_id" => $id,
            "meta.code"               => "photogallery",
        ];

        $join = [
            "{$this->entity}_meta meta" => "meta.id = prop.meta_id",
            "files fs"                  => "fs.id = prop.value",
        ];

        $rows = $this->database
            ->setDbTable("{$this->entity}_property prop")
            ->getList($select, $filter, $join)
            ->getRows();

        return new CommonStructure($rows, 'thePhotogallery');
    }

    /**
     * @param int $id
     * @param string $entity
     *
     * @return CommonStructure
     */
    protected function getVideogallery(int $id): CommonStructure
    {

        $select = [
            "value",
        ];

        $filter = [
            "{$this->entity}_id" => $id,
            "meta_id"            => $this->getEntityMetaIdByPropertyCode('videogallery'),
        ];

        $rows = $this->database
            ->setDbTable("{$this->entity}_property")
            ->getList($select, $filter)
            ->getRows(7);

        return new CommonStructure($rows, 'theVideogallery');
    }

    /**
     * @param int $id
     * @param array $youtubeLinks
     *
     * @return void
     */
    public function addVideogallery(int $id, array $youtubeLinks): void
    {

        $insert = [
            "{$this->entity}_id" => $id,
            "meta_id"            => $this->getEntityMetaIdByPropertyCode('videogallery'),
        ];

        $this->database->setDbTable("{$this->entity}_property");

        array_walk($youtubeLinks, function ($link) use (&$insert) {
            $insert["value"] = $link;
            $this->database->add($insert);
        });
    }

    /**
     * @param int $id
     *
     * @return array|null
     */
    private function getFileDirectory(int $id): ?array
    {

        $select = "fs.sub_dir";

        $filter = [
            "prop.{$this->entity}_id" => $id,
        ];

        $join = [
            "{$this->entity}_property prop" => "prop.value = fs.id",
        ];

        $rows = $this->database
            ->setDbTable("files fs")
            ->getList($select, $filter, $join)
            ->getRows(7);

        return $rows;
    }

    /**
     * @param int|null $id
     *
     * @return string|null
     */
    private function getFileName(?int $id = null): ?string
    {

        $selectField = [
            "file_name",
        ];

        $filters = [
            "id" => $id ?? $_POST['fileId'] ?? $_POST['id'] ?? $_GET['id'],
        ];

        return $this->database
            ->setDbTable('files')
            ->getList($selectField, $filters)
            ->getColumn();
    }

    /**
     * @param int $id
     * @param string $propertyCode
     * @param string|null $propertyValue
     *
     * @return void
     */
    private function addPropertyByMetaCode(int $id, string $propertyCode, ?string $propertyValue): void
    {
        if (!trim($propertyValue)) {
            return;
        }

        $insert = [
            "{$this->entity}_id" => $id,
            "value"              => $propertyValue,
            "meta_id"            => $this->getEntityMetaIdByPropertyCode($propertyCode),
        ];

        $this->database->setDbTable("{$this->entity}_property")->add($insert);
    }

    /**
     * @param string $propertyCode
     *
     * @return int
     */
    private function getEntityMetaIdByPropertyCode(string $propertyCode): int
    {
        $filter = [
            "code" => $propertyCode,
        ];

        $id = $this->database
            ->setDbTable("{$this->entity}_meta")
            ->getList("id", $filter)
            ->getColumn();

        return intval($id);
    }

    /**
     * @param int $fileId
     *
     * @return array
     */
    private function getFileMetaById(int $fileId): ?array
    {
        $select = [
            "created",
            "label",
            "size",
            "mime_type",
            "file_name",
            "sub_dir",
            "original_name",
        ];

        $filter = [
            "id" => $fileId,
        ];

        $fileMeta = $this->database
            ->setDbTable("files")
            ->getList($select, $filter)
            ->getRow();

        return $fileMeta;
    }

    /**
     * @param string $dataType
     *
     * @return CommonStructure
     */
    private function getPropertyByDataType(string $dataType): CommonStructure
    {

        $select = [
            "id",
            "label",
            "code",
        ];

        $filter = [
            "is_active"    => 1,
            "content_type" => "!= 'media'",
            "data_type"    => "'$dataType'",
        ];

        $rows = $this->database
            ->setDbTable("{$this->entity}_meta")
            ->skipArgs()
            ->getList($select, $filter)
            ->setSorting(["sort" => "ASC"])
            ->getRows();

        return new CommonStructure($rows, 'theProperty');
    }

    /**
     * @param array $metaCodes
     *
     * @return array
     */
    private function getEntityMeta(array $metaCodes): array
    {

        $joinCodes = "";

        foreach ($metaCodes as $key => $value) {
            $joinCodes .= "'$key',";
        }

        $joinCodes = trim($joinCodes, ",");
        $query     = "SELECT id, code FROM {$this->entity}_meta WHERE code IN ($joinCodes) ORDER BY code ASC";

        return $this->database->customQuery($query)->getRows();
    }
}
