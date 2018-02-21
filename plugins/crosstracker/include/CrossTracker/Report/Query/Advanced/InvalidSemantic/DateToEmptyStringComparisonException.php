<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
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

namespace Tuleap\CrossTracker\Report\Query\Advanced\InvalidSemantic;

use Tuleap\Tracker\Report\Query\Advanced\Grammar\Metadata;

class DateToEmptyStringComparisonException extends InvalidSemanticComparisonException
{
    public function __construct(Metadata $metadata)
    {
        $message = sprintf(
            dgettext("tuleap-crosstracker", "%s cannot be compared to the empty string with > operator."),
            $metadata->getName()
        );
        parent::__construct($message);
    }
}
