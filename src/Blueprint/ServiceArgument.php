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

namespace Yceruto\BundleFlex\Blueprint;

/**
 * Argument of a service definition, rendered in the chosen configuration format.
 *
 * Literal string values are escaped by the renderers ("%" becomes "%%", a leading "@"
 * becomes "@@" in YAML-shaped formats) so they are never mistaken for references.
 *
 * @phpstan-type ConfigScalar string|int|float|bool|null
 * @phpstan-type ConfigValue ConfigScalar|list<ConfigScalar>
 */
final readonly class ServiceArgument
{
    /**
     * @param ConfigValue $value
     */
    private function __construct(
        public ArgumentType $type,
        public string|int|float|bool|array|null $value,
    ) {
    }

    public static function service(string $id): self
    {
        return new self(ArgumentType::Service, $id);
    }

    public static function parameter(string $name): self
    {
        return new self(ArgumentType::Parameter, $name);
    }

    /**
     * @param ConfigValue $value
     */
    public static function value(string|int|float|bool|array|null $value): self
    {
        return new self(ArgumentType::Value, $value);
    }

    /**
     * The referenced service id or parameter name.
     */
    public function getReference(): string
    {
        if (ArgumentType::Value === $this->type || !\is_string($this->value)) {
            throw new \LogicException('Literal arguments have no reference.');
        }

        return $this->value;
    }
}
