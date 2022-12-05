<?php
/**
 * Copyright (c) Enalean, 2022 - Present. All Rights Reserved.
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


namespace Tuleap\MediawikiStandalone\Permissions\Admin;

use Psr\Http\Message\ServerRequestInterface;

final class PermissionsFromRequestExtractor
{
    /**
     * @param int[] $readers_ugroup_ids
     * @param int[] $writers_ugroup_ids
     */
    public function __construct(private array $readers_ugroup_ids, private array $writers_ugroup_ids)
    {
    }

    /**
     * @throws InvalidRequestException
     */
    public static function extractPermissionsFromRequest(ServerRequestInterface $request): self
    {
        return new self(
            self::extractFromRequest($request, 'readers'),
            self::extractFromRequest($request, 'writers'),
        );
    }

    /**
     * @return int[]
     */
    private static function extractFromRequest(ServerRequestInterface $request, string $key): array
    {
        $body = $request->getParsedBody();
        if (! is_array($body)) {
            throw new InvalidRequestException("Expected body to be an associative array");
        }

        if (! isset($body[$key])) {
            return [];
        }
        if (! is_array($body[$key])) {
            throw new InvalidRequestException("Expected $key to be an array");
        }

        return array_map(static fn(string $id) => (int) $id, $body[$key]);
    }

    /**
     * @return int[]
     */
    public function getReadersUgroupIds(): array
    {
        return $this->readers_ugroup_ids;
    }

    /**
     * @return int[]
     */
    public function getWritersUgroupIds(): array
    {
        return $this->writers_ugroup_ids;
    }
}