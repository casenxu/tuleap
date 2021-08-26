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

namespace Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement;

use Tuleap\ProgramManagement\Adapter\Events\ArtifactUpdatedProxy;
use Tuleap\ProgramManagement\Domain\Events\ArtifactUpdatedEvent;
use Tuleap\ProgramManagement\Tests\Stub\CheckProgramIncrementStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyIsProgramIncrementTrackerStub;
use Tuleap\Test\Builders\UserTestBuilder;
use Tuleap\Tracker\Artifact\Event\ArtifactUpdated;
use Tuleap\Tracker\Test\Builders\ArtifactTestBuilder;
use Tuleap\Tracker\Test\Builders\ChangesetTestBuilder;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

final class ProgramIncrementIdentifierTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private const PROGRAM_INCREMENT_ID = 96;
    private \PFUser $user;

    protected function setUp(): void
    {
        $this->user = UserTestBuilder::aUser()->withId(197)->build();
    }

    public function testItThrowsAnExceptionWhenTrackerIsNotValid(): void
    {
        $this->expectException(ProgramIncrementNotFoundException::class);
        ProgramIncrementIdentifier::fromId(CheckProgramIncrementStub::buildOtherArtifactChecker(), 1, $this->user);
    }

    public function testItBuildAProgramIncrement(): void
    {
        $program_increment = ProgramIncrementIdentifier::fromId(
            CheckProgramIncrementStub::buildProgramIncrementChecker(),
            self::PROGRAM_INCREMENT_ID,
            $this->user
        );
        self::assertEquals(self::PROGRAM_INCREMENT_ID, $program_increment->getId());
    }

    public function testItReturnsNullWhenArtifactUpdatedIsNotAProgramIncrement(): void
    {
        self::assertNull(
            ProgramIncrementIdentifier::fromArtifactUpdated(
                VerifyIsProgramIncrementTrackerStub::buildNotProgramIncrement(),
                $this->buildArtifactUpdated()
            )
        );
    }

    public function testItReturnsAProgramIncrementFromArtifactUpdated(): void
    {
        $program_increment = ProgramIncrementIdentifier::fromArtifactUpdated(
            VerifyIsProgramIncrementTrackerStub::buildValidProgramIncrement(),
            $this->buildArtifactUpdated()
        );
        self::assertSame(self::PROGRAM_INCREMENT_ID, $program_increment->getId());
    }

    private function buildArtifactUpdated(): ArtifactUpdatedEvent
    {
        $tracker   = TrackerTestBuilder::aTracker()->withId(127)->build();
        $artifact  = ArtifactTestBuilder::anArtifact(self::PROGRAM_INCREMENT_ID)->inTracker($tracker)->build();
        $changeset = ChangesetTestBuilder::aChangeset('919')
            ->ofArtifact($artifact)
            ->submittedBy($this->user->getId())
            ->build();
        $event     = new ArtifactUpdated($artifact, $this->user, $changeset);
        return ArtifactUpdatedProxy::fromArtifactUpdated($event);
    }
}
