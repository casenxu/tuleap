<?php
/**
 * Copyright (c) Enalean, 2019. All Rights Reserved.
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

namespace Tuleap\Baseline\Factory;

use DateTime;
use PFUser;
use Tracker_Artifact;
use Tuleap\Baseline\Baseline;

class BaselineBuilder
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var Tracker_Artifact */
    private $milestone;

    /** @var DateTime */
    private $snapshot_date;

    /** @var PFUser */
    private $author;

    public function id(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function milestone(Tracker_Artifact $milestone): self
    {
        $this->milestone = $milestone;
        return $this;
    }

    public function snapshotDate(DateTime $snapshot_date): self
    {
        $this->snapshot_date = $snapshot_date;
        return $this;
    }

    public function author(PFUser $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function build()
    {
        return new Baseline(
            $this->id,
            $this->name,
            $this->milestone,
            $this->snapshot_date,
            $this->author
        );
    }
}
