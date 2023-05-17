/**
 * Copyright (c) Enalean, 2023-Present. All Rights Reserved.
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

import { browserSupportsWebAuthn } from "@simplewebauthn/browser";
import { openTargetModalIdOnClick } from "@tuleap/tlp-modal";
import "../themes/style.scss";
import { register } from "./register";

const HIDDEN = "webauthn-hidden";

document.addEventListener("DOMContentLoaded", (): void => {
    if (browserSupportsWebAuthn()) {
        const register_section = document.querySelector("#webauthn-register-section");
        if (register_section instanceof HTMLElement) {
            register_section.classList.remove(HIDDEN);
        }

        prepareRegistration();
    } else {
        const disabled_section = document.querySelector("#webauthn-disabled-section");
        if (disabled_section instanceof HTMLElement) {
            disabled_section.classList.remove(HIDDEN);
        }
    }
});

function prepareRegistration(): void {
    const form_name_modal = document.querySelector("#webauthn-name-modal");
    if (!(form_name_modal instanceof HTMLElement)) {
        return;
    }

    const name_modal_input = form_name_modal.querySelector("#webauthn-name-input");
    const error = document.querySelector("#webauthn-error");
    const add_button_icon = document.querySelector("#webauthn-add-button > i");

    if (
        !(name_modal_input instanceof HTMLInputElement) ||
        !(error instanceof HTMLElement) ||
        !(add_button_icon instanceof HTMLElement)
    ) {
        return;
    }

    function registration(name: string): void {
        if (!(error instanceof HTMLElement) || !(add_button_icon instanceof HTMLElement)) {
            return;
        }

        register(name).match(
            () => {
                location.reload();
            },
            (fault) => {
                add_button_icon.classList.add(HIDDEN);
                error.classList.remove(HIDDEN);
                error.innerText = fault.toString();
            }
        );
    }

    const name_modal = openTargetModalIdOnClick(document, "webauthn-add-button");
    if (name_modal === null) {
        return;
    }
    form_name_modal.addEventListener("submit", (event) => {
        event.preventDefault();
        name_modal.hide();

        const name = name_modal_input.value.trim();
        name_modal_input.value = "";

        add_button_icon.classList.remove(HIDDEN);
        registration(name);
    });
}