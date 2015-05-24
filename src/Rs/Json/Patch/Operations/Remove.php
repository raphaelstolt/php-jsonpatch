<?php
namespace Rs\Json\Patch\Operations;

use Rs\Json\Patch\Operation;
use Rs\Json\Pointer;
use Rs\Json\Pointer\NonexistentValueReferencedException;

class Remove extends Operation
{
    /**
     * @param \stdClass $operation
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
    protected function assertMandatories(\stdClass $operation) {}

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
        $this->remove($targetDocument, $this->getPointerParts());

        return json_encode($targetDocument);
    }

    /**
     * @param array $json         The json_decode'd Json structure.
     * @param array $pointerParts The parts of the fed pointer.
     */
    private function remove(array &$json, array $pointerParts)
    {
        $pointerPart = array_shift($pointerParts);

        if (isset($json[$pointerPart])) {
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
        } elseif ($pointerPart === Pointer::LAST_ARRAY_ELEMENT_CHAR && is_array($json)) {
            unset($json[count($json) - 1]);
        } else {
            unset($json[$pointerPart]);
        }
    }
}
