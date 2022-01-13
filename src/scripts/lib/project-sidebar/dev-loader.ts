/**
 * Copyright (c) 2021-Present Enalean
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

import "../../../themes/tlp/src/scss/tlp-vars-orange.scss";
import "./src/main";
import { example_config } from "@tuleap/project-sidebar-internal";

document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.createElement("tuleap-project-sidebar");
    sidebar.setAttribute("config", JSON.stringify(example_config));
    document.body.appendChild(sidebar);

    // Quick&dirty demonstrations
    // Show how to do something when the project announcement should be opened
    sidebar.addEventListener("show-project-announcement", () => {
        // eslint-disable-next-line no-alert
        alert("Show project announcement");
    });
    // Show how to do something when the sidebar is modified
    const observer = new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            const current_state = sidebar.hasAttribute(mutation.attributeName ?? "")
                ? "collapsed"
                : "opened";
            // eslint-disable-next-line no-alert
            alert(`Sidebar state changed, currently: ${current_state}`);
        }
    });
    observer.observe(sidebar, { attributes: true, attributeFilter: ["collapsed"] });
});
