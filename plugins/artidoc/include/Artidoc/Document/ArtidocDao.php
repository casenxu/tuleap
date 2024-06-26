<?php
/**
 * Copyright (c) Enalean, 2024 - Present. All Rights Reserved.
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

namespace Tuleap\Artidoc\Document;

use ParagonIE\EasyDB\EasyDB;
use Tuleap\DB\DataAccessObject;

final class ArtidocDao extends DataAccessObject implements SearchArtidocDocument, SearchPaginatedRawSections, SaveSections
{
    public function searchById(int $id): ?array
    {
        return $this->getDB()->row(
            <<<EOS
            SELECT *
            FROM plugin_docman_item
            WHERE item_id = ?
              AND item_type = ?
              AND other_type = ?
              AND delete_date IS NULL
            EOS,
            $id,
            \Docman_Item::TYPE_OTHER,
            ArtidocDocument::TYPE,
        );
    }

    public function searchPaginatedRawSectionsById(int $id, int $limit, int $offset): PaginatedRawSections
    {
        return $this->getDB()->tryFlatTransaction(function (EasyDB $db) use ($id, $limit, $offset) {
            $rows = $db->run(
                <<<EOS
                SELECT artifact_id
                FROM plugin_artidoc_document
                WHERE item_id = ?
                ORDER BY `rank`
                LIMIT ? OFFSET ?
                EOS,
                $id,
                $limit,
                $offset,
            );

            $total = $db->cell('SELECT COUNT(*) FROM plugin_artidoc_document WHERE item_id = ?', $id);

            return new PaginatedRawSections($id, $rows, $total);
        });
    }

    public function cloneItem(int $source_id, int $target_id): void
    {
        $this->getDB()->run(
            <<<EOS
            INSERT INTO plugin_artidoc_document (item_id, artifact_id, `rank`)
            SELECT ?, artifact_id, `rank`
            FROM plugin_artidoc_document
            WHERE item_id = ?
            EOS,
            $target_id,
            $source_id,
        );
    }

    public function save(int $id, array $artifact_ids): void
    {
        $this->getDB()->tryFlatTransaction(static function (EasyDB $db) use ($id, $artifact_ids) {
            $db->run('DELETE FROM plugin_artidoc_document WHERE item_id = ?', $id);

            if (count($artifact_ids) > 0) {
                $rank = 0;
                $db->insertMany(
                    'plugin_artidoc_document',
                    array_map(
                        static function ($artifact_id) use ($id, &$rank) {
                            return ['item_id' => $id, 'artifact_id' => $artifact_id, 'rank' => $rank++];
                        },
                        $artifact_ids,
                    ),
                );
            }
        });
    }
}
