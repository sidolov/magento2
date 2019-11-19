<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryInStorePickupApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\InventoryInStorePickupApi\Api\Data\OperationResultExtensionInterface;

/**
 * Operation result interface with statuses for each operation.
 *
 * @api
 */
interface OperationResultInterface extends ExtensibleDataInterface
{
    /**
     * Get successfully finished operation ids.
     *
     * @return array
     */
    public function getSucceeded() : array;

    /**
     * Get error information for failed operations.
     *
     * @return array
     */
    public function getFailed() : array;

    /**
     * Set Extension Attributes for Operation result.
     *
     * @param \Magento\InventoryInStorePickupApi\Api\Data\OperationResultExtensionInterface|null $extensionAttributes
     *
     * @return void
     */
    public function setExtensionAttributes(?OperationResultExtensionInterface $extensionAttributes): void;

    /**
     * Get Extension Attributes of Operation result.
     *
     * @return \Magento\InventoryInStorePickupApi\Api\Data\OperationResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?OperationResultExtensionInterface;
}
