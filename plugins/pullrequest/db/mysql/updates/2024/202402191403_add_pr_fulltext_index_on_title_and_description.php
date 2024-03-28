<?php
/**
 * Copyright (c) Enalean, 2024 - present. All Rights Reserved.
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

//phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps
final class b202402191403_add_pr_fulltext_index_on_title_and_description extends \Tuleap\ForgeUpgrade\Bucket
{
    public function description()
    {
        return 'Add a fulltext index on title and description in the plugin_pullrequest_review table.';
    }

    public function up()
    {
        $this->api->addIndex(
            'plugin_pullrequest_review',
            'idx_pr_title',
            'ALTER TABLE plugin_pullrequest_review ADD FULLTEXT INDEX idx_pr_title(title);',
        );

        $this->api->addIndex(
            'plugin_pullrequest_review',
            'idx_pr_description',
            'ALTER TABLE plugin_pullrequest_review ADD FULLTEXT INDEX idx_pr_description(description);',
        );
    }
}
