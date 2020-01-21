<?php

namespace Dashifen\Transformer;

use ReflectionMethod;
use ReflectionException;

abstract class AbstractTransformer implements TransformerInterface
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
    public function canTransform (string $field): bool
    {
        return method_exists($this, $this->getTransformationMethod($field));
    }
    
    
    /**
     * getTransformationMethod
     *
     * Returns the name of a method in this class that is used to validate
     * information labeled by $field.
     *
     * @param string $field
     *
     * @return string
     */
    abstract protected function getTransformationMethod (string $field): string;
    
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
    public function transform (string $field, $value)
    {
        if ($this->canTransform($field)) {
            
            // if we can transform data that's labeled by our field, then we'll
            // pass that value through the identified method.  if $value is an
            // array, we can pass it over to the method below to transform each
            // of its values.
            
            return !is_array($value)
                ? $this->{$this->getTransformationMethod($field)}($value)
                : $this->transformArray($field, $value);
        }
        
        // otherwise, we return the original value unharmed.  this allows us to
        // call this method in a loop passing it each field/value pair and only
        // altering the ones for which we actually have a transformation
        // method.
        
        return $value;
    }
    
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
    public function transformArray (string $field, array $values)
    {
        // while it's likely that we're here because someone called the
        // transform method above, we'll want to be sure to double-check that
        // we can do so in case someone called this one directly.  then, if we
        // can do so, we can use array_map to apply our transformation to each
        // value within the array or the original values without alteration.
        
        if ($this->canTransform($field)) {
            
            // if we're here, then we can transform.  but, there are two ways
            // to handle array transformations:  the array in its entirety or
            // each value within the array separately.  we won't know which one
            // without "asking."  in the past, reflections were expensive, but
            // recent PHP versions has made them far less so, especially for
            // objects already in memory.
            
            try {
                $transformer = $this->getTransformationMethod($field);
                $reflection = new ReflectionMethod($this, $transformer);
                $firstParam = $reflection->getParameters()[0];
                
                // now, if the first parameter to our transformation method is
                // not an array, we'll use array_map to send each value of it
                // over individually.  but, if it is, then we pass the whole
                // thing over to the transformer and let it take over.
                
                return !$firstParam->isArray()
                    ? array_map([$this, $transformer], $values)
                    : $this->{$transformer}($values);
            } catch (ReflectionException $exception) {
                
                // if we could not reflect, that's weird since we're reflecting
                // an object already in memory.  in other words:  this
                // shouldn't happen.  if it does, all we can do is raise an
                // error
                
                trigger_error('Unable to reflect', E_USER_ERROR);
            }
        }
        
        return $values;
    }
}
