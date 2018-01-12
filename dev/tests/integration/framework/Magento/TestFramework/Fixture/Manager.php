<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Fixture;

/**
 * Fixture manager.
 * Get ability to manage test fixtures.
 */
class Manager
{
    /**
     * @var FixtureInterface[]
     */
    private $fixtures = [];

    /**
     * Add fixture to manager by unique identifier.
     *
     * @param string $identifier
     * @param FixtureInterface $fixture
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @return void
     */
    public function add(string $identifier, FixtureInterface $fixture) : void
    {
        if (!empty($this->fixtures[$identifier])) {
            throw new \Magento\Framework\Exception\AlreadyExistsException(__('Fixture already exist.'));
        }
        $this->fixtures[$identifier] = $fixture;
    }

    /**
     * Get fixture by unique identifier.
     *
     * @param string $identifier
     * @return FixtureInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(string $identifier) : FixtureInterface
    {
        if (empty($this->fixtures[$identifier])) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested fixture is absent.'));
        }
        return $this->fixtures[$identifier];
    }

    /**
     * Get all stored fixtures.
     *
     * @return FixtureInterface[]
     */
    public function getAll() : array
    {
        return $this->fixtures;
    }

    /**
     * Rollback all fixtures stored in manager.
     *
     * @return void
     */
    public function rollbackAll() : void
    {
        foreach ($this->fixtures as $fixture) {
            $fixture->rollback();
        }
    }
}
