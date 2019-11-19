<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\InventoryInStorePickup\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryInStorePickup\Model\Order\GetPickupLocationCode;
use Magento\InventoryInStorePickupApi\Api\Data\OperationResultInterface;
use Magento\InventoryInStorePickupApi\Api\Data\OperationResultInterfaceFactory;
use Magento\InventoryInStorePickupApi\Api\IsStorePickupOrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * @inheritDoc
 */
class IsStorePickupOrder implements IsStorePickupOrderInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var GetPickupLocationCode
     */
    private $getPickupLocationCode;

    /**
     * @var OperationResultInterfaceFactory
     */
    private $operationResultFactory;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param GetPickupLocationCode $getPickupLocationCode
     * @param OperationResultInterfaceFactory $operationResultFactory
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        GetPickupLocationCode $getPickupLocationCode,
        OperationResultInterfaceFactory $operationResultFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->getPickupLocationCode = $getPickupLocationCode;
        $this->operationResultFactory = $operationResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $orderIds) : OperationResultInterface
    {
        $success = [];
        $errors = [];
        foreach ($orderIds as $orderId) {
            try {
                $order = $this->orderRepository->get($orderId);
            } catch (NoSuchEntityException $exception) {
                $errors[] = [
                    'order_id' => $orderId,
                    'message' => $exception->getMessage()
                ];
                continue;
            }

            if (!$this->getPickupLocationCode->execute($order)) {
                $errors[] = [
                    'order_id' => $orderId,
                    'message' => __('Cannot use In-Store Pickup for the order.')
                ];
                continue;
            }
            $success[] = $orderId;
        }

        return $this->operationResultFactory->create(
            [
                'succeeded' => $success,
                'failed' => $errors
            ]
        );
    }
}
