<?php

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/localzet/FrameX      FrameX Project v1-2
 * @link        https://github.com/Triangle-org/Engine  Triangle Engine v2+
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2018-2023 Localzet Group
 * @license     https://www.gnu.org/licenses/agpl AGPL-3.0 license
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as
 *              published by the Free Software Foundation, either version 3 of the
 *              License, or (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace support\telegram\Objects;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class BaseObject.
 *
 * @mixin Collection
 */
abstract class BaseObject extends Collection
{
    /**
     * Builds collection entity.
     *
     * @param array|mixed $data
     */
    public function __construct($data)
    {
        parent::__construct($this->getRawResult($data));
    }

    /**
     * Property relations.
     *
     * @return array
     */
    abstract public function relations();

    /**
     * Magically access collection data.
     *
     * @param $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->getPropertyValue($property);
    }

    /**
     * Magically map to an object class (if exists) and return data.
     *
     * @param string $property Name of the property or relation.
     * @param mixed $default Default value or \Closure that returns default value.
     *
     * @return mixed
     */
    protected function getPropertyValue($property, $default = null)
    {
        $property = Str::snake($property);
        if (!$this->offsetExists($property)) {
            return value($default);
        }

        $value = $this->items[$property];

        $relations = $this->relations();
        if (isset($relations[$property])) {
            return $this->getRelationValue($property, $value);
        }

        /** @var BaseObject $class */
        $class = 'support\telegram\Objects\\' . Str::studly($property);

        if (class_exists($class)) {
            return $class::make($value);
        }

        if (is_array($value)) {
            return TelegramObject::make($value);
        }

        return $value;
    }

    /**
     * @param string $relationName
     * @param array $relationRawData
     * @return array|\Illuminate\Support\Enumerable|\Illuminate\Support\Traits\EnumeratesValues|\support\telegram\Objects\BaseObject
     */
    protected function getRelationValue(string $relationName, iterable $relationRawData)
    {
        /** @var class-string<\support\telegram\Objects\BaseObject>|list<class-string<\support\telegram\Objects\BaseObject>> $relation */
        $relation = $this->relations()[$relationName];

        if (is_string($relation)) {
            if (!class_exists($relation)) {
                throw new \InvalidArgumentException("Could not load “{$relationName}” relation: class “{$relation}” not found.");
            }
            return $relation::make($relationRawData);
        }

        $isOneToManyRelation = is_array($relation);
        if ($isOneToManyRelation) {
            /** @var class-string<\support\telegram\Objects\BaseObject> $clasString */
            $clasString = $relation[0];
            $relatedObjects = Collection::make(); // @todo array type can be used in v4
            foreach ($relationRawData as $singleObjectRawData) {
                $relatedObjects[] = $clasString::make($singleObjectRawData);
            }
            return $relatedObjects;
        }

        throw new \InvalidArgumentException("Unknown type of the relationship data for the “{$relationName}” relation.");
    }

    /**
     * Get an item from the collection by key.
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return mixed|static
     */
    public function get($key, $default = null)
    {
        $value = parent::get($key, $default);

        if (null !== $value && is_array($value)) {
            return $this->getPropertyValue($key, $default);
        }

        return $value;
    }

    /**
     * Returns raw response.
     *
     * @return array|mixed
     */
    public function getRawResponse()
    {
        return $this->items;
    }

    /**
     * Returns raw result.
     *
     * @param $data
     *
     * @return mixed
     */
    public function getRawResult($data)
    {
        return data_get($data, 'result', $data);
    }

    /**
     * Get Status of request.
     *
     * @return mixed
     */
    public function getStatus()
    {
        return data_get($this->items, 'ok', false);
    }

    /**
     * Detect type based on fields.
     *
     * @return string|null
     */
    public function objectType(): ?string
    {
        return null;
    }

    /**
     * Determine if the object is of given type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function isType($type)
    {
        if ($this->offsetExists($type)) {
            return true;
        }

        return $this->objectType() === $type;
    }

    /**
     * Determine the type by given types.
     *
     * @param array $types
     *
     * @return string|null
     */
    protected function findType(array $types): ?string
    {
        return $this->keys()
            ->intersect($types)
            ->pop();
    }

    /**
     * Magic method to get properties dynamically.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!Str::startsWith($name, 'get')) {
            return false;
        }
        $property = substr($name, 3);

        return $this->getPropertyValue($property);
    }
}
