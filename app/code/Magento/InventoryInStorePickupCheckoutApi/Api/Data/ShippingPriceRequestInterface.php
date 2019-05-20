<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickupCheckoutApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\InventoryInStorePickupCheckoutApi\Api\Data\ShippingPriceRequestExtensionInterface;

/**
 * Shipping Price Request Data Transfer Object.
 *
 * @api
 */
interface ShippingPriceRequestInterface extends ExtensibleDataInterface
{
    /**
     * Get Default Price of In-Store Pickup Delivery.
     *
     * @return float
     */
    public function getDefaultPrice(): float;

    /**
     * Get count of free boxes.
     *
     * @return float
     */
    public function getFreePackages(): float;

    /**
     * @param \Magento\InventoryInStorePickupCheckoutApi\Api\Data\ShippingPriceRequestExtensionInterface|null $extensionAttributes
     *
     * @return void
     */
    public function setExtensionAttributes(?ShippingPriceRequestExtensionInterface $extensionAttributes): void;

    /**
     * @return \Magento\InventoryInStorePickupCheckoutApi\Api\Data\ShippingPriceRequestExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ShippingPriceRequestExtensionInterface;
}
