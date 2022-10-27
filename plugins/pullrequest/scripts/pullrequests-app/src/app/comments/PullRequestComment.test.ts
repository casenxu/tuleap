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

import { selectOrThrow } from "@tuleap/dom";
import type { HostElement } from "./PullRequestComment";
import { PullRequestComment } from "./PullRequestComment";
import { PullRequestCommentPresenterStub } from "../../../tests/stubs/PullRequestCommentPresenterStub";
import { setCatalog } from "../gettext-catalog";

describe("PullRequestComment", () => {
    let target: ShadowRoot;

    beforeEach(() => {
        setCatalog({ getString: (msgid) => msgid });

        target = document.implementation
            .createHTMLDocument()
            .createElement("div") as unknown as ShadowRoot;
    });

    describe("Display", () => {
        it(`Given a not outdated inline comment, then it should have the right classes`, () => {
            const host = {
                comment: PullRequestCommentPresenterStub.buildInlineComment(),
            } as unknown as HostElement;
            const update = PullRequestComment.content(host);

            update(host, target);

            const root = selectOrThrow(target, "[data-test=pullrequest-comment]");

            expect(root.classList).toContain("pull-request-comment");
            expect(root.classList).toContain("is-inline-comment");
            expect(root.classList).toContain("inline-comment");
        });

        it(`Given an outdated inline comment, then it should have the right classes`, () => {
            const host = {
                comment: PullRequestCommentPresenterStub.buildInlineCommentOutdated(),
            } as unknown as HostElement;
            const update = PullRequestComment.content(host);

            update(host, target);

            const root = selectOrThrow(target, "[data-test=pullrequest-comment]");

            expect(root.classList).toContain("pull-request-comment");
            expect(root.classList).toContain("is-inline-comment");
            expect(root.classList).toContain("inline-comment");
            expect(root.classList).toContain("is-outdated");
        });

        it(`Given a global comment, then it should have the right classes`, () => {
            const host = {
                comment: PullRequestCommentPresenterStub.buildGlobalComment(),
            } as unknown as HostElement;
            const update = PullRequestComment.content(host);

            update(host, target);

            const root = selectOrThrow(target, "[data-test=pullrequest-comment]");

            expect(root.classList).toContain("pull-request-comment");
            expect(root.classList).toContain("comment");
        });

        it(`Given a pull-request event comment, then it should have the right classes`, () => {
            const host = {
                comment: PullRequestCommentPresenterStub.buildPullRequestEventComment(),
            } as unknown as HostElement;
            const update = PullRequestComment.content(host);

            update(host, target);

            const root = selectOrThrow(target, "[data-test=pullrequest-comment]");

            expect(root.classList).toContain("pull-request-comment");
            expect(root.classList).toContain("timeline-event");
        });

        it("should execute the post_rendering_callback each time the component renders", () => {
            const post_rendering_callback = jest.fn();
            const host = {
                comment: PullRequestCommentPresenterStub.buildInlineComment(),
                post_rendering_callback,
            } as unknown as HostElement;

            jest.useFakeTimers();

            const update = PullRequestComment.content(host);
            update(host, target);

            jest.advanceTimersByTime(1);

            expect(post_rendering_callback).toHaveBeenCalledTimes(1);
        });
    });
});