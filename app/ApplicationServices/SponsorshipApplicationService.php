<?php
namespace Sponsor\ApplicationServices;

use Sponsor\Contracts\Repositories\SponsorshipApplicationRepository;
use Sponsor\Models\Sponsor;
use Sponsor\Models\SponsorshipApplication;

class SponsorshipApplicationService
{
    /**
     * @param Sponsor $sponsor
     * @param         $sponsorshipId
     * @param         $page
     * @param         $size
     * @return mixed
     * @throws \Exception
     */
    public function listApplications(Sponsor $sponsor, $sponsorshipId, $page, $size)
    {
        $sponsorship = $this->getSponsorshipRepository()->findSponsorshipFor($sponsor->id, $sponsorshipId);
        if (is_null($sponsorship)) {
            throw new \Exception('不存在此赞助');
        }

        return $this->getSponsorshpApplicationRepository()->findApplications($sponsorship->id, $page, $size);
    }

    /**
     * @param $team
     * @param $page
     * @param $size
     * @return mixed
     */
    public function listApplicationsOf($team, $page, $size)
    {
        // Todo: team validation
        return $this->getSponsorshpApplicationRepository()->findApplicationsOf($team, $page, $size);
    }

    /**
     * store sponsorship application
     *
     * @param SponsorshipApplication $application
     * @return bool
     * @throws \Exception
     */
    public function store(SponsorshipApplication $application)
    {
        $sponsorship = $this->getSponsorshipRepository()->findSponsorship($application->sponsorship_id);
        if (is_null($sponsorship)) {
            throw new \Exception('不存在赞助');
        }
        if (!$this->getSponsorshipDomainService()->canApply($sponsorship)) {
            throw new \Exception('不能申请已过期赞助');
        }

        return $this->getSponsorshipApplicationDomainService()->store($application);
    }

    /**
     * approve given sponsorship application initiated by given sponsor
     *
     * @param Sponsor $sponsor
     * @param         $applicationId
     * @param         $memo
     * @return bool
     * @throws \Exception
     */
    public function approve(Sponsor $sponsor, $applicationId, $memo)
    {
        $application = $this->getSponsorshpApplicationRepository()->findApplication($applicationId);
        if (is_null($application)) {
            throw new \Exception('不存在此申请');
        }

        $sponsorship = $this->getSponsorshipRepository()->findSponsorshipFor($sponsor->id, $application->sponsorship_id);
        if (is_null($sponsorship)) {
            throw new \Exception('不能处理此赞助');
        }

        return $this->getSponsorshipApplicationDomainService()->approve($application, $memo);
    }

    /**
     * reject given sponsorship application initiated by given sponsor
     *
     * @param Sponsor $sponsor
     * @param         $applicationId
     * @param         $memo
     * @return bool
     * @throws \Exception
     */
    public function reject(Sponsor $sponsor, $applicationId, $memo)
    {
        $application = $this->getSponsorshpApplicationRepository()->findApplication($applicationId);
        if (is_null($application)) {
            throw new \Exception('不存在此申请');
        }

        $sponsorship = $this->getSponsorshipRepository()->findSponsorshipFor($sponsor->id, $application->sponsorship_id);
        if (is_null($sponsorship)) {
            throw new \Exception('不能处理此赞助');
        }

        return $this->getSponsorshipApplicationDomainService()->reject($application, $memo);
    }

    /**
     * @return \Sponsor\Contracts\Repositories\SponsorshipRepository
     */
    private function getSponsorshipRepository()
    {
        return app(\Sponsor\Contracts\Repositories\SponsorshipRepository::class);
    }

    /**
     * @return \Sponsor\Contracts\Repositories\SponsorshipApplicationRepository
     */
    private function getSponsorshpApplicationRepository()
    {
        return app(\Sponsor\Contracts\Repositories\SponsorshipApplicationRepository::class);
    }

    /**
     * @return \Sponsor\Services\SponsorshipApplicationService
     */
    private function getSponsorshipApplicationDomainService()
    {
        return app(\Sponsor\Services\SponsorshipApplicationService::class);
    }

    /**
     * @return \Sponsor\Services\SponsorshipService
     */
    private function getSponsorshipDomainService()
    {
        return app(\Sponsor\Services\SponsorshipService::class);
    }
}
