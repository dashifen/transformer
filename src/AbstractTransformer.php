<?php

namespace Dashifen\Transformer;

abstract class AbstractTransformer implements TransformerInterface {
  /**
   * canTransform
   *
   * Returns true if this object can transform data identified by the field
   * label.
   *
   * @param string $field
   *
   * @return bool
   */
  public function canTransform (string $field): bool {
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
  abstract protected function getTransformationMethod(string $field): string;

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
  public function transform (string $field, $value) {

    // if we can transform data that's labeled by our field, then we'll pass
    // our value through the transformation method (using variable method
    // naming) and return the results.  otherwise, we just return the value
    // unchanged so that, if we can't transform it, not changes are made to
    // it.

    if ($this->canTransform($field)) {
      return is_array($value)
        ? $this->transformArray($field, $value)
        : $this->{$this->getTransformationMethod($field)}($value);
    }

    return $value;
  }

  /**
   * transformArray
   *
   * Passes each value within an array through a transformation based on the
   * field name.
   *
   * @param string $field
   * @param array  $values
   *
   * @return array
   */
  public function transformArray(string $field, array $values): array {

    // since what we want to do is loop over the $values array and apply the
    // same transformation on each of it's members returning the resulting
    // array, we're going to use array_map() since it does exactly that!

    return array_map([$this, $this->getTransformationMethod($field)], $values);
  }
}