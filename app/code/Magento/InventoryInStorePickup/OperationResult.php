<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryInStorePickup;

use \Magento\InventoryInStorePickupApi\Api\Data\OperationResultInterface;
use Magento\InventoryInStorePickupApi\Api\Data\OperationResultExtensionInterface;

/**
 * Operation result object that contains statuses for each operation.
 */
class OperationResult implements OperationResultInterface
{
    /**
     * @var OperationResultExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @var array
     */
    private $succeeded;

    /**
     * @var array
     */
    private $failed;

    /**
     * @param array $succeeded
     * @param array $failed
     */
    public function __construct(array $succeeded = [], array $failed = [])
    {
        $this->succeeded = $succeeded;
        $this->failed = $failed;
    }

    /**
     * @inheritdoc
     */
    public function getSucceeded() : array
    {
        return $this->succeeded;
    }

    /**
     * @inheritdoc
     */
    public function getFailed() : array
    {
        return $this->failed;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(?OperationResultExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?OperationResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
