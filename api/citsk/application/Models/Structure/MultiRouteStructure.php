<?php

namespace Citsk\Models\Structure;

use Citsk\Library\Shared;

class MultiRouteStructure extends Structure
{

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theRoute(array $rows): void
    {

        foreach ($rows as $row) {
            $this->structure[] = [
                'id'           => (int) $row['id'],
                'label'        => $row['label'],
                'routingMode'  => $row['mode'],
                'distance'     => (int) $row['distance'],
                'duration'     => (int) $row['duration'],
                'properties'   => $row['properties'],
                'previewImage' => $row['preview_image'],
                'photogallery' => $row['photogallery'],
                'videogallery' => $row['videogallery'],
                'waypoints'    => $row['waypoints'],
            ];
        }
    }

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theAdminRoute(array $rows): void
    {

        foreach ($rows as $row) {
            $this->structure[] = [
                'id'            => (int) $row['id'],
                'districtId'    => (int) $row['district_id'],
                "districtLabel" => $row['district_label'],
                'stateId'       => (int) $row['state_id'],
                'distance'      => (int) $row['distance'],
                'duration'      => (int) $row['duration'],
                'stateLabel'    => $row['state_label'],
                'created'       => $row['created'],
                'published'     => $row['published'] ?? '-',
                'deleted'       => $row['deleted'] ?? '-',
                'label'         => $row['label'],
                'routingMode'   => $row['mode'],
                'properties'    => $row['properties'],
                'previewImage'  => $row['preview_image'],
                'photogallery'  => $row['photogallery'],
                'videogallery'  => $row['videogallery'],
                'waypoints'     => $row['waypoints'],
            ];
        }
    }

    /**
     * @param array $rows
     *
     * @return void
     */
    public function thePlacemark(array $rows): void
    {
        foreach ($rows as $row) {
            $this->structure[] = [
                'id'           => (int) $row['id'],
                'label'        => $row['label'],
                'routingMode'  => $row['mode'],
                'properties'   => $row['properties'],
                'previewImage' => $row['preview_image'],
                'coords'       => Shared::getCoords($row['latitude'], $row['longitude']),
            ];
        }

    }
}
