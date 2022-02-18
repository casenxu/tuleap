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

namespace Tuleap\Docman\View\Admin;

use Tuleap\Event\Dispatchable;

final class DetectEnhancementOfDocmanInterface implements Dispatchable
{
    public const NAME = 'detectEnhancementOfDocmanInterface';

    private bool $is_enhanced = false;

    public function __construct(private \Project $project)
    {
    }

    public function docmanInterfaceIsEnhanced(): void
    {
        $this->is_enhanced = true;
    }

    public function isEnhanced(): bool
    {
        return $this->is_enhanced;
    }

    public function getProject(): \Project
    {
        return $this->project;
    }
}
