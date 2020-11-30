<?php

namespace Citsk\Models\Structure;

use Citsk\Library\Shared;

class GeoobjectStructure extends Structure
{

    public function thePlacemark(array $rows): void
    {

        foreach ($rows as $row) {
            $this->structure[] = [
                'id'           => (int) $row['id'],
                'label'        => $row['label'],
                'category'     => Shared::joinCategories($row['categories']),
                'coords'       => Shared::getCoords($row['latitude'], $row['longitude']),
                'previewImage' => $row['preview_image'],
                'properties'   => $row['properties'],

            ];
        };
    }

    public function theGeoobject(array $rows)
    {

        foreach ($rows as $row) {
            $this->structure[] = [
                'id'            => (int) $row['id'],
                "districtLabel" => $row['district_label'],
                'coords'        => Shared::getCoords($row['latitude'], $row['longitude']),
                'category'      => Shared::joinCategories($row['categories']),
                'label'         => $row['label'],
                'previewImage'  => $row['preview_image'],
                'videogallery'  => $row['videogallery'],
                'properties'    => $row['properties'],
                'photogallery'  => $row['photogallery'],
            ];
        }
    }

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theAdminGeoobject(array $rows): void
    {

        foreach ($rows as $row) {
            $this->structure[] = [
                'id'            => (int) $row['id'],
                'districtId'    => (int) $row['district_id'],
                'stateId'       => (int) $row['state_id'],
                "districtLabel" => $row['district_label'],
                'stateLabel'    => $row['state_label'],
                'created'       => $row['created'],
                'published'     => $row['published'] ?? '-',
                'deleted'       => $row['deleted'] ?? '-',
                'coords'        => Shared::getCoords($row['latitude'], $row['longitude']),
                'category'      => Shared::joinCategories($row['categories']),
                'label'         => $row['label'],
                'previewImage'  => $row['preview_image'],
                'videogallery'  => $row['videogallery'],
                'properties'    => $row['properties'],
                'photogallery'  => $row['photogallery'],
                'categories'    => $row['categories'],
            ];
        }
    }

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theCategory(array $rows): void
    {

        foreach ($rows as $key => $item) {
            $this->structure[$key] = [
                "id"    => (int) $item['id'],
                "label" => $item['label'],
                "code"  => $item['code'],
            ];

            if (isset($item['items'])) {
                foreach ($item['items'] as $category) {
                    $this->structure[$key]['items'][] = [
                        "id"    => (int) $category['id'],
                        "label" => $category['label'],
                    ];
                }
            }
        }
    }
}
