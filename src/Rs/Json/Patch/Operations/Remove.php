<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\Operation;
use Rs\Json\Pointer;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class Remove extends Operation
{
    /**
     * Used for bitmap operations to find out if allowed or not
     *
     * @const int
     */
    const APPLY = 8;

    /**
     * @param \stdClass $operation
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @throws \RuntimeException
     */
    public function __construct(\stdClass $operation)
    {
        $this->assertMandatories($operation);
        parent::__construct('remove', $operation);
    }

    /**
     * Guard the mandatory operation properties
     *
     * @param \stdClass $operation The operation structure.
     */
    protected function assertMandatories(\stdClass $operation)
    {
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
            $pointer->get($this->getPath());
        } catch (NonexistentValueReferencedException $e) {
            return $targetDocument;
        }

        $targetDocument = json_decode($targetDocument);
        $this->remove($targetDocument, $this->getPointerParts());

        return json_encode($targetDocument, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array|\stdClass $json         The json_decode'd Json structure.
     * @param array           $pointerParts The parts of the fed pointer.
     */
    private function remove(&$json, array $pointerParts)
    {
        $pointerPart = array_shift($pointerParts);

        if (is_array($json) && isset($json[$pointerPart])) {
            if (count($pointerParts) === 0) {
                unset($json[$pointerPart]);
                if (ctype_digit($pointerPart)) {
                    $json = array_values($json);
                }
            } else {
                $this->remove(
                    $json[$pointerPart],
                    $pointerParts
                );
            }
        } elseif (is_object($json) && isset($json->{$pointerPart})) {
            if (count($pointerParts) === 0) {
                unset($json->{$pointerPart});
            } else {
                $this->remove(
                    $json->{$pointerPart},
                    $pointerParts
                );
            }
        } elseif ($pointerPart === Pointer::LAST_ARRAY_ELEMENT_CHAR && is_array($json)) {
            unset($json[count($json) - 1]);
        } else {
            if (null === $pointerPart) {
                $json = new \stdClass();
            } elseif (is_object($json)) {
                unset($json->{$pointerPart});
            } else {
                unset($json[$pointerPart]);
            }
        }
    }
}
