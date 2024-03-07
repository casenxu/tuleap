/*
 * Copyright (c) Enalean, 2024 - present. All Rights Reserved.
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

import { describe, beforeEach, it, expect } from "vitest";
import { buildQueryFromFilters } from "./get-pull-requests-query-builder";
import { AuthorFilterBuilder } from "../components/Filters/Author/AuthorFilter";
import { UserStub } from "../../tests/stubs/UserStub";
import { LabelFilterBuilder } from "../components/Filters/Labels/LabelFilter";
import { ProjectLabelStub } from "../../tests/stubs/ProjectLabelStub";
import { GettextStub } from "../../tests/stubs/GettextStub";
import { KeywordFilterBuilder } from "../components/Filters/Keywords/KeywordFilter";
import { TargetBranchFilterBuilder } from "../components/Filters/Branches/TargetBranchFilter";
import { ReviewerFilterBuilder } from "../components/Filters/Reviewer/ReviewerFilter";

describe("get-pull-requests-query-builder", () => {
    let are_closed_pull_requests_shown: boolean;

    beforeEach(() => {
        are_closed_pull_requests_shown = true;
    });

    describe("Author filter", () => {
        it("Given a filter on author, then it should return a proper query string", () => {
            const user_id = 102;
            const query = buildQueryFromFilters(
                [
                    AuthorFilterBuilder(GettextStub).fromAuthor(
                        UserStub.withIdAndName(user_id, "John Doe (jdoe)"),
                    ),
                ],
                are_closed_pull_requests_shown,
            );

            expect(query).toContain(JSON.stringify({ authors: [{ id: user_id }] }));
        });
    });

    describe("Closed pull-requests filter", () => {
        it('When closed pull-requests are shown, then it should NOT set { status: "open" } in the query', () => {
            are_closed_pull_requests_shown = true;
            const query = buildQueryFromFilters([], are_closed_pull_requests_shown);

            expect(query).toStrictEqual(JSON.stringify({}));
        });

        it('When closed pull-requests are hidden, then it should set { status: "open" } in the query', () => {
            are_closed_pull_requests_shown = false;
            const query = buildQueryFromFilters([], are_closed_pull_requests_shown);

            expect(query).toContain(JSON.stringify({ status: "open" }));
        });
    });

    describe("Labels Filters", () => {
        it("Given filters on labels, then it should return a proper query string", () => {
            const builder = LabelFilterBuilder(GettextStub);
            const emergency_label = ProjectLabelStub.regulardWithIdAndLabel(1, "Emergency");
            const easy_fix_label = ProjectLabelStub.outlinedWithIdAndLabel(2, "Easy fix");
            const query = buildQueryFromFilters(
                [builder.fromLabel(emergency_label), builder.fromLabel(easy_fix_label)],
                are_closed_pull_requests_shown,
            );

            expect(query).toContain(
                JSON.stringify({
                    labels: [{ id: emergency_label.id }, { id: easy_fix_label.id }],
                }),
            );
        });
    });

    describe("Keywords filters", () => {
        it("Given keywords filters, then it should return a proper query string", () => {
            const builder = KeywordFilterBuilder(GettextStub);
            const foo_keyword = builder.fromKeyword(1, "Foo");
            const bar_keyword = builder.fromKeyword(2, "Bar");

            const query = buildQueryFromFilters(
                [foo_keyword, bar_keyword],
                are_closed_pull_requests_shown,
            );

            expect(query).toContain(
                JSON.stringify({
                    search: [{ keyword: "Foo" }, { keyword: "Bar" }],
                }),
            );
        });
    });

    describe("Target branch filter", () => {
        it("Given a filter on a target branch, then it should return a proper query string", () => {
            const branch = { name: "walnut" };
            const query = buildQueryFromFilters(
                [TargetBranchFilterBuilder(GettextStub).fromBranch(branch)],
                are_closed_pull_requests_shown,
            );

            expect(query).toContain(JSON.stringify({ target_branches: [{ name: branch.name }] }));
        });
    });

    describe("Reviewer filter", () => {
        it("Given a filter on a reviewer, then it should return a proper query string", () => {
            const reviewer = UserStub.withIdAndName(102, "John Doe");
            const query = buildQueryFromFilters(
                [ReviewerFilterBuilder(GettextStub).fromReviewer(reviewer)],
                are_closed_pull_requests_shown,
            );

            expect(query).toContain(JSON.stringify({ reviewers: [{ id: reviewer.id }] }));
        });
    });
});
