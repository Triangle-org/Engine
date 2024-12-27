<?php declare(strict_types=1);

/**
 * @package     Triangle Engine (FrameX Project)
 * @link        https://github.com/Triangle-org/Engine Triangle Engine (v2+)
 * @link        https://github.com/localzet-archive/FrameX-Public FrameX (v1-2)
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright (c) 2023-2024 Triangle Framework Team
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

namespace Triangle\Engine\Log;

use InvalidArgumentException;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\MongoDBFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

/**
 * Класс MongoDBHandler
 * Этот класс представляет собой обработчик, который записывает логи в базу данных MongoDB.
 * Он наследует от базового класса AbstractProcessingHandler.
 *
 * Пример использования:
 *
 *   $log = new \Monolog\Logger('application');
 *   $client = new \MongoDB\Client('mongodb://localhost:27017');
 *   $mongodb = new \Monolog\Handler\MongoDBHandler($client, 'logs', 'prod');
 *   $log->pushHandler($mongodb);
 *
 * В приведенном выше примере используется клиентская библиотека MongoDB PHP; однако также поддерживается
 * класс MongoDB\Driver\Handler из расширения ext-mongodb.
 *
 * @link https://github.com/Seldaek/monolog
 * @author      Ivan Zorin <creator@localzet.com>
 * @author      Jordi Boggiano <j.boggiano@seld.be>
 */
class MongoDBHandler extends AbstractProcessingHandler
{
    /** @var Collection $collection Коллекция MongoDB для записи логов. */
    private Collection $collection;

    /** @var Client|Manager $manager Клиент или менеджер MongoDB. */
    private Manager|Client $manager;

    /** @var string $namespace Пространство имен MongoDB. */
    private string $namespace;

    /**
     * Конструктор.
     *
     * @param Client|Manager $mongodb Клиент библиотеки MongoDB или драйвера.
     * @param string $database Имя базы данных.
     * @param string $collection Имя коллекции.
     * @param int $level Уровень логирования.
     * @param bool $bubble Булево значение, указывающее, должен ли обработчик позволить обработке следующим обработчикам.
     * @throws InvalidArgumentException Если переданный клиент не является экземпляром MongoDB\Client или MongoDB\Driver\Manager.
     */
    public function __construct($mongodb, string $database, string $collection, int $level = Logger::DEBUG, bool $bubble = true)
    {
        if (!($mongodb instanceof Client) && !($mongodb instanceof Manager)) {
            throw new InvalidArgumentException('MongoDB\Client or MongoDB\Driver\Manager instance required');
        }

        if ($mongodb instanceof Client) {
            $this->collection = $mongodb->selectCollection($database, $collection);
        } else {
            $this->manager = $mongodb;
            $this->namespace = $database . '.' . $collection;
        }

        parent::__construct($level, $bubble);
    }

    /**
     * Записать лог.
     *
     * @param array $record Запись лога.
     */
    protected function write(array $record): void
    {
        if (isset($this->collection)) {
            $this->collection->insertOne($record['formatted']);
        }

        if (isset($this->manager, $this->namespace)) {
            $bulkWrite = new BulkWrite;
            $bulkWrite->insert($record["formatted"]);
            $this->manager->executeBulkWrite($this->namespace, $bulkWrite);
        }
    }

    /**
     * Получить форматтер по умолчанию.
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new MongoDBFormatter;
    }
}
