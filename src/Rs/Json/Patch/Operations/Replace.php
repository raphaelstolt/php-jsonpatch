<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\Operation;
use Rs\Json\Pointer;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class Replace extends Operation
{
    /**
     * Used for bitmap operations to find out if allowed or not
     *
     * @const int
     */
    const APPLY = 16;

    /**
     * @param \stdClass $operation
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @throws \RuntimeException
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
     *
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
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @throws \Rs\Json\Pointer\InvalidJsonException
     * @throws \Rs\Json\Pointer\InvalidPointerException
     * @throws \Rs\Json\Pointer\NonWalkableJsonException
     * @throws \RuntimeException
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

        $targetDocument = json_decode($targetDocument);

        $this->replace(
            $targetDocument,
            $this->getPointerParts(),
            $this->getValue()
        );

        return json_encode($targetDocument, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array|\stdClass $json         The json_decode'd Json structure.
     * @param array           $pointerParts The parts of the fed pointer.
     * @param mixed           $value        The value to replace.
     */
    private function replace(&$json, array $pointerParts, $value = null)
    {
        $pointerPart = array_shift($pointerParts);

        if (is_string($this->getValue())) {
            $value = json_decode($this->getValue());
        }

        if (!is_array($value) && !is_object($value)) {
            $value = $this->getValue();
        }

        if (is_object($json) && array_key_exists($pointerPart, get_object_vars($json))) {
            if (count($pointerParts) === 0) {
                $json->{$pointerPart} = $value;
            } else {
                $this->replace(
                    $json->{$pointerPart},
                    $pointerParts,
                    $value
                );
            }
        } elseif ($pointerPart === Pointer::LAST_ARRAY_ELEMENT_CHAR && is_array($json)) {
            $json[count($json) - 1] = $value;
        } elseif (is_array($json) && isset($json[$pointerPart])) {
            if (count($pointerParts) === 0) {
                $json[$pointerPart] = $value;
            } else {
                $this->replace(
                    $json[$pointerPart],
                    $pointerParts,
                    $value
                );
            }
        }
    }
}
