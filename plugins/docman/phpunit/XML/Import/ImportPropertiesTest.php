<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\Docman\XML\Import;

use PHPUnit\Framework\TestCase;

class ImportPropertiesTest extends TestCase
{
    public function testWiki(): void
    {
        $create_date = (new \DateTimeImmutable())->setTimestamp(1234567890);
        $update_date = (new \DateTimeImmutable())->setTimestamp(1324567890);

        $properties = ImportProperties::buildWiki('title', 'description', 'wiki page name', $create_date, $update_date);
        $this->assertEquals('title', $properties->getTitle());
        $this->assertEquals('description', $properties->getDescription());
        $this->assertEquals($create_date, $properties->getCreateDate());
        $this->assertEquals($update_date, $properties->getUpdateDate());
        $this->assertEquals(null, $properties->getLinkUrl());
        $this->assertEquals('wiki page name', $properties->getWikiPage());
        $this->assertEquals(PLUGIN_DOCMAN_ITEM_TYPE_WIKI, $properties->getItemTypeId());
    }

    public function testLink(): void
    {
        $create_date = (new \DateTimeImmutable())->setTimestamp(1234567890);
        $update_date = (new \DateTimeImmutable())->setTimestamp(1324567890);

        $properties = ImportProperties::buildLink('title', 'description', 'link url', $create_date, $update_date);
        $this->assertEquals('title', $properties->getTitle());
        $this->assertEquals('description', $properties->getDescription());
        $this->assertEquals($create_date, $properties->getCreateDate());
        $this->assertEquals($update_date, $properties->getUpdateDate());
        $this->assertEquals('link url', $properties->getLinkUrl());
        $this->assertEquals(null, $properties->getWikiPage());
        $this->assertEquals(PLUGIN_DOCMAN_ITEM_TYPE_LINK, $properties->getItemTypeId());
    }

    public function testEmpty(): void
    {
        $create_date = (new \DateTimeImmutable())->setTimestamp(1234567890);
        $update_date = (new \DateTimeImmutable())->setTimestamp(1324567890);

        $properties = ImportProperties::buildEmpty('title', 'description', $create_date, $update_date);
        $this->assertEquals('title', $properties->getTitle());
        $this->assertEquals('description', $properties->getDescription());
        $this->assertEquals($create_date, $properties->getCreateDate());
        $this->assertEquals($update_date, $properties->getUpdateDate());
        $this->assertEquals(null, $properties->getLinkUrl());
        $this->assertEquals(null, $properties->getWikiPage());
        $this->assertEquals(PLUGIN_DOCMAN_ITEM_TYPE_EMPTY, $properties->getItemTypeId());
    }

    public function testFolder(): void
    {
        $create_date = (new \DateTimeImmutable())->setTimestamp(1234567890);
        $update_date = (new \DateTimeImmutable())->setTimestamp(1324567890);

        $properties = ImportProperties::buildFolder('title', 'description', $create_date, $update_date);
        $this->assertEquals('title', $properties->getTitle());
        $this->assertEquals('description', $properties->getDescription());
        $this->assertEquals($create_date, $properties->getCreateDate());
        $this->assertEquals($update_date, $properties->getUpdateDate());
        $this->assertEquals(null, $properties->getLinkUrl());
        $this->assertEquals(null, $properties->getWikiPage());
        $this->assertEquals(PLUGIN_DOCMAN_ITEM_TYPE_FOLDER, $properties->getItemTypeId());
    }

    public function testFile(): void
    {
        $create_date = (new \DateTimeImmutable())->setTimestamp(1234567890);
        $update_date = (new \DateTimeImmutable())->setTimestamp(1324567890);

        $properties = ImportProperties::buildFile('title', 'description', $create_date, $update_date);
        $this->assertEquals('title', $properties->getTitle());
        $this->assertEquals('description', $properties->getDescription());
        $this->assertEquals($create_date, $properties->getCreateDate());
        $this->assertEquals($update_date, $properties->getUpdateDate());
        $this->assertEquals(null, $properties->getLinkUrl());
        $this->assertEquals(null, $properties->getWikiPage());
        $this->assertEquals(PLUGIN_DOCMAN_ITEM_TYPE_FILE, $properties->getItemTypeId());
    }

    public function testEmbbeded(): void
    {
        $create_date = (new \DateTimeImmutable())->setTimestamp(1234567890);
        $update_date = (new \DateTimeImmutable())->setTimestamp(1324567890);

        $properties = ImportProperties::buildEmbedded('title', 'description', $create_date, $update_date);
        $this->assertEquals('title', $properties->getTitle());
        $this->assertEquals('description', $properties->getDescription());
        $this->assertEquals($create_date, $properties->getCreateDate());
        $this->assertEquals($update_date, $properties->getUpdateDate());
        $this->assertEquals(null, $properties->getLinkUrl());
        $this->assertEquals(null, $properties->getWikiPage());
        $this->assertEquals(PLUGIN_DOCMAN_ITEM_TYPE_EMBEDDEDFILE, $properties->getItemTypeId());
    }
}
