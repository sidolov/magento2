<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickupApi\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryInStorePickupApi\Api\Data\OperationResultInterface;

/**
 * Send an email to the customer that order is ready to be picked up.
 *
 * @api
 */
interface NotifyOrderIsReadyForPickupInterface
{
    /**
     * Notify customer that the order is ready for pickup.
     *
     * @param int[] $orderIds
     *
     * @return OperationResultInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(array $orderIds) : OperationResultInterface;
}
