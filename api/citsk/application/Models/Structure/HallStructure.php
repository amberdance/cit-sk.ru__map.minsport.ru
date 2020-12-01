<?php

namespace Citsk\Models\Structure;

class HallStructure extends Structure
{

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theHall(array $rows): void
    {
        foreach ($rows as $row) {
            $this->structure[] = [
                'id'           => (int) $row['id'],
                'geoId'        => (int) $row['geo_id'],
                'geoLabel'     => $row['geo_label'],
                'label'        => $row['label'],
                'properties'   => $row['properties'],
                'previewImage' => $row['preview_image'],
                'photogallery' => $row['photogallery'],
                'videogallery' => $row['videogallery'],
            ];
        }
    }

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theAdminHall(array $rows): void
    {
        foreach ($rows as $row) {
            $this->structure[] = [
                'id'            => (int) $row['id'],
                'districtId'    => (int) $row['district_id'],
                "districtLabel" => $row['district_label'],
                'stateId'       => (int) $row['state_id'],
                'geoId'         => (int) $row['geo_id'],
                'stateLabel'    => $row['state_label'],
                'geoLabel'      => $row['geo_label'],
                'created'       => $row['created'],
                'published'     => $row['published'] ?? '-',
                'deleted'       => $row['deleted'] ?? '-',
                'label'         => $row['label'],
                'properties'    => $row['properties'],
                'previewImage'  => $row['preview_image'],
                'photogallery'  => $row['photogallery'],
                'videogallery'  => $row['videogallery'],
            ];
        }
    }
}
