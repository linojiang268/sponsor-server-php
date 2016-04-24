<?php
namespace intg\Sponsor\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use intg\Sponsor\TestCase;
use Sponsor\Models\Sponsorship;

class SponsorshipControllerTest extends TestCase
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
    //       List initiated sponsorships
    //=========================================
    public function testListSponsorshipsEmpty()
    {
        $response = $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->create([
            'id' => 1,
        ]))->get('/web/sponsorships/page/1')->response;

        $response = json_decode($response->content());
        $this->assertEmpty($response->sponsorships);
        $this->assertEquals(0, $response->totalPages);
    }

    public function testListSponsorshipsHasSome()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-20',
            'application_end_date'   => '2015-11-25',
        ]);
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 2,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-21',
            'application_end_date'   => '2015-11-25',
        ]);
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 3,
            'sponsor_id'             => 2,
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
        ]);

        $response = $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->get('/web/sponsorships/page/1')->response;

        $response = json_decode($response->content());
        $this->assertEquals(1, $response->totalPages);
        $sponsorships = $response->sponsorships;
        $this->assertCount(2, $sponsorships, 'should have 2 sponsorships for current user');

        // sponsorships should have it sorted
        $this->assertEquals(2, $sponsorships[0]->id);
        $this->assertEquals(1, $sponsorships[1]->id);
    }

    //=========================================
    //        List sponsorships by teams
    //=========================================
    public function testListSponsorshipsByTeamsEmpty()
    {

        $user = factory(\Sponsor\Models\User::class)->create([
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
        ]);
        $this->startSession();
        $response = $this->actingAs($user, 'extended-eloquent-team')
            ->get('/api/sponsorships/')->response;

        $response = json_decode($response->content());
        $this->assertEmpty($response->sponsorships);
        $this->assertEquals(0, $response->total);
    }

    public function testListSponsorshipsByTeamsHasSome()
    {
        $user = factory(\Sponsor\Models\User::class)->create([
            'mobile'    => '13800138000',
            'nick_name' => 'wangwu',
        ]);
        factory(\Sponsor\Models\User::class)->create([
            'mobile'    => '13800138002',
            'nick_name' => 'wangwu2',
        ]);
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'updated_at'             => '2015-11-20 01:00:00',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 2,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'updated_at'             => '2015-11-20 03:00:00',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 3,
            'sponsor_id'             => 2,
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'updated_at'             => '2015-11-20 02:00:00',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 4,
            'sponsor_id'             => 2,
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'updated_at'             => '2015-11-20 02:00:00',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PENDING,
        ]);
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 5,
            'sponsor_id'             => 2,
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'updated_at'             => '2015-11-20 02:00:00',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_CLOSED,
        ]);
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 6,
            'sponsor_id'             => 2,
            'application_start_date' => '2015-11-18',
            'application_end_date'   => '2015-11-19',
            'updated_at'             => '2015-11-18 00:00:00',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);

        $response = $this->actingAs($user, 'extended-eloquent-team')
            ->get('/api/sponsorships?page=1&size=2')->response;

        $response = json_decode($response->content(), true);
        $this->assertEquals(3, $response['total']);

        $sponsorships = $response['sponsorships'];
        $this->assertCount(2, $sponsorships, 'should have 2 sponsorships');

        // sponsorships should have it sorted
        $this->assertEquals(2, $sponsorships[0]['id']);
        $this->assertEquals(3, $sponsorships[1]['id']);

        $sponsorship = $sponsorships[0];
        $this->assertEquals($sponsorship['application_start_date'], '2015-11-22');
        $this->assertEquals($sponsorship['updated_at'], '2015-11-20 03:00:00');
        $this->assertEquals($sponsorship['status'], \Sponsor\Models\Sponsorship::STATUS_PUBLISHED);
    }

    //=========================================
    //      Postpone application
    //=========================================
    public function testPostponeApplicationNoSuchSponsorship()
    {
        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make())
            ->post('/web/sponsorships/1/postpone', [
                '_token'               => csrf_token(),
                'application_end_date' => '2015-10-12',
            ]);
        $this->assertRedirectedTo('/web/sponsorships');
    }

    public function testPostponeApplicationWithBadDeadline()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->post('/web/sponsorships/1/postpone', [
            '_token'               => csrf_token(),
            'application_end_date' => '2015-10-12',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1');
        $this->assertSessionHasErrors();
    }

    public function testPostponeApplicationSuccess()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'status'                 => Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->post('/web/sponsorships/1/postpone', [
            '_token'               => csrf_token(),
            'application_end_date' => '2015-11-24',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1');
        $this->seeInDatabase('sponsorships', [
            'id'                     => 1,
            'sponsor_id'             => 1,
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-24',
        ]);
    }

    //=========================================
    //      Update Sponsorship
    //=========================================
    public function testUpdateSponsorshipSuccessfully()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PENDING,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->put('/web/sponsorships/1', [
            '_token'                 => csrf_token(),
            'name'                   => 'b赞助',
            'intro'                  => 'b赞助内容',
            'application_start_date' => '2015-11-21',
            'application_end_date'   => '2015-11-23',
            'application_condition'  => 'b赞助申请条件',
        ], [
            'Referer' => '/web/sponsorships/1',
        ]);
        $this->assertRedirectedTo('/web/sponsorships/1');

        $this->seeInDatabase('sponsorships', [
            'id'                     => 1,
            'name'                   => 'b赞助',
            'intro'                  => 'b赞助内容',
            'application_start_date' => '2015-11-21',
            'application_end_date'   => '2015-11-23',
            'application_condition'  => 'b赞助申请条件',
        ]);
    }

    public function testUpdateSponsorshipFailedWithInvalidStartDate()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PENDING,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->put('/web/sponsorships/1', [
            '_token'                 => csrf_token(),
            'name'                   => 'b赞助',
            'intro'                  => 'b赞助内容',
            'application_start_date' => '2015-11-19',
            'application_end_date'   => '2015-11-23',
            'application_condition'  => 'b赞助申请条件',
        ], [
            'Referer' => '/web/sponsorships/1',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1');
        $this->assertSessionHasErrors(['application_start_date' => '申请开始时间不能小于今天']);

        $this->seeInDatabase('sponsorships', [
            'id'   => 1,
            'name' => 'a赞助',
        ]);
    }

    public function testUpdateSponsorshipFailedSponsorshipNotExists()
    {
        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->put('/web/sponsorships/1', [
            '_token'                 => csrf_token(),
            'name'                   => 'b赞助',
            'intro'                  => 'b赞助内容',
            'application_start_date' => '2015-11-21',
            'application_end_date'   => '2015-11-23',
            'application_condition'  => 'b赞助申请条件',
        ], [
            'Referer' => '/web/sponsorships/1',
        ]);

        $this->assertRedirectedTo('/web/sponsorships');
    }

    public function testUpdateSponsorshipFailedWithInvalidSponsorship()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->put('/web/sponsorships/1', [
            '_token'                 => csrf_token(),
            'name'                   => 'b赞助',
            'intro'                  => 'b赞助内容',
            'application_start_date' => '2015-11-21',
            'application_end_date'   => '2015-11-23',
            'application_condition'  => 'b赞助申请条件',
        ], [
            'Referer' => '/web/sponsorships/1',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1');
        $this->assertSessionHasErrors(['message' => '不能修改赞助']);

        $this->seeInDatabase('sponsorships', [
            'id'   => 1,
            'name' => 'a赞助',
        ]);
    }

    public function testUpdateSponsorshipFailedWithoutPermission()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 2,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->put('/web/sponsorships/1', [
            '_token'                 => csrf_token(),
            'name'                   => 'b赞助',
            'intro'                  => 'b赞助内容',
            'application_start_date' => '2015-11-21',
            'application_end_date'   => '2015-11-23',
            'application_condition'  => 'b赞助申请条件',
        ], [
            'Referer' => '/web/sponsorships/1',
        ]);

        $this->assertRedirectedTo('/web/sponsorships');

        $this->seeInDatabase('sponsorships', [
            'id'   => 1,
            'name' => 'a赞助',
        ]);
    }

    //=========================================
    //      Destroy Sponsorship
    //=========================================
    public function testDestroySponsorshipSuccessfully()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PENDING,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->delete('/web/sponsorships/1', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships',
        ]);

        $this->assertRedirectedTo('/web/sponsorships');

        $this->notSeeInDatabase('sponsorships', [
            'id' => 1,
        ]);
    }

    public function testDestroySponsorshipSuccessfullySponsorshipNotExists()
    {
        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->delete('/web/sponsorships/1', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1',
        ]);

        $this->assertRedirectedTo('/web/sponsorships');
    }

    public function testDestroySponsorshipFailedWithoutPermission()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 2,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->delete('/web/sponsorships/1', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1',
        ]);

        $this->assertRedirectedTo('/web/sponsorships');

        $this->seeInDatabase('sponsorships', [
            'id'   => 1,
            'name' => 'a赞助',
        ]);
    }

    public function testDestroySponsorshipFailedWithInvalidSponsorship()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->delete('/web/sponsorships/1', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships',
        ]);

        $this->assertRedirectedTo('/web/sponsorships');
        $this->assertSessionHasErrors(['message' => '不能删除赞助']);

        $this->seeInDatabase('sponsorships', [
            'id' => 1,
        ]);
    }

    //=========================================
    //      Close Sponsorship
    //=========================================
    public function testCloseSponsorshipSuccessfully()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->post('/web/sponsorships/1/close', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1');

        $this->seeInDatabase('sponsorships', [
            'id'     => 1,
            'status' => \Sponsor\Models\Sponsorship::STATUS_CLOSED,
        ]);
    }

    public function testCloseSponsorshipFailedSponsorshipNotExists()
    {
        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->post('/web/sponsorships/1/close', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1');
    }

    public function testCloseSponsorshipFailedWithoutPermission()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 2,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->post('/web/sponsorships/1/close', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships/1',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1');

        $this->seeInDatabase('sponsorships', [
            'id'     => 1,
            'status' => \Sponsor\Models\Sponsorship::STATUS_PUBLISHED,
        ]);
    }

    public function testCloseSponsorshipFailedWithInvalidSponsorship()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_PENDING,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->post('/web/sponsorships/1/close', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1');
        $this->assertSessionHasErrors(['message' => '不能关闭赞助']);

        $this->seeInDatabase('sponsorships', [
            'id'     => 1,
            'status' => \Sponsor\Models\Sponsorship::STATUS_PENDING,
        ]);
    }

    public function testCloseSponsorshipSuccessfullyWithClosedSponsorship()
    {
        factory(\Sponsor\Models\Sponsorship::class)->create([
            'id'                     => 1,
            'sponsor_id'             => 1,
            'name'                   => 'a赞助',
            'intro'                  => 'a赞助内容',
            'application_start_date' => '2015-11-22',
            'application_end_date'   => '2015-11-25',
            'application_condition'  => 'a赞助申请条件',
            'status'                 => \Sponsor\Models\Sponsorship::STATUS_CLOSED,
        ]);

        $this->startSession();
        $this->actingAs(factory(\Sponsor\Models\Sponsor::class)->make([
            'id' => 1,
        ]))->post('/web/sponsorships/1/close', [
            '_token' => csrf_token(),
        ], [
            'Referer' => '/web/sponsorships',
        ]);

        $this->assertRedirectedTo('/web/sponsorships/1');

        $this->seeInDatabase('sponsorships', [
            'id'     => 1,
            'status' => \Sponsor\Models\Sponsorship::STATUS_CLOSED,
        ]);
    }
}
