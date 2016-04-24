<?php
namespace Sponsor\Exceptions;

use Exception;

class SponsorshipNotFoundException extends \Exception
{
    private $sponsorshipId;
    private $sponsorId;

    /**
     * @inheritDoc
     */
    public function __construct($sponsorshipId, $sponsorId = null)
    {
        parent::__construct(sprintf('sponsor[%d] not found', $sponsorshipId), 1011);

        $this->sponsorshipId = $sponsorshipId;
        $this->sponsorId = $sponsorId;
    }

    public function getSponsorshipId()
    {
        return $this->sponsorshipId;
    }

    public function getSponsorId()
    {
        return $this->sponsorId;
    }
}