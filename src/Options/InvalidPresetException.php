<?php

declare(strict_types=1);

/*
 * This file is part of the yceruto/bundle-flex package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\BundleFlex\Options;

/**
 * Thrown when a BUNDLE_FLEX_* environment variable has an invalid value.
 */
final class InvalidPresetException extends \InvalidArgumentException
{
    public static function fromInvalidValue(string $variable, \InvalidArgumentException $previous): self
    {
        return new self(\sprintf('Invalid value for the environment variable "%s": %s', $variable, $previous->getMessage()), 0, $previous);
    }
}
