<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventoryInStorePickupApi\Api;

use Magento\InventoryInStorePickupApi\Api\Data\OperationResultInterface;

/**
 * A service which provides info if order is placed using In-store pickup.
 *
 * @api
 */
interface IsStorePickupOrderInterface
{
    /**
     * Check if order with the specified id was places with store-pickup.
     *
     * @param int[] $orderIds
     * @return OperationResultInterface
     */
    public function execute(array $orderIds) : OperationResultInterface;
}
