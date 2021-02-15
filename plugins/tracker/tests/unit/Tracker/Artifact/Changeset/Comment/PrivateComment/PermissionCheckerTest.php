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
 * along with Tuleap. If not, see http://www.gnu.org/licenses/.
 */

declare(strict_types=1);

namespace Tuleap\Tracker\Artifact\Changeset\Comment\PrivateComment;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Tracker;
use Tracker_Artifact_Changeset;
use Tracker_Artifact_Changeset_Comment;

class PermissionCheckerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var PermissionChecker
     */
    private $checker;
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|\PFUser
     */
    private $user;
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|Tracker_Artifact_Changeset_Comment
     */
    private $comment;
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|Tracker_Artifact_Changeset
     */
    private $changeset;
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|Tracker
     */
    private $tracker;

    protected function setUp(): void
    {
        $this->user = \Mockery::mock(\PFUser::class);

        $this->tracker = \Mockery::mock(Tracker::class, ['getGroupId' => 101]);
        $this->tracker->shouldReceive('userIsAdmin')->andReturn(false)->byDefault();

        $this->changeset = \Mockery::mock(Tracker_Artifact_Changeset::class);
        $this->changeset->shouldReceive('getTracker')->andReturn($this->tracker);

        $this->comment = $this->buildComment([]);

        $this->checker = new PermissionChecker();
    }

    public function testReturnsTrueIfUserIsSiteAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnTrue();
        $this->assertTrue($this->checker->userCanSeeComment($this->user, $this->comment));
    }

    public function testReturnsTrueIfUserIsProjectAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnTrue();

        $this->assertTrue($this->checker->userCanSeeComment($this->user, $this->comment));
    }

    public function testReturnsTrueIfUserIsTrackerAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnFalse();
        $this->tracker->shouldReceive('userIsAdmin')->andReturn(true)->once();
        $this->assertTrue($this->checker->userCanSeeComment($this->user, $this->comment));
    }

    public function testReturnsTrueIfUserIsMemberOfStaticUgroupAndNotAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnFalse();

        $ugroup_1 = \Mockery::mock(\ProjectUGroup::class, ['getId' => 1]);
        $ugroup_2 = \Mockery::mock(\ProjectUGroup::class, ['getId' => 2]);
        $ugroup_3 = \Mockery::mock(\ProjectUGroup::class, ['getId' => 3]);

        $this->comment = $this->buildComment([$ugroup_1, $ugroup_2, $ugroup_3]);

        $this->user->shouldReceive('isMemberOfUGroup')->with(1, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(2, 101)->andReturnTrue();

        $this->assertTrue($this->checker->userCanSeeComment($this->user, $this->comment));
    }

    public function testReturnsFalseIfThereAreNoUGroupsButCommentIsPrivate(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnFalse();

        $this->user->shouldReceive('isMemberOfUGroup')->never();

        $this->assertFalse($this->checker->userCanSeeComment($this->user, $this->comment));
    }

    public function testReturnsTrueIfPrivateCommentIsNull(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnFalse();

        $this->user->shouldReceive('isMemberOfUGroup')->never();

        $this->comment = $this->buildComment(null);

        $this->assertTrue($this->checker->userCanSeeComment($this->user, $this->comment));
    }

    public function testReturnsFalseIfUserIsNotMemberOfStaticUgroupAndNotAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnFalse();

        $ugroup_1 = \Mockery::mock(\ProjectUGroup::class, ['getId' => 1]);
        $ugroup_2 = \Mockery::mock(\ProjectUGroup::class, ['getId' => 2]);
        $ugroup_3 = \Mockery::mock(\ProjectUGroup::class, ['getId' => 3]);

        $this->comment = $this->buildComment([$ugroup_1, $ugroup_2, $ugroup_3]);

        $this->user->shouldReceive('isMemberOfUGroup')->with(1, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(2, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(3, 101)->andReturnFalse();

        $this->assertFalse($this->checker->userCanSeeComment($this->user, $this->comment));
    }

    public function testGetAllUGroupsIfUserIsSiteAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnTrue();

        $ugroup_1      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 1]);
        $ugroup_2      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 2]);
        $ugroup_3      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 3]);
        $this->comment = $this->buildComment([$ugroup_1, $ugroup_2, $ugroup_3]);

        $ugroups = $this->checker->getUgroupsThatUserCanSeeOnComment($this->user, $this->comment);

        $this->assertIsArray($ugroups);
        $this->assertCount(3, $ugroups);
    }

    public function testGetAllUGroupsIfUserIsProjectAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnTrue();

        $ugroup_1      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 1]);
        $ugroup_2      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 2]);
        $ugroup_3      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 3]);
        $this->comment = $this->buildComment([$ugroup_1, $ugroup_2, $ugroup_3]);
        $ugroups       = $this->checker->getUgroupsThatUserCanSeeOnComment($this->user, $this->comment);

        $this->assertIsArray($ugroups);
        $this->assertCount(3, $ugroups);
    }

    public function testGetAllUGroupsIfUserIsTrackerAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnFalse();

        $this->tracker->shouldReceive('userIsAdmin')->andReturn(true)->once();

        $ugroup_1      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 1]);
        $ugroup_2      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 2]);
        $ugroup_3      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 3]);
        $this->comment = $this->buildComment([$ugroup_1, $ugroup_2, $ugroup_3]);
        $ugroups       = $this->checker->getUgroupsThatUserCanSeeOnComment($this->user, $this->comment);

        $this->assertIsArray($ugroups);
        $this->assertCount(3, $ugroups);
    }

    public function testGetUGroupsThatUserIsMemberOfAndUserIsNotAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnFalse();

        $this->user->shouldReceive('isMemberOfUGroup')->with(1, 101)->andReturnTrue();
        $this->user->shouldReceive('isMemberOfUGroup')->with(2, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(3, 101)->andReturnTrue();

        $ugroup_1      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 1]);
        $ugroup_2      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 2]);
        $ugroup_3      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 3]);
        $this->comment = $this->buildComment([$ugroup_1, $ugroup_2, $ugroup_3]);
        $ugroups       = $this->checker->getUgroupsThatUserCanSeeOnComment($this->user, $this->comment);

        $this->assertIsArray($ugroups);
        $this->assertCount(2, $ugroups);
        $this->assertEquals(1, $ugroups[0]->getId());
        $this->assertEquals(3, $ugroups[1]->getId());
    }

    public function testGetUserIsNotAllowedToSeeUGroupsIfUserIsNotMemberOfUGroupsAndUserIsNotAdmin(): void
    {
        $this->user->shouldReceive('isSuperUser')->once()->andReturnFalse();
        $this->user->shouldReceive('isAdmin')->with(101)->once()->andReturnFalse();

        $ugroup_1      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 1]);
        $ugroup_2      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 2]);
        $ugroup_3      = \Mockery::mock(\ProjectUGroup::class, ['getId' => 3]);
        $this->comment = $this->buildComment([$ugroup_1, $ugroup_2, $ugroup_3]);
        $this->user->shouldReceive('isMemberOfUGroup')->with(1, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(2, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(3, 101)->andReturnFalse();

        $ugroups = $this->checker->getUgroupsThatUserCanSeeOnComment($this->user, $this->comment);

        $this->assertInstanceOf(UserIsNotAllowedToSeeUGroups::class, $ugroups);
    }

    public function testGetUserIsNotAllowedToSeeUGroupsIfUGroupsIsNull(): void
    {
        $this->user->shouldReceive('isSuperUser')->never();
        $this->user->shouldReceive('isAdmin')->with(101)->never();

        $this->user->shouldReceive('isMemberOfUGroup')->with(1, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(2, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(3, 101)->andReturnFalse();

        $this->comment = $this->buildComment(null);

        $ugroups = $this->checker->getUgroupsThatUserCanSeeOnComment($this->user, $this->comment);

        $this->assertInstanceOf(UserIsNotAllowedToSeeUGroups::class, $ugroups);
    }

    public function testGetUserIsNotAllowedToSeeUGroupsIfThereAreNoGroups(): void
    {
        $this->user->shouldReceive('isSuperUser')->never();
        $this->user->shouldReceive('isAdmin')->with(101)->never();

        $this->user->shouldReceive('isMemberOfUGroup')->with(1, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(2, 101)->andReturnFalse();
        $this->user->shouldReceive('isMemberOfUGroup')->with(3, 101)->andReturnFalse();

        $ugroups = $this->checker->getUgroupsThatUserCanSeeOnComment($this->user, $this->comment);

        $this->assertInstanceOf(UserIsNotAllowedToSeeUGroups::class, $ugroups);
    }

    /**
     * @param \ProjectUGroup[]|null $ugroups
     */
    private function buildComment(?array $ugroups): Tracker_Artifact_Changeset_Comment
    {
        return new Tracker_Artifact_Changeset_Comment(
            525,
            $this->changeset,
            null,
            null,
            110,
            1234567890,
            'A text comment',
            'text',
            0,
            $ugroups
        );
    }
}
