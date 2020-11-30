<?php

namespace Citsk\Models\Structure;

class DistrictStructure extends Structure
{

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theDistrict(array $rows): void
    {

        foreach ($rows as $row) {
            $this->structure[] = [
                "id"          => (int) $row['id'],
                "label"       => $row['label'],
                "regionalId" => (int) $row['regional_id'],
            ];
        }

    }

}
