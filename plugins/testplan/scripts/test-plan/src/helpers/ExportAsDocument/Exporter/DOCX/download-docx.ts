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

import type { VueGettextProvider } from "../../../vue-gettext-provider";
import { triggerBlobDownload } from "./trigger-blob-download";
import { File, Packer, Paragraph, StyleLevel } from "docx";
import type {
    DateTimeLocaleInformation,
    ExportDocument,
    GlobalExportProperties,
} from "../../../../type";
import { buildCoverPage } from "./cover-builder";
import {
    HEADER_LEVEL_SECTION,
    HEADER_STYLE_SECTION_TITLE,
    MAIN_TITLES_NUMBERING_ID,
    properties,
} from "./document-properties";
import { buildMilestoneBacklog } from "./backlog-builder";
import { buildFooter, buildHeader } from "./header-footer";
import { TableOfContentsPrefilled } from "./TableOfContents/table-of-contents";

export async function downloadDocx(
    document: ExportDocument,
    gettext_provider: VueGettextProvider,
    global_export_properties: GlobalExportProperties,
    datetime_locale_information: DateTimeLocaleInformation
): Promise<void> {
    const exported_formatted_date = new Date().toLocaleDateString(
        datetime_locale_information.locale,
        { timeZone: datetime_locale_information.timezone }
    );

    const footers = {
        default: buildFooter(gettext_provider, global_export_properties, exported_formatted_date),
    };

    const headers = {
        default: buildHeader(global_export_properties),
    };

    const file = new File({
        ...properties,
        sections: [
            {
                children: [
                    ...(await buildCoverPage(
                        gettext_provider,
                        global_export_properties,
                        exported_formatted_date
                    )),
                ],
                properties: {
                    titlePage: true,
                },
            },
            {
                headers,
                children: [
                    new Paragraph({
                        text: gettext_provider.$gettext("Table of contents"),
                        heading: HEADER_LEVEL_SECTION,
                        numbering: {
                            reference: MAIN_TITLES_NUMBERING_ID,
                            level: 0,
                        },
                    }),
                    new TableOfContentsPrefilled(gettext_provider, global_export_properties, {
                        hyperlink: true,
                        stylesWithLevels: [
                            new StyleLevel(
                                HEADER_STYLE_SECTION_TITLE,
                                Number(HEADER_STYLE_SECTION_TITLE.substr(-1))
                            ),
                        ],
                    }),
                ],
            },
            {
                headers,
                children: [
                    ...buildMilestoneBacklog(document, gettext_provider, global_export_properties),
                ],
                footers,
            },
        ],
    });
    triggerBlobDownload(`${document.name}.docx`, await Packer.toBlob(file));
}
