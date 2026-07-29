<?php

namespace Benammi\ArrayAIO;

use RuntimeException;

/**
 * Thrown when a collection is expected to contain exactly one matching item, but none were found.
 */
class ItemNotFoundException extends RuntimeException
{
}
