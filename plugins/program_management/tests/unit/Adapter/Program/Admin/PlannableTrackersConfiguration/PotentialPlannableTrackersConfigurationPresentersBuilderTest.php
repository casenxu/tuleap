<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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

declare(strict_types=1);

namespace Tuleap\ProgramManagement\Adapter\Program\Admin\PlannableTrackersConfiguration;

use Tuleap\ProgramManagement\Domain\Program\Admin\ProgramForAdministrationIdentifier;
use Tuleap\ProgramManagement\Stub\RetrievePlannableTrackersStub;
use Tuleap\ProgramManagement\Stub\VerifyIsTeamStub;
use Tuleap\ProgramManagement\Stub\VerifyProjectPermissionStub;
use Tuleap\Test\Builders\ProjectTestBuilder;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Test\PHPUnit\TestCase;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

final class PotentialPlannableTrackersConfigurationPresentersBuilderTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\TrackerFactory
     */
    private $tracker_factory;
    private ProgramForAdministrationIdentifier $program;

    protected function setUp(): void
    {
        $this->tracker_factory = $this->createMock(\TrackerFactory::class);
        $this->tracker_factory->method('getTrackersByGroupId')->willReturn(
            [
                TrackerTestBuilder::aTracker()->withId(300)->withName('program increment tracker')->build(),
                TrackerTestBuilder::aTracker()->withId(500)->withName('feature tracker')->build(),
            ]
        );
        $this->program = ProgramForAdministrationIdentifier::fromProject(
            VerifyIsTeamStub::withNotValidTeam(),
            VerifyProjectPermissionStub::withAdministrator(),
            UserTestBuilder::aUser()->build(),
            ProjectTestBuilder::aProject()->withId(101)->build()
        );
    }

    public function testBuildTrackerPresentersWithCheckedTrackerIfExist(): void
    {
        $retriever = RetrievePlannableTrackersStub::buildIds(300);

        $builder    = new PotentialPlannableTrackersConfigurationPresentersBuilder(
            $this->tracker_factory,
            $retriever
        );
        $presenters = $builder->buildPotentialPlannableTrackerPresenters($this->program);

        self::assertCount(2, $presenters);
        self::assertSame(300, $presenters[0]->id);
        self::assertTrue($presenters[0]->is_selected);
        self::assertSame(500, $presenters[1]->id);
        self::assertFalse($presenters[1]->is_selected);
    }

    public function testBuildTrackerPresentersWithoutCheckedTracker(): void
    {
        $retriever = RetrievePlannableTrackersStub::buildIds();

        $builder    = new PotentialPlannableTrackersConfigurationPresentersBuilder($this->tracker_factory, $retriever);
        $presenters = $builder->buildPotentialPlannableTrackerPresenters($this->program);

        self::assertCount(2, $presenters);
        self::assertSame(300, $presenters[0]->id);
        self::assertFalse($presenters[0]->is_selected);
        self::assertSame(500, $presenters[1]->id);
        self::assertFalse($presenters[1]->is_selected);
    }
}
