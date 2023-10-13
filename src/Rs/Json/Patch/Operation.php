<?php
namespace Rs\Json\Patch;

abstract class Operation
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|array
     */
    protected $path;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param  string     $op
     * @param  \stdClass  $operation
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @throws \RuntimeException
     */
    public function __construct($op, \stdClass $operation)
    {
        if (!\class_exists('Rs\\Json\\Pointer')) {
            $exceptionMessage = 'Unable to create JSON patch operation as '
                . 'Json Pointer is not installed';
            throw new \RuntimeException($exceptionMessage);
        }

        if (!\property_exists($operation, 'path')) {
            $exceptionMessage = \sprintf(
                "No path property set for patch operation '%s'",
                \json_encode($operation)
            );
            throw new InvalidOperationException($exceptionMessage);
        }
        $this->name = $op;
        $this->path = $operation->path;
        $this->value = \property_exists($operation, 'value') ? $operation->value : null;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (\is_array($this->path)) {
            return '/'.\implode('/', $this->path);
        }
        return $this->path;
    }

    /**
     * @return array
     */
    protected function getPointerParts()
    {
        return \array_slice(
            \array_map('urldecode', \explode('/', \trim($this->getPath()))),
            1
        );
    }

    abstract public function perform(mixed $targetDocument): mixed;

    /**
     * Guard the mandatory operation properties
     *
     * @param  \stdClass $operation The operation structure.
     *
     * @throws \Rs\Json\Patch\InvalidOperationException
     * @return void
     */
    abstract protected function assertMandatories(\stdClass $operation);
}
