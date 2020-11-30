<?php
namespace Citsk\Library;

trait GeoFilter
{

    /**
     * @return array
     */
    public function getSwitcherValue(): array
    {
        $i = 1;

        if (isset($_POST['slider'])) {
            $i = count($_POST['slider']) + 1;
        }

        $result = [
            "inner" => true,
        ];

        foreach ($_POST['switcher'] as $key => $value) {
            $metaCode = Shared::toSnakeCase($key);
            $value    = boolval($value) ? '1' : '0';

            $result["geo_property gp{$i}"] = "gp{$i}.geo_id = geo.id";
            $result["geo_meta gm{$i}"]     = "gm{$i}.id = gp{$i}.meta_id AND gm{$i}.code = '$metaCode' AND gp{$i}.value = '{$value}'";
            $i++;
        }

        return $result;

    }

    /**
     * @return array
     */
    public function getSliderValue(): array
    {
        $i = 1;

        $result = [
            "inner" => true,
        ];

        foreach ($_POST['slider'] as $key => $value) {
            $metaCode = Shared::toSnakeCase($key);

            $result["geo_property gp{$i}"] = "gp{$i}.geo_id = geo.id";
            $result["geo_meta gm{$i}"]     = "gm{$i}.id = gp{$i}.meta_id AND gm{$i}.code = '$metaCode' AND gp{$i}.value <= {$value}";
            $i++;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getCategoryValue(): string
    {

        $result = $_POST['category'][0];

        if (count($_POST['category']) >= 1) {
            $_POST['category'] = array_splice($_POST['category'], 1);

            foreach ($_POST['category'] as $id) {
                $result .= " OR cat.category_id = $id";
            }

        } else {
            $result = $_POST['category'][0];
        }

        return $result;
    }
}
