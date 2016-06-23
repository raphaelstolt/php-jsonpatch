<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operation;
use Rs\Json\Pointer;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class Test extends Operation
{
    /**
     * Used for bitmap operations to find out if allowed or not
     *
     * @const int
     */
    const APPLY = 32;

    /**
     * @param \stdClass $operation
     */
    public function __construct(\stdClass $operation)
    {
        $this->assertMandatories($operation);
        parent::__construct('test', $operation);
    }

    /**
     * Guard the mandatory operation property
     *
     * @param  \stdClass $operation $operation The operation structure.
     * @throws \Rs\Json\Patch\InvalidOperationException
     */
    protected function assertMandatories(\stdClass $operation)
    {
        if (!property_exists($operation, 'value')) {
            throw new InvalidOperationException('Mandatory value property not set');
        }
    }

    /**
     * @param  string $targetDocument
     *
     * @return boolean
     */
    public function perform($targetDocument)
    {
        $pointer = new Pointer($targetDocument);

        try {
            $get = $pointer->get($this->getPath());
            // Pointer::get() method can return mixed result, we should force type to array for json string
            if ($this->isValidJsonString($get)) {
                $get = json_decode($get, true);
            }
        } catch (NonexistentValueReferencedException $e) {
            $get = null;
        }

        $value = is_object($this->getValue()) ? (array) $this->getValue() : $this->getValue();

        if (is_array($value) && is_array($get)) {
            return $this->arraysAreIdentical($value, $get);
        }

        return $get === $this->getValue();
    }

    /**
     * Check if string is a valid JSON string
     *
     * @param  string  $string
     *
     * @return boolean
     */
    private function isValidJsonString($string)
    {
        if (is_string($string) && strlen($string)) {
            // Decode and check last error
            $result = json_decode($string);
            return (json_last_error() === JSON_ERROR_NONE) && ($result != $string);
        }

        return false;
    }

    /**
     * @param array $value
     * @param array $get
     *
     * @return bool
     */
    private function arraysAreIdentical(array $value, array $get)
    {
        asort($get);
        asort($value);

        return json_encode($get, JSON_UNESCAPED_UNICODE) === json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
