<?php
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

namespace Tuleap\Tracker\Semantic\Progress;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tuleap\Tracker\Artifact\Artifact;

class MethodBasedOnLinksCountTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\Tracker_FormElement_Field_ArtifactLink
     */
    private $links_field;
    /**
     * @var MethodBasedOnLinksCount
     */
    private $method;
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|SemanticProgressDao
     */
    private $dao;

    protected function setUp(): void
    {
        $this->dao         = \Mockery::mock(SemanticProgressDao::class);
        $this->links_field = \Mockery::mock(\Tracker_FormElement_Field_ArtifactLink::class, ['getId' => 1003]);
        $this->method      = new MethodBasedOnLinksCount(
            $this->dao,
            '_is_child'
        );
    }

    public function testItReturnsTrueIfFieldIsUsed(): void
    {
        $this->assertTrue($this->method->isFieldUsedInComputation($this->links_field));
    }

    public function testItReturnsFalseIfFieldIsNotUsed(): void
    {
        $random_field = \Mockery::mock(\Tracker_FormElement_Field_Date::class);

        $this->assertFalse($this->method->isFieldUsedInComputation($random_field));
    }

    public function testItDoesComputesTheProgressWhenItHasOpenAndClosedLinkedArtifacts(): void
    {
        $tracker = \Mockery::mock(\Tracker::class, [
            'getItemName' => 'stories',
            'getGroupId' => 104,
            'getId' => 113
        ]);

        $last_artifact_changeset = \Mockery::mock(
            \Tracker_Artifact_ChangesetValue_ArtifactLink::class,
            ['getValue' => [
                '141' => $this->buildArtifactLinkInfo(141, "_is_child", $tracker, false), // 1 out of 4 children is closed
                '142' => $this->buildArtifactLinkInfo(142, "is_subtask", $tracker, true),
                '143' => $this->buildArtifactLinkInfo(143, "covered_by", $tracker, true),
                '144' => $this->buildArtifactLinkInfo(144, "_is_child", $tracker, true),
                '145' => $this->buildArtifactLinkInfo(145, "_is_child", $tracker, true),
                '146' => $this->buildArtifactLinkInfo(146, "_is_child", $tracker, true)
            ]]
        );

        $artifact = \Mockery::mock(
            Artifact::class,
            [
                'getAnArtifactLinkField' => $this->links_field
            ]
        );
        $this->links_field->shouldReceive('getLastChangesetValue')
            ->once()
            ->with($artifact)
            ->andReturn($last_artifact_changeset);

        $progression_result = $this->method->computeProgression(
            $artifact,
            \Mockery::mock(\PFUser::class)
        );

        $this->assertEquals(0.25, $progression_result->getValue());
    }

    public function testItDoesNotComputeTheProgressWhenThereIsNoLinkField(): void
    {
        $artifact = \Mockery::mock(
            Artifact::class,
            [
                'getAnArtifactLinkField' => null
            ]
        );

        $progression_result = $this->method->computeProgression(
            $artifact,
            \Mockery::mock(\PFUser::class)
        );

        $this->assertEquals(null, $progression_result->getValue());
    }

    /**
     * @testWith [true, 0]
     *           [false, 1]
     */
    public function testItComputesWhenItHasNoLinksOfGivenType(bool $is_artifact_open, float $expected_progress_value): void
    {
        $last_artifact_changeset = \Mockery::mock(
            \Tracker_Artifact_ChangesetValue_ArtifactLink::class,
            ['getValue' => []]
        );

        $artifact = \Mockery::mock(Artifact::class, [
            'isOpen' => $is_artifact_open,
            'getAnArtifactLinkField' => $this->links_field
        ]);

        $this->links_field->shouldReceive('getLastChangesetValue')
            ->once()
            ->with($artifact)
            ->andReturn($last_artifact_changeset);

        $progression_result = $this->method->computeProgression(
            $artifact,
            \Mockery::mock(\PFUser::class)
        );

        $this->assertEquals($expected_progress_value, $progression_result->getValue());
    }

    private function buildArtifactLinkInfo(int $artifact_id, string $nature, \Tracker $tracker, bool $is_artifact_open): \Tracker_ArtifactLinkInfo
    {
        $artifact = \Mockery::mock(
            Artifact::class,
            [
                'getId'            => $artifact_id,
                'getTracker'       => $tracker,
                'getLastChangeset' => \Mockery::mock(\Tracker_Artifact_Changeset::class, ['getId' => 12451]),
                'isOpen'           => $is_artifact_open
            ]
        );

        return \Tracker_ArtifactLinkInfo::buildFromArtifact($artifact, $nature);
    }

    public function testItIsConfigured(): void
    {
        $this->assertTrue($this->method->isConfigured());
    }

    public function testItDoesNotExportsToRESTYet(): void
    {
        self::assertNull(
            $this->method->exportToREST(\Mockery::mock(\PFUser::class)),
        );
    }
    public function testItDoesNotExportToXMLYet(): void
    {
        $xml_data = '<?xml version="1.0" encoding="UTF-8"?><semantics/>';
        $root     = new \SimpleXMLElement($xml_data);

        $this->method->exportToXMl($root, [
            'F201' => 1001
        ]);

        $this->assertCount(0, $root->children());
    }

    public function testDoesNotSaveItsConfigurationYet(): void
    {
        $tracker = \Mockery::mock(\Tracker::class, ['getId' => 113]);

        $this->dao->shouldReceive('save')->never();

        $this->assertFalse($this->method->saveSemanticForTracker($tracker));
    }

    public function testItDoesNotDeleteItsConfigurationYet(): void
    {
        $tracker = \Mockery::mock(\Tracker::class, ['getId' => 113]);

        $this->dao->shouldReceive('delete')->never();

        $this->assertFalse(
            $this->method->deleteSemanticForTracker($tracker)
        );
    }
}
