<?php
/**
 * Created by PhpStorm.
 * User: rjurgens
 * Date: 19/10/2017
 * Time: 15.57
 */

namespace App\Google;

use App\Entity\Security\Group;
use App\Entity\Security\User;

/**
 * Class GoogleGroup
 * @package App\GoogleAPI
 * @author Robert Jürgens <robert@jurgens.fi>
 * @copyright Fma Jürgens 2017, All rights reserved.
 */
class GoogleGroup
{
    /**
     * Gets a Google group based on a Symfony Group and a search criteria within the Google App.
     *
     * @param Group $grp
     * @param string $criteria
     * @return \Google_Service_Directory_Group|null
     */
    public static function getGroup(Group $grp, $criteria = 'GoogleId')
    {
        try {
            $service = GoogleAPIClient::serviceInstance();
            $getter = 'get' . $criteria;
            $group = $service->groups->get($grp->$getter());
            return $group;
        } catch (\Google_Exception $e) {
            return null;
        }
    }

    /**
     * Creates a Google group based on a Symfony Group.
     *
     * @param Group $grp
     * @return \Google_Service_Directory_Group|null
     */
    public static function createGroup(Group $grp)
    {
        try {
            $service = GoogleAPIClient::serviceInstance();
            if (($group = self::getGroup($grp, 'Email')) === null) {
                $group = new \Google_Service_Directory_Group();
                $group->setEmail($grp->getEmail());
                $group->setName($grp->getName());
                $group = $service->groups->insert($group);
                self::addMembers($group, $grp->getUsers());
                return $group;
            }
        } catch (\Google_Exception $e) {
                return null;
        }
        return null;
    }

    /**
     * Updates a Google Group based on a Symfony Group.
     *
     * @param Group $grp
     * @return \Google_Service_Directory_Group|null
     */
    public static function updateGroup(Group $grp)
    {
        try {
            $service = GoogleAPIClient::serviceInstance();
            if (($group = self::getGroup($grp, 'GoogleId')) !== null) {
                $group->setEmail($grp->getEmail());
                $group->setName($grp->getName());

                $group = $service->groups->update($group->getId(), $group);

                self::setMembers($group, $grp->getUsers());
                return $group;
            }
        } catch (\Google_Exception $e) {
            return null;
        }
        return null;
    }

    /**
     * Removes a Google Group.
     *
     * @param \Google_Service_Directory_Group $grp
     * @return bool
     */
    public static function removeGroup(\Google_Service_Directory_Group $grp)
    {
        try {
            $service = GoogleAPIClient::serviceInstance();
            $service->groups->delete($grp->getId());
            return true;
        } catch (\Google_Exception $e) {
            return false;
        }
    }

    /**
     * Adds users to a Google group.
     *
     * @param \Google_Service_Directory_Group $grp
     * @param array $users
     * @return int
     */
    public static function addMembers(\Google_Service_Directory_Group $grp, $users)
    {
        $count = 0;
        /** @var User $user */
        foreach ($users as $user) {
            try {
                $service = GoogleAPIClient::serviceInstance();
                $member = new \Google_Service_Directory_Member();
                $member->setEmail($user->getGoogleEmail());
                $member->setRole('MEMBER');
                $service->members->insert($grp->getId(), $member);
                $count++;
            } catch (\Google_Exception $e) {
                continue;
            }
        }
        return $count;
    }

    /**
     * Sets the members of a Google group.
     *
     * @param \Google_Service_Directory_Group $grp
     * @param array $users
     * @throws \Google_Exception
     */
    public static function setMembers(\Google_Service_Directory_Group $grp, $users)
    {
        $service = GoogleAPIClient::serviceInstance();
        $ulist = array_map(function(User $a) { return $a->getEmail(); }, $users);
        /** @var \Google_Service_Directory_Member $mbr */
        foreach ($service->members->listMembers($grp->getId()) as $mbr) {
            if (!in_array($mbr->getEmail(), $ulist) && $mbr->getRole() === 'MEMBER')
                $service->members->delete($grp->getId(), $mbr->getId());
        }
        self::addMembers($grp, $users);
    }

    /**
     * Removes members of a Google group.
     *
     * @param \Google_Service_Directory_Group $grp
     * @param array $users
     * @throws \Google_Exception
     */
    public static function removeMembers(\Google_Service_Directory_Group $grp, $users)
    {
        $service = GoogleAPIClient::serviceInstance();
        /** @var User $user */
        foreach ($users as $user) {
            $service->members->delete($grp->getId(), $user->getEmail());
        }
    }

    /**
     * Refreshes the Google group, refetches it from the directory.
     *
     * @param \Google_Service_Directory_Group $grp
     * @return \Google_Service_Directory_Group
     * @throws \Google_Exception
     */
    public static function refresh(\Google_Service_Directory_Group $grp)
    {
        $service = GoogleAPIClient::serviceInstance();
        return $service->groups->get($grp->getId() ? $grp->getId() : $grp->getEmail());
    }

}