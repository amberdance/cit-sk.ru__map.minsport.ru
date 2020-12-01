<?php
namespace Citsk\Models\Structure;

use Exception;

class Structure
{

    /**
     * @var array
     */
    public $structure = [];

    /**
     * @param string|null $label
     * @param array $data
     */
    public function __construct(array $data, string $label = null)
    {
        $methodName = null;

        if (!$label) {
            $methodName = "abstractStructure";
        } else {
            $methodName = $this->getMethodName($label);
        }

        $this->handleStructure($methodName, $data);

    }

    /**
     * @param string $label
     * @param array $data
     *
     * @return void
     */
    private function handleStructure(string $methodName, array $data): array
    {

        if (!$data) {
            $this->structure = [];
        }

        call_user_func([$this, $methodName], $data);

        return $this->structure;

    }

    /**
     * @param null ?string
     *
     * @return string
     */
    private function getMethodName(?string $label = null): string
    {

        if (!$label) {
            return 'abstractStructure';
        }

        global $USER;

        $methodName = null;

        if ($USER->isAuthorized) {
            $methodName = str_replace("the", "theAdmin", $label);
        }

        if (method_exists($this, $methodName)) {
            return $methodName;

        } else {
            if (method_exists($this, $label)) {
                return $label;
            }
        }

        throw new Exception("Structure not found");

    }

}
