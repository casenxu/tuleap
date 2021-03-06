<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
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

namespace Tuleap\Agiledashboard\FormElement;

use DateTime;
use TimePeriodWithoutWeekEnd;

class BurnupCacheDateRetriever
{
    public function getYesterday()
    {
        $date = new DateTime();
        $date->setTime(0, 0, 0);

        return $date->getTimestamp();
    }

    /**
     * @return int[]
     */
    public function getWorkedDaysToCacheForPeriod(TimePeriodWithoutWeekEnd $burnup_period, DateTime $yesterday)
    {
        $start_date = $this->getFirstDayToCache($burnup_period);
        $end_date   = $this->getLastDayToCache($burnup_period);

        $day = array();

        while ($start_date <= $end_date && $start_date <= $yesterday) {
            if ($burnup_period->isNotWeekendDay($start_date->getTimestamp())) {
                $day[] = $start_date->getTimestamp();
            }

            $this->addOneDayToDateTime($start_date);
        }

        return $day;
    }

    private function getFirstDayToCache(TimePeriodWithoutWeekEnd $burnup_period)
    {
        $start_date = new DateTime();
        $start_date->setTimestamp($burnup_period->getStartDate());
        $this->addOneDayToDateTime($start_date);

        return $start_date;
    }

    private function addOneDayToDateTime(DateTime $date)
    {
        $date->setTime(0, 0, 0);
        $date->modify('+1 day');

        return $date;
    }

    private function getLastDayToCache(TimePeriodWithoutWeekEnd $burnup_period)
    {
        $end_date = new DateTime();
        $end_date->setTimestamp($burnup_period->getEndDate());
        $this->addOneDayToDateTime($end_date);

        return $end_date;
    }
}
