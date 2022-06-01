<?php
/*
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
 *
 */

declare(strict_types=1);

namespace Tuleap\Test\Stubs;

use Project_NotFoundException;
use Tuleap\Project\ProjectByIDFactory;

final class ProjectByIDFactoryStub implements ProjectByIDFactory
{
    private function __construct(private ?\Project $project)
    {
    }

    public static function buildWithoutProject(): self
    {
        return new self(null);
    }

    public static function buildWith(\Project $project): self
    {
        return new self($project);
    }

    public function getValidProjectById(int $project_id): \Project
    {
        if ($this->project && (int) $this->project->getID() === $project_id) {
            return $this->project;
        }
        throw new Project_NotFoundException();
    }
}