<?php
/**
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
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
 */

declare(strict_types=1);

namespace Tuleap\MediawikiStandalone\Configuration;

use Tuleap\Cryptography\ConcealedString;

/**
 * @psalm-immutable
 */
final class LocalSettingsRepresentation
{
    private const MEDIAWIKI_PHP_CLI = '/opt/remi/php74/root/usr/bin/php';

    public string $php_cli_path = self::MEDIAWIKI_PHP_CLI;

    public function __construct(
        public ConcealedString $pre_shared_key,
        public string $https_url,
        public string $oauth2_client_id,
        public ConcealedString $oauth2_client_secret,
    ) {
    }
}