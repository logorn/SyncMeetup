<?php

namespace GoogleCal\Repository;

use Doctrine\DBAL\Connection;
use GoogleCal\Entity\GoogleDetails;

/**
 * MeetupDetails repository
 */
class GoogleDetailsRepository
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function find($id)
    {
        $googleDetailsData = $this->db->fetchAssoc('SELECT * FROM google_details WHERE id = ?', array($id));
        $googleDetails = $this->buildGoogleDetails($googleDetailsData);
        return $googleDetails;
    }

    public function save(GoogleDetails $googleDetails)
    {
        $googleDetailsData = array(
            'access_token' => $googleDetails->getAccessToken(),
            'refresh_token' => $googleDetails->getRefreshToken(),
            'calendar' => $googleDetails->getCalendar()
        );

        $expires = $googleDetails->getExpires();
        if ($expires != null) {
            $googleDetailsData = array_merge($googleDetailsData, array('expires' => $expires->format('Y-m-d H:i:s')));
        }

        $googleDetailsId = $googleDetails->getId();
        if ($googleDetailsId) {
            $this->db->update('google_details', $googleDetailsData, array('id' => $googleDetailsId));
        } else {
            $this->db->insert('google_details', $googleDetailsData);
            $id = $this->db->lastInsertId();
            $googleDetails->setId($id);
        }

        return $googleDetails;
    }

    private function buildGoogleDetails($googleDetailsData)
    {
        if (!$googleDetailsData) {
            return null;
        }

        $googleDetails = new GoogleDetails();
        $googleDetails->setId($googleDetailsData['id']);
        $googleDetails->setAccessToken($googleDetailsData['access_token']);
        $googleDetails->setRefreshToken($googleDetailsData['refresh_token']);
        $googleDetails->setCalendar($googleDetailsData['calendar']);
        if ($googleDetailsData['expires'] != null) {
            $expires = new \DateTime($googleDetailsData['expires']);
            $googleDetails->setExpires($expires);
        }
        return $googleDetails;
    }
}