<?php

namespace Sponsor\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Sponsor\ApplicationServices\SponsorshipApplicationService as SponsorshipApplicationApplicationService;
use Sponsor\Models\SponsorshipApplication;
use Sponsor\Models\Sponsorship;
use Sponsor\Utils\PaginationUtil;

class SponsorshipApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Guard $auth, $sponsorship, SponsorshipApplicationApplicationService $service)
    {
        list($page, $size) = $this->sanePageAndSize($request);

        try {
            list($applications, $total) = $service->listApplications($auth->user(), $sponsorship, $page, $size);
            return $this->json([
                'applications' => $applications,
                'totalPages' => PaginationUtil::computePages($total, $size),
            ]);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }
    }

    public function appliedSponsorships(Guard $auth, Request $request, SponsorshipApplicationApplicationService $service)
    {
        list($page, $size) = $this->sanePageAndSize($request);
        list($total, $applications) = $service->listApplicationsOf($auth->user()->getAuthIdentifier(), $page, $size);

        return $this->json([
            'total'        => $total,
            'totalPages'   => PaginationUtil::computePages($total, $size),
            'sponsorships' => array_map(function (SponsorshipApplication $application) {
                $sponsorship = $this->morphSponsorship($application->sponsorship);
                $sponsorship['apply_status'] = $application->status;
                return $sponsorship;
            }, $applications),
        ]);
    }

    private function morphSponsorship(Sponsorship $sponsorship)
    {
        return [
            'id'         => $sponsorship->id,
            'name'       => $sponsorship->name,
            'sponsor_id' => $sponsorship->sponsor_id,
            'sponsor_name' => is_null($sponsorship->sponsor) ? null : $sponsorship->sponsor->name,
            'team_id'    => $sponsorship->team_id,
            'intro'      => $sponsorship->intro,
            'application_start_date' => $sponsorship->application_start_date,
            'application_end_date' => $sponsorship->application_end_date,
            'application_condition' => $sponsorship->application_condition,
            'status'     => $sponsorship->status,
            'updated_at' => $sponsorship->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Guard $auth, Request $request, $sponsorship, SponsorshipApplicationApplicationService $service)
    {
        $this->validate($request, [
            'team'               => 'integer',
            'team_name'          => 'max:32',
            'mobile'             => 'mobile',
            'contact_user'       => 'max:32',
            'application_reason' => 'max:128',
        ], [
            'team.integer'         => '社团格式错误',
            'team_name.max'        => '社团名称格式错误',
            'mobile.mobile'        => '手机号格式错误',
            'contact_user'         => '联系人格式错误',
            'application_reason'   => '申请说明格式错误',
        ]);

        $application = new SponsorshipApplication([
            'sponsorship_id'     => $sponsorship,
            'team_id'            => $auth->user()->getAuthIdentifier(),
            'team_name'          => $request->get('team_name'),
            'mobile'             => $request->get('mobile'),
            'contact_user'       => $request->get('contact_user'),
            'application_reason' => $request->get('application_reason'),
            'status'             => SponsorshipApplication::STATUS_PENDING,
        ]);

        try {
            $service->store($application);
        } catch (\Exception $ex) {
            return $this->jsonException($ex);
        }

        return $this->json([
            'id' => $application->id,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * approve sponsorship application
     *
     * @param Guard                                    $auth
     * @param                                          $sponsorship
     * @param                                          $id
     * @param SponsorshipApplicationApplicationService $service
     * @return $this
     */
    public function approve(Request $request, Guard $auth, $sponsorship, $id, SponsorshipApplicationApplicationService $service)
    {
        $this->validate($request, [
            'memo' => 'max:128',
        ], [
            'memo.max' => '备注格式错误',
        ]);

        $message = null;
        try {
            $service->approve($auth->user(), $id, $request->get('memo'));
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            if($request->ajax()){
                return $this->jsonException($ex->getMessage());
            }
        }

        return $request->ajax() ? $this->json(['redirect' => '/web/sponsorships']) : redirect('/web/sponsorships/' . $sponsorship . '/applications')->withErrors(['message' => $message]);
    }

    /**
     * reject sponsorship application
     *
     * @param Request                                  $request
     * @param Guard                                    $auth
     * @param                                          $sponsorship
     * @param SponsorshipApplicationApplicationService $service
     * @return $this
     */
    public function reject(Request $request, Guard $auth, $sponsorship, $id, SponsorshipApplicationApplicationService $service)
    {
        $this->validate($request, [
            'memo' => 'max:128',
        ], [
            'memo.max' => '备注格式错误',
        ]);

        $message = null;
        try {
            $service->reject($auth->user(), $id, $request->get('memo'));
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            if($request->ajax()){
                return $this->jsonException($ex->getMessage());
            }
        }

        return $request->ajax() ? $this->json(['redirect' => '/web/sponsorships']) : redirect('/web/sponsorships/' . $sponsorship . '/applications')->withErrors(['message' => $message]);
    }
}
