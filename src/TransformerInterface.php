<?php

namespace Dashifen\Transformer;

interface TransformerInterface
{
    /**
     * canTransform
     *
     * Returns true if this object can transform data identified by the field
     *
     * @param string $field
     *
     * @return bool
     */
    public function canTransform (string $field): bool;
    
    /**
     * transform
     *
     * Passed the value through a transformation based on the field name.
     *
     * @param string $field
     * @param mixed  $value
     *
     * @return mixed
     */
    public function transform (string $field, $value);
    
    /**
     * transformArray
     *
     * Passes each value within an array through a transformation based on the
     * field name.  Return is mixed in case our transformation includes the use
     * of array_reduce or other behaviors that might not leave the array intact
     * as an array.
     *
     * @param string $field
     * @param array  $values
     *
     * @return mixed
     */
    public function transformArray (string $field, array $values);
}
