<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operation;
use Rs\Json\Pointer;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class Replace extends Operation
{
    /**
     * @param \stdClass $operation
     */
    public function __construct(\stdClass $operation)
    {
        $this->assertMandatories($operation);
        parent::__construct('replace', $operation);
    }

    /**
     * Guard the mandatory operation property
     *
     * @param  \stdClass $operation The operation structure.
     * @throws Rs\Json\Patch\InvalidOperationException
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
     * @return string
     */
    public function perform($targetDocument)
    {
        $pointer = new Pointer($targetDocument);
        try {
            $get = $pointer->get($this->getPath());
        } catch (NonexistentValueReferencedException $e) {
            return $targetDocument;
        }

        $targetDocument = json_decode($targetDocument, true);

        $this->replace(
            $targetDocument,
            $this->getPointerParts(),
            $this->getValue()
        );

        return json_encode($targetDocument);
    }

    /**
     * @param array $json         The json_decode'd Json structure.
     * @param array $pointerParts The parts of the fed pointer.
     * @param mixed $value        The value to replace.
     */
    private function replace(array &$json, array $pointerParts, $value = null)
    {
        $pointerPart = array_shift($pointerParts);

        if (is_string($this->getValue())) {
            $value = json_decode($this->getValue(), true);
        }

        if (!is_array($value)) {
            $value = $this->getValue();
        }

        if (array_key_exists($pointerPart, $json)) {
            if (count($pointerParts) === 0) {
                $json[$pointerPart] = $value;
            } else {
                $this->replace(
                    $json[$pointerPart],
                    $pointerParts,
                    $value
                );
            }
        } elseif ($pointerPart === Pointer::LAST_ARRAY_ELEMENT_CHAR && is_array($json)) {
            $json[count($json) - 1] = $value;
        }
    }
}
