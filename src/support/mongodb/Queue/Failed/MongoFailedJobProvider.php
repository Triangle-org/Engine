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

namespace support\mongodb\Queue\Failed;

use Carbon\Carbon;
use Illuminate\Queue\Failed\DatabaseFailedJobProvider;

class MongoFailedJobProvider extends DatabaseFailedJobProvider
{
    /**
     * Log a failed job into storage.
     *
     * @param string $connection
     * @param string $queue
     * @param string $payload
     * @param  \Exception  $exception
     * @return void
     */
    public function log($connection, $queue, $payload, $exception)
    {
        $failed_at = Carbon::now()->getTimestamp();

        $exception = (string) $exception;

        $this->getTable()->insert(compact('connection', 'queue', 'payload', 'failed_at', 'exception'));
    }

    /**
     * Get a list of all of the failed jobs.
     *
     * @return object[]
     */
    public function all()
    {
        $all = $this->getTable()->orderBy('_id', 'desc')->get()->all();

        $all = array_map(function ($job) {
            $job['id'] = (string) $job['_id'];

            return (object) $job;
        }, $all);

        return $all;
    }

    /**
     * Get a single failed job.
     *
     * @param mixed $id
     * @return object
     */
    public function find($id)
    {
        $job = $this->getTable()->find($id);

        if (!$job) {
            return;
        }

        $job['id'] = (string) $job['_id'];

        return (object) $job;
    }

    /**
     * Delete a single failed job from storage.
     *
     * @param mixed $id
     * @return bool
     */
    public function forget($id)
    {
        return $this->getTable()->where('_id', $id)->delete() > 0;
    }
}
