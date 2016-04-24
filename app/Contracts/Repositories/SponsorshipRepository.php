<?php
namespace Sponsor\Contracts\Repositories;

interface SponsorshipRepository
{
    /**
     * find sponsorship initiated by given sponsor
     * @param $sponsorId              id of the sponsor who initiated the sponsor
     * @param null $sponsorShipId     id of the sponsorship
     * @return \Sponsor\Models\Sponsorship|null
     */
    public function findSponsorshipFor($sponsorId, $sponsorShipId);

    /**
     * find sponsorship
     * @param $sponsorshipId     id of the sponsorship
     * @return \Sponsor\Models\Sponsorship|null
     */
    public function findSponsorship($sponsorshipId);

    /**
     * find sponsorships by given criteria
     * @param       $page        page of pagination
     * @param       $size        size of pagination
     * @param array $criteria    keys taken:
     *                            - sponsor
     *                            - relations [sponsor]
     *                            - with_expired (boolean)true default
     *                            - only_published (boolean)false default
     * @return \Sponsor\Models\Sponsorship|null
     */
    public function findSponsorships($page, $size, $criteria = []);
}
