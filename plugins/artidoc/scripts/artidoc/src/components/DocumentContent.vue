<!--
  - Copyright (c) Enalean, 2024 - present. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -
  -->

<template>
    <ol>
        <li
            v-for="section in sections"
            v-bind:key="section.artifact.id"
            v-bind:id="`${section.artifact.id}`"
        >
            <article class="document-section">
                <div class="section-header">
                    <slot
                        name="section-header"
                        v-bind:title="section.title"
                        v-bind:artifact_id="section.artifact.id"
                    >
                    </slot>
                </div>
                <slot
                    name="section-content"
                    v-bind:description_value="section.description.post_processed_value"
                ></slot>
            </article>
        </li>
    </ol>
</template>
<script setup lang="ts">
import type { ArtidocSection } from "@/helpers/artidoc-section.type";

defineProps<{ sections: readonly ArtidocSection[] }>();
</script>
<style lang="scss" scoped>
.document-section {
    display: flex;
    flex-direction: column;
    margin-bottom: var(--tlp-x-large-spacing);
}

.section-header {
    margin-bottom: var(--tlp-medium-spacing);
    border-bottom: 1px solid var(--tlp-neutral-normal-color);
}

ol {
    counter-reset: item-without-dot;
}

li {
    padding: 0 0 0 var(--tlp-medium-spacing);
    counter-increment: item-without-dot;
}

li::marker {
    content: counter(item-without-dot);
    color: var(--tlp-dimmed-color-lighter-50);
    font-style: italic;
    font-weight: 600;
}
</style>
