<?php
namespace Sponsor\ApplicationServices;

use Sponsor\Contracts\Repositories\SponsorshipRepository;
use Sponsor\Exceptions\SponsorshipNotFoundException;
use Sponsor\Services\SponsorshipService as SponsorshipDomainService;

class SponsorshipService
{
    public function postponeApplication($sponsorId, $sponsorshipId, $postponeTo)
    {
        $repository = $this->getSponsorshpRepository();
        $sponsorship = $repository->findSponsorshipFor($sponsorId, $sponsorshipId);
        if ($sponsorship == null) {
            throw new SponsorshipNotFoundException($sponsorshipId, $sponsorId);
        }

        $this->getSponsorshipService()->postponeApplication($sponsorship, $postponeTo);
    }

    public function destroy($sponsorId, $sponsorshipId)
    {
        $repository = $this->getSponsorshpRepository();
        $sponsorship = $repository->findSponsorshipFor($sponsorId, $sponsorshipId);
        if ($sponsorship == null) {
            return true;
        }

        return $this->getSponsorshipService()->destroy($sponsorship);
    }

    public function close($sponsorId, $sponsorshipId)
    {
        $repository = $this->getSponsorshpRepository();
        $sponsorship = $repository->findSponsorshipFor($sponsorId, $sponsorshipId);
        if ($sponsorship == null) {
            return true;
        }

        return $this->getSponsorshipService()->close($sponsorship);
    }

    /**
     * @return \Sponsor\Contracts\Repositories\SponsorshipRepository
     */
    private function getSponsorshpRepository()
    {
        return app(SponsorshipRepository::class);
    }

    /**
     * @return \Sponsor\Services\SponsorshipService
     */
    private function getSponsorshipService()
    {
        return app(SponsorshipDomainService::class);
    }
}
