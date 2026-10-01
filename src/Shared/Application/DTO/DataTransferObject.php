<?php

namespace Shared\Application\DTO;

abstract class DataTransferObject
{
    /**
     * Convert the DTO to an array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Create a DTO instance from an array of attributes.
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->{$key} = $value;
            }
        }
        return $instance;
    }
}