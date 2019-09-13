<?php

namespace Dashifen\Transformer;

abstract class AbstractTransformation implements TransformerInterface {
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

    return $this->canTransform($field)
      ? $this->{$this->getTransformationMethod($field)}($value)
      : $value;
  }
}