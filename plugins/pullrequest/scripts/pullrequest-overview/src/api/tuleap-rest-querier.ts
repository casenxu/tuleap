/*
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

import { getJSON, getAllJSON, uri } from "@tuleap/fetch-result";
import type { PullRequest, User, TimelineItem } from "@tuleap/plugin-pullrequest-rest-api-types";
import type { Fault } from "@tuleap/fault";
import type { ResultAsync } from "neverthrow";

interface TimelineItemsCollection {
    collection: ReadonlyArray<TimelineItem>;
}

export const fetchPullRequestInfo = (pullrequest_id: string): ResultAsync<PullRequest, Fault> => {
    return getJSON(uri`/api/v1/pull_requests/${encodeURIComponent(pullrequest_id)}`);
};

export const fetchUserInfo = (user_id: number): ResultAsync<User, Fault> => {
    return getJSON(uri`/api/v1/users/${encodeURIComponent(user_id)}`);
};

export const fetchPullRequestTimelineItems = (
    pullrequest_id: string
): ResultAsync<readonly TimelineItem[], Fault> => {
    return getAllJSON<TimelineItemsCollection, TimelineItem>(
        uri`/api/v1/pull_requests/${encodeURIComponent(pullrequest_id)}/timeline`,
        {
            params: { limit: 50, offset: 0 },
            getCollectionCallback: (
                payload: TimelineItemsCollection
            ): ReadonlyArray<TimelineItem> => payload.collection,
        }
    );
};