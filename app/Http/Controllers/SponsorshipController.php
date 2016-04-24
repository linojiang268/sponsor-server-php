<?php
namespace Sponsor\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Sponsor\ApplicationServices\SponsorshipService as SponsorshipApplicationService;
use Sponsor\Contracts\Repositories\SponsorshipApplicationRepository;
use Sponsor\Contracts\Repositories\SponsorshipRepository;
use Sponsor\Exceptions\SponsorshipNotFoundException;
use Sponsor\Models\Sponsorship;
use Sponsor\Services\SponsorshipService;
use Sponsor\Utils\PaginationUtil;

class SponsorshipController extends Controller
{
    /**
     * list sponsorships initiated by current user
     *
     * @param Request $request
     * @param Guard   $auth
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Guard $auth, SponsorshipService $service)
    {
        return view('sponsorship.index');
    }

    public function paged(Request $request, Guard $auth, SponsorshipService $service, $page)
    {
        $size = $this->getDefaultPageSize();
        list($sponsorships, $total) = $service->listSponsorshipsFor($auth->user(),
            $this->sanePage($page), $size);

        return $this->json([
            'sponsorships' => $sponsorships,
            'totalPages' => PaginationUtil::computePages($total, $size),
        ]);
    }

    /**
     * @param Request               $request
     * @param SponsorshipRepository $repository
     * @return \Illuminate\Http\JsonResponse
     */
    public function sponsorships(Guard $auth, Request $request, SponsorshipRepository $repository, SponsorshipApplicationRepository $applicationRepository)
    {
        list($page, $size) = $this->sanePageAndSize($request);
        list($total, $sponsorships) = $repository->findSponsorships(
            $page, $size,
            [
                'with_expired'   => false,
                'only_published' => true,
                'relations'      => ['sponsor'],
            ]
        );

        if ($auth->user()) {
            $sponsorIds = array_map(function (Sponsorship $sponsorship) {
                return $sponsorship->id;
            }, $sponsorships);

            $stat = $applicationRepository->findApplicationStatus(
                $sponsorIds, $auth->user()->getAuthIdentifier());

            $rstSponsorships = array_map(function (Sponsorship $sponsorship) use ($stat) {
                $rstSponsorship = $this->morphSponsorship($sponsorship);
                $rstSponsorship['apply_status'] = $stat[$sponsorship->id];
                return $rstSponsorship;
            }, $sponsorships);
        }

        return $this->json([
            'total'        => $total,
            'totalPages'   => PaginationUtil::computePages($total, $size),
            'sponsorships' => isset($rstSponsorships) ? $rstSponsorships : array_map(function (Sponsorship $sponsorship) {
                return $this->morphSponsorship($sponsorship);
            }, $sponsorships),
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
        return view('sponsorship.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'                   => 'required',
            'intro'                  => 'required|max:255',
            'application_start_date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
            'application_end_date'   => 'required|date_format:Y-m-d|after:application_start_date',
            'application_condition'  => 'required|max:255',
            'status'                 => 'required|integer|in:' . implode(',', [Sponsorship::STATUS_PENDING, Sponsorship::STATUS_PUBLISHED]),

        ], [
            'name.required'                      => '名称未填写',
            'name.max'                           => '名称错误',
            'intro.required'                     => '简介未填写',
            'intro.max'                          => '简介错误',
            'application_start_date.required'    => '申请开始时间未填写',
            'application_start_date.date_format' => '申请开始时间错误',
            'application_start_date.after'       => '申请开始时间不能小于今天',
            'application_end_date.required'      => '申请结束时间未填写',
            'application_end_date.date_format'   => '申请结束时间错误',
            'application_end_date.after'         => '申请结束时间不能小于开始时间',
            'application_condition.required'     => '申请条件未填写',
            'application_condition.max'          => '申请条件错误',
            'status.required'                    => '参数提交非法',
            'status.integer'                     => '参数提交非法',
        ]);

        $sponsorship = new Sponsorship([
            'name'                   => $request->get('name'),
            'sponsor_id'             => $request->user()->getAuthIdentifier(),
            'application_start_date' => $request->get('application_start_date'),
            'application_end_date'   => $request->get('application_end_date'),
            'application_condition'  => $request->get('application_condition'),
            'intro'                  => $request->get('intro'),
            'status'                 => $request->get('status'),
        ]);
        $sponsorship->save();

        return $request->ajax() ? $this->json(['redirect' => '/web/sponsorships']) : redirect('/web/sponsorships');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @param Guard   $auth
     * @param SponsorshipRepository $repository
     * @return \Illuminate\Http\Response
     */
    public function show($id, Guard $auth, SponsorshipRepository $repository)
    {
        $sponsorship = $repository->findSponsorshipFor($auth->user()->getAuthIdentifier(), $id);

        return view('sponsorship.show', [ 'sponsorship' => $sponsorship]);
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
    public function update(Request $request, $id, Guard $auth, SponsorshipRepository $repository, SponsorshipService $sponsorshipService)
    {
        $this->validate($request, [
            'name'                   => 'required|max:128',
            'intro'                  => 'required|max:255',
            'application_start_date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
            'application_end_date'   => 'required|date_format:Y-m-d|after:application_start_date',
            'application_condition'  => 'required|max:255',
            'status'                 => 'integer|in:' . implode(',', [Sponsorship::STATUS_PENDING, Sponsorship::STATUS_PUBLISHED]),
        ], [
            'name.required'                      => '名称未填写',
            'name.max'                           => '名称错误',
            'intro.required'                     => '简介未填写',
            'intro.max'                          => '简介错误',
            'application_start_date.required'    => '申请开始时间未填写',
            'application_start_date.date_format' => '申请开始时间错误',
            'application_start_date.after'       => '申请开始时间不能小于今天',
            'application_end_date.required'      => '申请结束时间未填写',
            'application_end_date.date_format'   => '申请结束时间错误',
            'application_end_date.after'         => '申请结束时间不能小于开始时间',
            'application_condition.required'     => '申请条件未填写',
            'application_condition.max'          => '申请条件错误',
            'status.integer'                     => '参数提交非法',
        ]);

        $sponsorship = $repository->findSponsorshipFor($auth->user()->getAuthIdentifier(), intval($id));
        if (is_null($sponsorship)) {
            return $request->ajax() ? $this->jsonException('非法操作') : redirect('/web/sponsorships');
        }
        $error = null;
        try {
            $ret = $sponsorshipService->update($sponsorship, [
                'name'                   => $request->get('name'),
                'intro'                  => $request->get('intro'),
                'application_start_date' => $request->get('application_start_date'),
                'application_end_date'   => $request->get('application_end_date'),
                'application_condition'  => $request->get('application_condition'),
                'status'                 => intval($request->get('status')),
            ]);
        } catch(\Exception $ex) {
            $error = $ex->getMessage();
            if($request->ajax()){
                return $this->jsonException($ex->getMessage());
            }
        }
        return $request->ajax() ? $this->json(['redirect' => '/web/sponsorships/' . $id]) : redirect('/web/sponsorships/' . $id)->withErrors(['message' => $error]);
    }

    public function postponeApplication(Request $request, Guard $auth, $id, SponsorshipApplicationService $service)
    {
        $this->validate($request, [
            'application_end_date' => 'required|date_format:Y-m-d',
        ]);

        $error = null;
        try {
            $service->postponeApplication($auth->user()->getAuthIdentifier(), $id, $request->get('application_end_date'));
        } catch (SponsorshipNotFoundException $ex) {
            return $request->ajax() ? $this->jsonException($ex->getMessage()) : redirect('/web/sponsorships');

        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            if($request->ajax()){
               return $this->jsonException($ex->getMessage());
            }
        }
        return $request->ajax() ? $this->json(['redirect' => '/web/sponsorships/' . $id]) : redirect('/web/sponsorships/' . $id)->withErrors(['message' => $error]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Guard $auth, SponsorshipApplicationService $sponsorshipApplicationService, Request $request)
    {
        $error = null;
        try {
            $sponsorshipApplicationService->destroy($auth->user()->getAuthIdentifier(), $id);
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            if($request->ajax()){
                return $this->jsonException($ex->getMessage());
            }
        }

        return $request->ajax() ? $this->json(['redirect' => '/web/sponsorships']) : redirect('/web/sponsorships')->withErrors(['message' => $error]);
    }

    /**
     * Close given sponsorship
     *
     * @param                               $id
     * @param Guard                         $auth
     * @param SponsorshipApplicationService $sponsorshipApplicationService
     * @return \Illuminate\Http\Response
     */
    public function close($id, Guard $auth, SponsorshipApplicationService $sponsorshipApplicationService, Request $request)
    {
        $error = null;
        try {
            $sponsorshipApplicationService->close($auth->user()->getAuthIdentifier(), $id);
        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            if($request->ajax()){
                return $this->jsonException($ex->getMessage());
            }
        }

        return $request->ajax() ? $this->json(['redirect' => '/web/sponsorships/' . $id]) : redirect('/web/sponsorships/' . $id)->withErrors(['message' => $error]);
    }
}
