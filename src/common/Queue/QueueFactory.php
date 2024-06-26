<?php
/**
 * Copyright (c) Enalean, 2017 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Tuleap\Queue;

use ForgeConfig;
use Psr\Log\LoggerInterface;
use Tuleap\DB\CheckThereIsAnOngoingTransaction;
use Tuleap\DB\DBFactory;
use Tuleap\DB\DBTransactionExecutorWithConnection;
use Tuleap\Queue\DB\DBPersistentQueue;
use Tuleap\Queue\DB\DBPersistentQueueDAO;
use Tuleap\Queue\Redis\BackOffDelayFailedMessage;
use Tuleap\Redis\ClientFactory as RedisClientFactory;

class QueueFactory
{
    public const REDIS = 'redis';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CheckThereIsAnOngoingTransaction $transaction_checker,
    ) {
    }

    /**
     * @throws NoQueueSystemAvailableException
     */
    public function getPersistentQueue(string $queue_name, string $favor = ''): PersistentQueue
    {
        if ((int) ForgeConfig::getFeatureFlag(DBPersistentQueue::FEATURE_FLAG) === 1) {
            return new DBPersistentQueue(
                $queue_name,
                $this->logger,
                new DBPersistentQueueDAO(),
                new DBTransactionExecutorWithConnection(DBFactory::getMainTuleapDBConnection()),
            );
        }
        if (RedisClientFactory::canClientBeBuiltFromForgeConfig()) {
            return new PersistentQueueNoTransactionWrapper(
                new Redis\RedisPersistentQueue(
                    $this->logger,
                    new BackOffDelayFailedMessage(
                        $this->logger,
                        /**
                         * @psalm-param positive-int|0 $time_to_sleep
                         */
                        static function (int $time_to_sleep): void {
                            sleep($time_to_sleep);
                        }
                    ),
                    $queue_name,
                ),
                $this->transaction_checker
            );
        }
        if ($favor === self::REDIS) {
            throw new NoQueueSystemAvailableException();
        }
        return new Noop\PersistentQueue();
    }
}
