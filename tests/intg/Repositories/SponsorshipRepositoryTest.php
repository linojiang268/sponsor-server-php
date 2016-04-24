<?php
namespace intg\Sponsor\Repositories;

use Carbon\Carbon;
use intg\Sponsor\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Sponsor\Models\Sponsor;
use Sponsor\Models\Sponsorship;

class SponsorshipRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @before
     */
    public function fixNow()
    {
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d', '2015-11-20'));
    }

    /**
     * @after
     */
    public function unfixNow()
    {
        Carbon::setTestNow(null);
    }

    //=========================================
    //            findSponsorshipFor
    //=========================================
    public function testFindSponsorshipFor()
    {
        factory(Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
            'name'       => '赞助a',
        ]);
        $sponsorship = $this->getRepository()->findSponsorshipFor(1, 1);
        self::assertEquals(1, $sponsorship->id);
        self::assertEquals(1, $sponsorship->sponsor_id);
        self::assertEquals('赞助a', $sponsorship->name);
    }

    public function testFindSponsorshipForNotFound()
    {
        self::assertNull($this->getRepository()->findSponsorshipFor(1, 1));
    }

    public function testFindSponsorshipUseInvaidSponsor()
    {
        factory(Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
            'name'       => '赞助a',
        ]);
        self::assertNull($this->getRepository()->findSponsorshipFor(2, 1));
    }

    //=========================================
    //            findSponsorship
    //=========================================
    public function testFindSponsorship()
    {
        factory(Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
            'name'       => '赞助a',
        ]);
        $sponsorship = $this->getRepository()->findSponsorship(1);
        self::assertEquals(1, $sponsorship->id);
        self::assertEquals(1, $sponsorship->sponsor_id);
        self::assertEquals('赞助a', $sponsorship->name);
    }

    public function testFindSponsorshipNotFound()
    {
        self::assertNull($this->getRepository()->findSponsorship(1));
    }

    //=========================================
    //            findSponsorships
    //=========================================
    public function testFindSponsorships()
    {
        factory(Sponsorship::class)->create([
            'id'         => 1,
            'sponsor_id' => 1,
            'name'       => '赞助a',
            'application_start_date' => '2015-11-19',
            'application_end_date'   => '2015-11-19',
            'status'     => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
            'updated_at' => '2015-11-19 15:00:00',
        ]);
        factory(Sponsorship::class)->create([
            'id'         => 2,
            'sponsor_id' => 2,
            'name'       => '赞助b',
            'application_start_date' => '2015-11-21',
            'application_end_date'   => '2015-11-21',
            'status'     => \Sponsor\Models\Sponsorship::STATUS_PENDING,
            'updated_at' => '2015-11-20 18:00:00',
        ]);
        factory(Sponsorship::class)->create([
            'id'         => 3,
            'sponsor_id' => 3,
            'name'       => '赞助c',
            'application_start_date' => '2015-11-21',
            'application_end_date'   => '2015-11-21',
            'status'     => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
            'updated_at' => '2015-11-20 17:00:00',
        ]);
        factory(Sponsor::class)->create([
            'id'   => 2,
            'name' => '奔驰',
        ]);

        // first page
        list($total, $sponsorships) = $this->getRepository()->findSponsorships(1, 2);
        self::assertEquals(3, $total);
        self::assertCount(2, $sponsorships);
        self::assertEquals(2, $sponsorships[0]->id);
        self::assertEquals(3, $sponsorships[1]->id);

        // second page
        list($total, $sponsorships) = $this->getRepository()->findSponsorships(2, 2);
        self::assertEquals(3, $total);
        self::assertCount(1, $sponsorships);
        self::assertEquals(1, $sponsorships[0]->id);

        // special sponsor with sponsor relation
        list($total, $sponsorships) = $this->getRepository()->findSponsorships(1, 2, [
            'sponsor'   => 2,
            'relations' => ['sponsor'],
        ]);
        self::assertEquals(1, $total);
        self::assertCount(1, $sponsorships);
        self::assertEquals(2, $sponsorships[0]->id);
        self::assertEquals(2, $sponsorships[0]->sponsor->id);

        // without expired sponsorships
        $result = $this->getRepository()->findSponsorships(1, 2, ['with_expired' => false]);
        self::assertEquals(2, $result[0]);

        // without pending sponsorships
        $result = $this->getRepository()->findSponsorships(1, 2, ['only_published' => true]);
        self::assertEquals(2, $result[0]);

        // no results
        list($total, $sponsorships) = $this->getRepository()->findSponsorships(1, 1, ['sponsor' => 9]);
        self::assertEquals(0, $total);
        self::assertEquals([], $sponsorships);
    }

    /**
     * @return \Sponsor\Contracts\Repositories\SponsorshipRepository
     */
    private function getRepository()
    {
        return $this->app[\Sponsor\Contracts\Repositories\SponsorshipRepository::class];
    }
}
