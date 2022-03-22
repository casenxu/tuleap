/**
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

import { TextCell, NumberCell, EmptyCell } from "./data-formator";
import type { ArtifactReportResponseFieldValue } from "@tuleap/plugin-docgen-docx";
import { transformFieldValueIntoAFormattedCell } from "./transform-field-value-into-formatted-cell";

describe("transform-field-value-into-formatted-cell", () => {
    it("transforms string value into TextCell", (): void => {
        const field_value: ArtifactReportResponseFieldValue = {
            field_id: 2,
            type: "string",
            label: "Field02",
            value: "string_value",
        };
        const formatted_cell = transformFieldValueIntoAFormattedCell(field_value);

        expect(formatted_cell).toStrictEqual(new TextCell("string_value"));
    });
    it("transforms int value into NumberCell", (): void => {
        const field_value: ArtifactReportResponseFieldValue = {
            field_id: 1,
            type: "int",
            label: "integer field",
            value: 74,
        };
        const formatted_cell = transformFieldValueIntoAFormattedCell(field_value);

        expect(formatted_cell).toStrictEqual(new NumberCell(74));
    });
    it("transforms float value into NumberCell", (): void => {
        const field_value: ArtifactReportResponseFieldValue = {
            field_id: 1,
            type: "float",
            label: "Float field",
            value: 89.26,
        };
        const formatted_cell = transformFieldValueIntoAFormattedCell(field_value);

        expect(formatted_cell).toStrictEqual(new NumberCell(89.26));
    });
    it("transforms aid value into NumberCell", (): void => {
        const field_value: ArtifactReportResponseFieldValue = {
            field_id: 1,
            type: "aid",
            label: "Artifact ID",
            value: 123,
        };
        const formatted_cell = transformFieldValueIntoAFormattedCell(field_value);

        expect(formatted_cell).toStrictEqual(new NumberCell(123));
    });
    it("transforms atid value into NumberCell", (): void => {
        const field_value: ArtifactReportResponseFieldValue = {
            field_id: 1,
            type: "atid",
            label: "Artifact ID",
            value: 123,
        };
        const formatted_cell = transformFieldValueIntoAFormattedCell(field_value);

        expect(formatted_cell).toStrictEqual(new NumberCell(123));
    });
    it("transforms priority value into NumberCell", (): void => {
        const field_value: ArtifactReportResponseFieldValue = {
            field_id: 1,
            type: "priority",
            label: "Priority",
            value: 1,
        };
        const formatted_cell = transformFieldValueIntoAFormattedCell(field_value);

        expect(formatted_cell).toStrictEqual(new NumberCell(1));
    });
    it("transforms computed field manual value into NumberCell", (): void => {
        const field_value: ArtifactReportResponseFieldValue = {
            field_id: 1,
            type: "computed",
            label: "Computed field",
            value: 456,
            manual_value: 123,
            is_autocomputed: false,
        };
        const formatted_cell = transformFieldValueIntoAFormattedCell(field_value);

        expect(formatted_cell).toStrictEqual(new NumberCell(123));
    });
    it("transforms computed field autocomputed value into NumberCell", (): void => {
        const field_value: ArtifactReportResponseFieldValue = {
            field_id: 1,
            type: "computed",
            label: "Computed field",
            value: 456,
            manual_value: 123,
            is_autocomputed: true,
        };
        const formatted_cell = transformFieldValueIntoAFormattedCell(field_value);

        expect(formatted_cell).toStrictEqual(new NumberCell(456));
    });
    it("transforms all other fields into EmptyCell", (): void => {
        const field_value: ArtifactReportResponseFieldValue = {
            field_id: 1,
            type: "text",
            label: "Computed field",
            value: "Text value",
            format: "text",
        };
        const formatted_cell = transformFieldValueIntoAFormattedCell(field_value);

        expect(formatted_cell).toStrictEqual(new EmptyCell());
    });
});
