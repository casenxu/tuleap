<?php
/**
 * Copyright (c) Enalean 2023 - Present. All Rights Reserved.
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

namespace Tuleap\Tracker\REST\Artifact\ChangesetValue;

use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\Artifact\ChangesetValue\ChangesetValuesContainer;
use Tuleap\Tracker\Artifact\ChangesetValue\InitialChangesetValuesContainer;
use Tuleap\Tracker\REST\v1\ArtifactValuesRepresentation;

interface BuildFieldsData
{
    /**
     * @param ArtifactValuesRepresentation[] $values
     */
    public function getFieldsDataOnCreate(array $values, \Tracker $tracker): InitialChangesetValuesContainer;

    /**
     * @param ArtifactValuesRepresentation[] $values
     */
    public function getFieldsDataOnUpdate(
        array $values,
        Artifact $artifact,
        \PFUser $submitter,
    ): ChangesetValuesContainer;
}
