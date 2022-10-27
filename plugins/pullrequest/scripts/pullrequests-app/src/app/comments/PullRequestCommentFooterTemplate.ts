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

import { html } from "hybrids";
import type { UpdateFunction } from "hybrids";
import type { PullRequestComment } from "./PullRequestComment";
import { getReplyToCommentButtonText } from "../gettext-catalog";

export const getCommentFooter = (host: PullRequestComment): UpdateFunction<PullRequestComment> => {
    if (host.comment.type === "timeline-event") {
        return html``;
    }

    return html`
        <div class="pull-request-comment-footer" data-test="pull-request-comment-footer">
            <button
                type="button"
                class="tlp-button-small tlp-button-primary tlp-button-large tlp-button-outline disabled"
            >
                ${getReplyToCommentButtonText()}
            </button>
        </div>
    `;
};