<?php
/**
 * Copyright (c) Enalean, 2023 - present. All Rights Reserved.
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

namespace Tuleap\Tracker\Test\Stub;

use Tuleap\Tracker\Action\VerifyUserGroupFieldsAreCompatible;
use Tuleap\Tracker\Artifact\Artifact;

final class VerifyUserGroupFieldsAreCompatibleStub implements VerifyUserGroupFieldsAreCompatible
{
    private function __construct(private readonly bool $are_fields_compatible)
    {
    }

    public static function withCompatibleField(): self
    {
        return new self(true);
    }

    public static function withoutCompatibleField(): self
    {
        return new self(false);
    }

    public function areUserGroupFieldsCompatible(\Tracker_FormElement_Field_List $source_field, \Tracker_FormElement_Field_List $target_field, Artifact $artifact): bool
    {
        return $this->are_fields_compatible;
    }
}