<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Fixture;

/**
 * Interface for integration fixtures.
 */
interface FixtureInterface
{
    /**
     * Returns fixture data.
     *
     * @return \Magento\Framework\DataObject
     */
    public function getData() : \Magento\Framework\DataObject;

    /**
     * Save fixture data to storage.
     *
     * @return void
     */
    public function persist() : void;

    /**
     * Rollback fixture data.
     *
     * @return void
     */
    public function rollback() : void;
}
