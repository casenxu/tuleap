/*
 * Copyright (c) Enalean, 2022 - present. All Rights Reserved.
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

import type { CommentType, PullRequestEventType } from "@tuleap/plugin-pullrequest-constants";
import type { TYPE_EVENT_PULLREQUEST_ACTION } from "@tuleap/plugin-pullrequest-constants";
import type {
    ActionOnPullRequestEvent,
    CommentOnFile,
    GlobalComment,
} from "@tuleap/plugin-pullrequest-rest-api-types";

export interface HelpRelativeDatesDisplay {
    getRelativeDatePreference: () => string;
    getRelativeDatePlacement: () => string;
    getUserLocale: () => string;
    getFormatDateUsingPreferredUserFormat: (date: string) => string;
}

export type SupportedTimelineItemTypes =
    | CommentType
    | Extract<PullRequestEventType, typeof TYPE_EVENT_PULLREQUEST_ACTION>;
export type SupportedTimelineItem = GlobalComment | CommentOnFile | ActionOnPullRequestEvent;