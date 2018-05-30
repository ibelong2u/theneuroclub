<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Sarp\Model;

use Aheadworks\Sarp\Api\Data\ProfileInterface;
use Aheadworks\Sarp\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ProfileRegistry
 * @package Aheadworks\Sarp\Model
 */
class ProfileRegistry
{
    /**
     * @var ProfileInterface[]
     */
    private $instancesById = [];

    /**
     * @var ProfileInterface[]
     */
    private $instancesByReferenceId = [];

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ProfileResource
     */
    private $resource;

    /**
     * @var ProfileInterfaceFactory
     */
    private $profileFactory;

    /**
     * @param EntityManager $entityManager
     * @param ProfileResource $resource
     * @param ProfileInterfaceFactory $profileFactory
     */
    public function __construct(
        EntityManager $entityManager,
        ProfileResource $resource,
        ProfileInterfaceFactory $profileFactory
    ) {
        $this->entityManager = $entityManager;
        $this->resource = $resource;
        $this->profileFactory = $profileFactory;
    }

    /**
     * Retrieve profile using profile Id
     *
     * @param int $profileId
     * @return ProfileInterface
     * @throws NoSuchEntityException
     */
    public function retrieve($profileId)
    {
        if (!isset($this->instancesById[$profileId])) {
            /** @var ProfileInterface $profile */
            $profile = $this->profileFactory->create();
            $this->entityManager->load($profile, $profileId);
            if (!$profile->getProfileId()) {
                throw NoSuchEntityException::singleField('profileId', $profileId);
            }
            $this->instancesById[$profileId] = $profile;
            $this->instancesByReferenceId[$profile->getReferenceId()] = $profile;
        }
        return $this->instancesById[$profileId];
    }

    /**
     * Retrieve profile using profile reference Id
     *
     * @param string $referenceId
     * @return ProfileInterface
     * @throws NoSuchEntityException
     */
    public function retrieveByReferenceId($referenceId)
    {
        if (!isset($this->instancesByReferenceId[$referenceId])) {
            $profileId = $this->resource->getProfileIdByReferenceId($referenceId);
            if (!$profileId) {
                throw NoSuchEntityException::singleField('referenceId', $referenceId);
            }
            $this->instancesByReferenceId[$referenceId] = $this->retrieve($profileId);
        }
        return $this->instancesByReferenceId[$referenceId];
    }

    /**
     * Remove profile from registry
     *
     * @param int $profileId
     * @return void
     */
    public function remove($profileId)
    {
        if (isset($this->instancesById[$profileId])) {
            $profile = $this->instancesById[$profileId];
            unset($this->instancesById[$profileId]);
            unset($this->instancesByReferenceId[$profile->getReferenceId()]);
        }
    }

    /**
     * Replace existing profile instance with a new one
     *
     * @param ProfileInterface $profile
     * @return void
     */
    public function push(ProfileInterface $profile)
    {
        $this->instancesById[$profile->getProfileId()] = $profile;
        $this->instancesById[$profile->getReferenceId()] = $profile;
    }
}
