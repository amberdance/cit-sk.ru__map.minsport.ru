<?php

namespace Citsk\Models\Structure;

use Citsk\Library\Shared;

class CommonStructure extends Structure
{

    public function abstractStructure(array $rows): void
    {

        foreach ($rows as $row) {
            $this->structure[] = [
                "id"    => (int) $row['id'],
                "label" => $row['label'] ?? $row['value'] ?? $row['title'] ?? null,
            ];
        }

    }

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theProperty(array $rows): void
    {

        foreach ($rows as $row) {
            $this->structure[] = [
                "label" => $row['label'],
                "value" => Shared::getDataType($row['data_type'], $row['value']),
                'code'  => $row['code'],
            ];
        }

    }

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theAdminProperty(array $rows): void
    {

        $index = 1;

        foreach ($rows as $row) {
            $this->structure[] = [
                "id"        => $index,
                "serviceId" => (int) $row['id'],
                "value"     => Shared::getDataType($row['data_type'], $row['value']),
                "label"     => $row['label'],
                "code"      => $row['code'],
            ];

            $index++;
        }

    }

    /**
     * @param array $rows
     *
     * @return void
     */
    public function thePhotogallery(array $rows): array
    {
        $result = [];

        if (!$rows) {
            return $result;
        }

        foreach ($rows as $row) {
            $this->structure[] = [
                'id'  => (int) $row['id'],
                'src' => $row['src'] ? API_URL . "/uploads/{$row['src']}" : null,
            ];
        }

        return $this->structure;
    }

    /**
     * @param array $rows
     *
     * @return void
     */
    public function theVideogallery(array $rows): void
    {
        $this->structure = $rows;
    }
}
