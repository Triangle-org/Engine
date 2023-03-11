<?php

/**
 * @package     Triangle Engine
 * @link        https://github.com/Triangle-org/Engine
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

namespace support\mongodb\Relations;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\MorphTo as EloquentMorphTo;

class MorphTo extends EloquentMorphTo
{
    /**
     * @inheritdoc
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            // For belongs to relationships, which are essentially the inverse of has one
            // or has many relationships, we need to actually query on the primary key
            // of the related models matching on the foreign key that's on a parent.
            $this->query->where($this->getOwnerKey(), '=', $this->parent->{$this->foreignKey});
        }
    }

    /**
     * @inheritdoc
     */
    protected function getResultsByType($type)
    {
        $instance = $this->createModelByType($type);

        $key = $instance->getKeyName();

        $query = $instance->newQuery();

        return $query->whereIn($key, $this->gatherKeysByType($type, $instance->getKeyType()))->get();
    }

    /**
     * Get the owner key with backwards compatible support.
     *
     * @return string
     */
    public function getOwnerKey()
    {
        return property_exists($this, 'ownerKey') ? $this->ownerKey : $this->otherKey;
    }

    /**
     * Get the name of the "where in" method for eager loading.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @return string
     */
    protected function whereInMethod(EloquentModel $model, $key)
    {
        return 'whereIn';
    }
}
