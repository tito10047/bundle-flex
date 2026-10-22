<?php

declare(strict_types=1);

namespace MyVendor\EverythingYamlBundle\Exception;

/**
 * Implemented by every exception thrown by MyVendorEverythingYamlBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
