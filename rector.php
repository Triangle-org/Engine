<?php
/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright © 2016-2026 Localzet Group. All rights reserved.
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as published
 *              by the Free Software Foundation, either version 3 of the License, or
 *              (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 *              For any questions, please contact <support@localzet.com>
 */

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets()
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        // SetList::STRICT_BOOLEANS,
        SetList::NAMING,
        SetList::RECTOR_PRESET,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        // SetList::CARBON,
    ])
    ->withSkip([
        RemoveNullPropertyInitializationRector::class,
        RemoveUnusedPrivatePropertyRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,
        LocallyCalledStaticMethodToNonStaticRector::class,
        SplitDoubleAssignRector::class,
    ]);
