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
            "publish_state_id" => ":stateId",
        ];

        $filter = [
            "id" => ":id",
        ];

        $args = [
            ":id"      => $itemId,
            ":stateId" => $stateId,
        ];

        if ($stateId == 1) {
            $update['published'] = "CURRENT_TIMESTAMP()";
        }

        if ($stateId == 4) {
            $update['deleted'] = "CURRENT_TIMESTAMP()";
        }

        $this->database
            ->setDbTable("{$entity}_items")
            ->update($update, $filter, $args);
    }

    /**
     * @return void
     */
    public function removeItem(?int $id = null): void
    {

        $this->database->setDbTable("{$this->entity}_items item");

        $delete = [
            "item, prop",
        ];

        $filter = [
            "item.id" => ":id",
        ];

        $args = null;

        $join = [
            "{$this->entity}_property prop" => "prop.{$this->entity}_id = item.id",
        ];

        if ($this->entity == 'geo') {
            $delete[]                   = 'cat';
            $join['category_items cat'] = "cat.geo_id = item.id";
        }

        if ($this->entity == 'multiroute') {
            $delete[]                   = 'geo';
            $join['multiroute_geo geo'] = "geo.multiroute_id = item.id";
        }

        if (is_array($_POST['id'])) {

            array_walk($_POST['id'], function ($id) use ($delete, $filter, $args, $join) {

                $args = [
                    ":id" => $id,
                ];

                $this->database->delete($delete, $filter, $args, $join);
            });

        } else {
            $args = [
                ":id" => $id ?? $_POST['id'],
            ];

            $this->database->delete($delete, $filter, $args, $join);
        }

    }

    /**
     * @return CommonModel
     */
    public function removeFileByEntityId(): CommonModel
    {

        $this->database->setDbTable("files fs");

        $delete = [
            "fs",
        ];

        $filter = [
            "prop.{$this->entity}_id" => ":id",
        ];

        $args = [
            ":id" => $_POST['id'],
        ];

        $join = [
            "{$this->entity}_property prop" => "prop.value = fs.id",
        ];

        if (is_array($_POST['id'])) {

            foreach ($_POST['id'] as $id) {

                $args[':id'] = $id;
                $directory   = $this->getFileDirectory($id);

                array_walk($directory, function ($dir) {
                    Shared::removeDirectory($dir);
                });

                $this->database->delete($delete, $filter, $args, $join);

            }
        } else {
            $directory = $this->getFileDirectory($_POST['id']);

            array_walk($directory, function ($dir) {
                Shared::removeDirectory($dir);
            });

            $this->database->delete($delete, $filter, $args, $join);

        }

        return $this;
    }

    /**
     * @param array $fileMeta
     *
     * @return void
     */
    public function writeFilePathToDatabase(array $fileMeta): void
    {

        $fileId = null;

        foreach ($fileMeta as $file) {

            // file table insert
            $insert = [
                "file_name"     => "'{$file['file_name']}'",
                "original_name" => "'{$file['original_name']}'",
                "sub_dir"       => "'{$file['sub_dir']}'",
                "mime_type"     => "'{$file['type']}'",
                "size"          => $file['size'],
            ];

            $fileId = $this->database
                ->setDbTable("files")
                ->add($insert, null, true)
                ->getInsertedID();

            // property table insert
            $insert = [
                "{$this->entity}_id" => ":id",
                "value"              => $fileId,
                "meta_id"            => "(SELECT id FROM {$this->entity}_meta WHERE code = :code)",
            ];

            $args = [
                ":id"   => $_POST['id'],
                ":code" => "{$_POST['propertyCode']}",
            ];

            $this->database
                ->setDbTable("{$this->entity}_property")
                ->add($insert, $args);
        }
    }

    /**
     * @return void
     */
    public function removeFileById(): void
    {

        $fileMeta              = $this->getFileMetaById($_POST['id']);
        $fullPath              = UPLOADS_DIR . "/{$fileMeta['sub_dir']}";
        $countFilesInDirectory = count(scandir($fullPath)) - 2;

        if ($countFilesInDirectory > 1) {
            unlink("$fullPath/{$fileMeta['file_name']}");
        } else {
            Shared::removeDirectory($fullPath);
        }

        $delete = [
            "fs, prop",
        ];

        $filter = [
            "fs.id" => ":id"];

        $args = [
            ":id" => $_POST['id'],
        ];

        $join = [
            "files fs" => "fs.id = prop.value",
        ];

        $this->database
            ->setDbTable("{$this->entity}_property prop")
            ->delete($delete, $filter, $args, $join);
    }

    /**
     * @param string|null $id
     * @param int|null $limit
     * @param bool $isServiceFields
     *
     * @return CommonStructure
     */
    public function getProperties(?string $id = null, ?int $limit = null): CommonStructure
    {

        $tableName = $id ? "{$this->entity}_property prop" : "{$this->entity}_meta meta";

        $select = [
            "meta.label",
            "meta.data_type",
            "meta.code",
        ];

        $filter = [
            "meta.is_active"    => 1,
            "meta.content_type" => "!= 'media'",
        ];

        $args = null;
        $join = null;

        $sort = [
            "meta.sort" => "ASC",
        ];

        if ($id) {
            $select = array_merge($select, [
                "prop.id",
                "prop.value",
            ]);

            $args = [
                ":id" => $id,
            ];

            $filter["prop.{$this->entity}_id"] = ":id";
            $filter["prop.value"]              = "!= '0'";

            $join = [
                "{$this->entity}_meta meta" => "meta.id = prop.meta_id",
            ];
        }

        $rows = $this->database
            ->setDbTable($tableName)
            ->getList($select, $filter, $args, $join, null, $sort, $limit)
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
     *
     * @return CommonModel
     */
    public function addProperty(int $id): CommonModel
    {

        if (empty($_POST['properties'])) {
            return $this;
        }

        $property = $_POST['properties'];
        ksort($property);

        $splittedMetaCodes = implode(",", array_keys($property));
        $splittedMetaCodes = str_replace(",", "','", $splittedMetaCodes);

        $entityMeta = $this->getEntityMeta($splittedMetaCodes);

        $insert = [];
        $args   = [];

        $this->database->setDbTable("{$this->entity}_property");

        for ($i = 0; $i < count($entityMeta); $i++) {

            if (isset($_POST['properties']['videogallery']) && $entityMeta[$i]['code'] == 'videogallery') {
                $this->addVideogallery($id);

                continue;
            }

            $value = array_values($property)[$i];

            $insert = [
                "{$this->entity}_id" => ":id",
                "meta_id"            => ":metaId",
                "value"              => ":value",
            ];

            $args = [
                ":id"     => $id,
                ":metaId" => $entityMeta[$i]['id'],
                ":value"  => $value,
            ];

            $this->database->add($insert, $args);
        }

        return $this;
    }

    /**
     * @return CommonModel
     */
    public function updateProperty(): CommonModel
    {

        $select = [
            "prop.value",
        ];

        $join = [
            "{$this->entity}_meta meta" => "meta.id = prop.meta_id",
        ];

        $args = [
            ":id" => $_POST['id'],
        ];

        foreach ($_POST['properties'] as $propertyCode => $propertyValue) {

            if ($propertyCode == 'videogallery') {
                $this->updateVideogallery();
                continue;
            }

            if (gettype($propertyValue) == 'boolean') {
                $propertyValue = $propertyValue ? '1' : '0';
            }

            $update = [
                "prop.value" => "'$propertyValue'",
            ];

            $filter = [
                "prop.{$this->entity}_id" => ":id",
                "meta.code"               => "'$propertyCode'",
            ];

            $isFieldExists = $this->database
                ->setDbTable("{$this->entity}_property prop")
                ->getList($select, $filter, $args, $join)
                ->getRowCount();

            if ($isFieldExists) {
                $this->database
                    ->setDbTable("{$this->entity}_property prop")
                    ->update($update, $filter, $args, $join);
            } else {
                $this->addPropertyByMetaCode($propertyCode, $propertyValue);
            }
        }

        return $this;
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function isItemDraft(int $id): bool
    {

        $filter = [
            "id" => ":id",
        ];

        $args = [
            ":id" => $id,
        ];

        if ($this->entity == 'hall') {
            $filter = [
                "geo_id" => ":id",
            ];
        }

        $isDraft = $this->database
            ->setDbTable("{$this->entity}_items")
            ->getList("publish_state_id", $filter, $args)
            ->getColumn();

        return intval($isDraft == 2) ? true : false;

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
            "prop.{$this->entity}_id" => ":id",
            "meta.code"               => "'preview_image'",
        ];

        $args = [
            ":id" => $id,
        ];

        $order = [
            "id" => "DESC",
        ];

        $rows = $this->database
            ->setDbTable("{$this->entity}_property prop")
            ->getList($select, $filter, $args, $join, null, $order)
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
            "prop.{$this->entity}_id" => ":id",
            "meta.code"               => "'photogallery'",
        ];

        $args = [
            ":id" => $id,
        ];

        $join = [
            "{$this->entity}_meta meta" => "meta.id = prop.meta_id",
            "files fs"                  => "fs.id = prop.value",
        ];

        $rows = $this->database
            ->setDbTable("{$this->entity}_property prop")
            ->getList($select, $filter, $args, $join)
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

        $args = [
            ":id" => $id,
        ];

        $filter = [
            "{$this->entity}_id" => ":id",
            "meta_id"            => " (SELECT id FROM {$this->entity}_meta WHERE code = 'videogallery')",
        ];

        $rows = $this->database
            ->setDbTable("{$this->entity}_property")
            ->getList($select, $filter, $args)
            ->getRows(7);

        return new CommonStructure($rows, 'theVideogallery', );
    }

    /**
     * @param int $id
     * @param string $entity
     *
     * @return void
     */
    protected function addVideogallery(?int $id = null): void
    {

        $insert = [
            "{$this->entity}_id" => $id ?? $_POST['id'],
            "meta_id"            => " (SELECT id FROM {$this->entity}_meta WHERE code = 'videogallery')",
        ];

        foreach ($_POST['properties']['videogallery'] as $src) {
            $insert["value"] = "'$src'";

            $this->database
                ->setDbTable("{$this->entity}_property")
                ->add($insert);
        }
    }

    /**
     * @return void
     */
    protected function updateVideogallery(?int $id = null): void
    {
        $filter = [
            "{$this->entity}_id" => ':id',
            "meta_id"            => " (SELECT id FROM {$this->entity}_meta WHERE code = 'videogallery')",
        ];

        $args = [
            ':id' => $id ?? $_POST['id'],
        ];

        $this->database
            ->setDbTable("{$this->entity}_property")
            ->delete(null, $filter, $args);

        if (count($_POST['properties']['videogallery'])) {
            $this->addVideogallery();
        }
    }

    /**
     * @param int $id
     *
     * @return array|null
     */
    private function getFileDirectory(int $id): ?array
    {

        $selectGroup = [
            "fs.sub_dir",
        ];

        $filter = [
            "prop.{$this->entity}_id" => ":id",
        ];

        $args = [
            ":id" => $id,
        ];

        $join = [
            "{$this->entity}_property prop" => "prop.value = fs.id",
        ];

        $rows = $this->database
            ->setDbTable("files fs")
            ->getList($selectGroup, $filter, $args, $join, $selectGroup)
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

        $selectField = ["file_name"];
        $args        = ["id" => ":id"];
        $filters     = [":id" => $id ?? $_POST['fileId'] ?? $_POST['id'] ?? $_GET['id']];

        return $this->database
            ->setDbTable('files')
            ->getList($selectField, $args, $filters)
            ->getColumn();
    }

    /**
     * @param string $metaCode
     * @param string|null $propertyValue
     *
     * @return bool
     */
    private function addPropertyByMetaCode(string $metaCode, ?string $propertyValue): ?bool
    {
        if (!trim($propertyValue)) {
            return false;
        }

        $insert = [
            "{$this->entity}_id" => ":id",
            "value"              => ":value",
            "meta_id"            => "(SELECT id FROM {$this->entity}_meta WHERE code = '$metaCode')",
        ];

        $args = [
            ":id"    => $_POST['id'],
            ":value" => $propertyValue,
        ];

        $this->database
            ->setDbTable("{$this->entity}_property")
            ->add($insert, $args);

        return true;
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

        $args = ["id" => $fileId];

        $fileMeta = $this->database
            ->setDbTable("files")
            ->getList($select, $args)
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

        $sort = [
            "sort" => "ASC",
        ];

        $rows = $this->database
            ->setDbTable("{$this->entity}_meta")
            ->getList($select, $filter, null, null, null, $sort)
            ->getRows();

        return new CommonStructure($rows, 'theProperty');
    }

    /**
     * @param string|null $metaCodes
     *
     * @return array
     */
    private function getEntityMeta(?string $metaCodes = null): array
    {
        $select = [
            "id",
            "code",
        ];

        $filter = null;

        if ($metaCodes) {
            $filter = [
                "code" => "() '$metaCodes'",
            ];
        }

        $sort = [
            "code" => "ASC",
        ];

        $entityMeta = $this->database
            ->setDbTable("{$this->entity}_meta")
            ->getList($select, $filter, null, null, null, $sort)
            ->getRows();

        return $entityMeta;
    }
}
