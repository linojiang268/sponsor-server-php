<?php
namespace intg\Sponsor\Repositories;

use intg\Sponsor\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sponsor\Models\Sponsorship;
use Sponsor\Models\SponsorshipApplication;

class SponsorshipApplicationRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    //=========================================
    //            findApplication
    //=========================================
    public function testFindApplication()
    {
        factory(SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 2,
            'team_id'        => 3,
        ]);
        $application = $this->getRepository()->findApplication(1);
        self::assertEquals(1, $application->id);
        self::assertEquals(2, $application->sponsorship_id);
        self::assertEquals(3, $application->team_id);
    }

    public function testFindSponsorshipNotFound()
    {
        self::assertNull($this->getRepository()->findApplication(1));
    }

    //=========================================
    //            findApplications
    //=========================================
    public function testFindApplications()
    {
        factory(SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 2,
            'sponsorship_id' => 1,
            'team_id'        => 2,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 3,
            'sponsorship_id' => 1,
            'team_id'        => 3,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 4,
            'sponsorship_id' => 1,
            'team_id'        => 4,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 5,
            'sponsorship_id' => 1,
            'team_id'        => 5,
        ]);

        // no items
        list($applications, $total) = $this->getRepository()->findApplications(2, 1, 3);
        self::assertEquals(0, $total);
        self::assertEquals([], $applications);

        // first page
        list($applications, $total) = $this->getRepository()->findApplications(1, 1, 3);
        self::assertEquals(5, $total);
        self::assertCount(3, $applications);

        // second page
        list($applications, $total) = $this->getRepository()->findApplications(1, 2, 3);
        self::assertEquals(5, $total);
        self::assertCount(2, $applications);
    }

    //=========================================
    //            findApplicationsOf
    //=========================================
    public function testFindApplicationsOf()
    {
        factory(SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 1,
            'team_id'        => 1,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 2,
            'sponsorship_id' => 2,
            'team_id'        => 1,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 3,
            'sponsorship_id' => 3,
            'team_id'        => 1,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 4,
            'sponsorship_id' => 1,
            'team_id'        => 2,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 5,
            'sponsorship_id' => 5,
            'team_id'        => 1,
        ]);

        factory(Sponsorship::class)->create([
            'id' => 1,
        ]);
        factory(Sponsorship::class)->create([
            'id' => 2,
        ]);
        factory(Sponsorship::class)->create([
            'id' => 3,
        ]);
        factory(Sponsorship::class)->create([
            'id' => 5,
        ]);

        // no items
        list($total, $applications) = $this->getRepository()->findApplicationsOf(3, 1, 3);
        self::assertEquals(0, $total);
        self::assertEquals([], $applications);

        // first page
        list($total, $applications) = $this->getRepository()->findApplicationsOf(1, 1, 3);
        self::assertEquals(4, $total);
        self::assertCount(3, $applications);

        // second page
        list($total, $applications) = $this->getRepository()->findApplicationsOf(1, 2, 3);
        self::assertEquals(4, $total);
        self::assertCount(1, $applications);
    }

    //=========================================
    //          findApplicationStatus
    //=========================================
    public function testFindApplicationStatus()
    {
        factory(SponsorshipApplication::class)->create([
            'id'             => 1,
            'sponsorship_id' => 2,
            'team_id'        => 1,
            'status'         => SponsorshipApplication::STATUS_PENDING,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 2,
            'sponsorship_id' => 3,
            'team_id'        => 1,
            'status'         => SponsorshipApplication::STATUS_PENDING,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 3,
            'sponsorship_id' => 4,
            'team_id'        => 2,
            'status'         => SponsorshipApplication::STATUS_PENDING,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 4,
            'sponsorship_id' => 5,
            'team_id'        => 1,
            'status'         => SponsorshipApplication::STATUS_APPROVED,
        ]);

        factory(SponsorshipApplication::class)->create([
            'id'             => 5,
            'sponsorship_id' => 6,
            'team_id'        => 1,
            'status'         => SponsorshipApplication::STATUS_REJECTED,
        ]);

        $result = $this->getRepository()->findApplicationStatus([1, 2, 3, 4, 5, 6], 1);
        self::assertCount(6, $result);
        self::assertEquals(-1, $result[1]);
        self::assertEquals(SponsorshipApplication::STATUS_PENDING, $result[2]);
        self::assertEquals(SponsorshipApplication::STATUS_PENDING, $result[3]);
        self::assertEquals(-1, $result[4]);
        self::assertEquals(SponsorshipApplication::STATUS_APPROVED, $result[5]);
        self::assertEquals(SponsorshipApplication::STATUS_REJECTED, $result[6]);
    }

    /**
     * @return \Sponsor\Contracts\Repositories\SponsorshipApplicationRepository
     */
    private function getRepository()
    {
        return $this->app[\Sponsor\Contracts\Repositories\SponsorshipApplicationRepository::class];
    }
}
