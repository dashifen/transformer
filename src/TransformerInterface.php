<?php

namespace Dashifen\Transformer;

interface TransformerInterface {
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
}