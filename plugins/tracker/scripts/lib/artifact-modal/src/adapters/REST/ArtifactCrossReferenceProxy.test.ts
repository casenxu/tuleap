/*
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
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

import { ArtifactCrossReferenceProxy } from "./ArtifactCrossReferenceProxy";
import type { ArtifactWithStatus } from "./ArtifactWithStatus";

describe(`ArtifactCrossReferenceProxy`, () => {
    it(`builds from an artifact JSON payload from the REST API`, () => {
        const response = {
            xref: "bug #247",
            tracker: { color_name: "coral-pink" },
        } as ArtifactWithStatus;
        const reference = ArtifactCrossReferenceProxy.fromAPIArtifact(response);

        expect(reference.ref).toBe("bug #247");
        expect(reference.color).toBe("coral-pink");
    });
});