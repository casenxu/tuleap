/**
 * Copyright (c) Enalean, 2021 - present. All Rights Reserved.
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
import { Styles } from "./styles";
import type { TimePeriod } from "../type";

export function getLeftForDate(date: Date, time_period: TimePeriod): number {
    let left = 0;
    let i = 1;
    while (i < time_period.units.length && time_period.units[i] < date) {
        left += Styles.TIME_UNIT_WIDTH_IN_PX;
        i++;
    }

    const current_unit = time_period.getBeginningOfNextNthUnit(date, 0);
    const next_unit = time_period.getBeginningOfNextNthUnit(date, 1);
    const ms_since_beginning_of_unit = date.getTime() - current_unit.getTime();
    const ms_in_the_unit = next_unit.getTime() - current_unit.getTime();
    left += (Styles.TIME_UNIT_WIDTH_IN_PX * ms_since_beginning_of_unit) / ms_in_the_unit;

    return Math.round(left);
}
