<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickup\Model\Order\Email\Container;

use Magento\Sales\Model\Order\Email\Container\Container;
use Magento\Sales\Model\Order\Email\Container\IdentityInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * @inheritdoc
 */
class ReadyForPickupIdentity extends Container implements IdentityInterface
{
    /**
     * Configuration paths
     */
    private const XML_PATH_EMAIL_COPY_METHOD = 'storepickup_email/order_ready_for_pickup/copy_method';
    private const XML_PATH_EMAIL_COPY_TO = 'storepickup_email/order_ready_for_pickup/copy_to';
    private const XML_PATH_EMAIL_IDENTITY = 'storepickup_email/order_ready_for_pickup/identity';
    private const XML_PATH_EMAIL_GUEST_TEMPLATE = 'storepickup_email/order_ready_for_pickup/guest_template';
    private const XML_PATH_EMAIL_TEMPLATE = 'storepickup_email/order_ready_for_pickup/template';
    private const XML_PATH_EMAIL_ENABLED = 'storepickup_email/order_ready_for_pickup/enabled';

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * @inheritdoc
     */
    public function getEmailCopyTo()
    {
        $data = $this->getConfigValue(self::XML_PATH_EMAIL_COPY_TO, $this->getStore()->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getCopyMethod()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStore()->getStoreId());
    }

    /**
     * @inheritdoc
     */
    public function getGuestTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * @inheritdoc
     */
    public function getTemplateId()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_TEMPLATE, $this->getStore()->getStoreId());
    }

    /**
     * @inheritdoc
     */
    public function getEmailIdentity()
    {
        return $this->getConfigValue(self::XML_PATH_EMAIL_IDENTITY, $this->getStore()->getStoreId());
    }
}
