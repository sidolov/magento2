<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickup\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryInStorePickup\Model\Order\IsFulfillable;
use Magento\InventoryInStorePickupApi\Api\Data\OperationResultInterface;
use Magento\InventoryInStorePickupApi\Api\Data\OperationResultInterfaceFactory;
use Magento\InventoryInStorePickupApi\Api\IsOrderReadyForPickupInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Check if order can be shipped and the pickup location has enough QTY
 */
class IsOrderReadyForPickup implements IsOrderReadyForPickupInterface
{
    /**
     * @var IsFulfillable
     */
    private $isFulfillable;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OperationResultInterfaceFactory
     */
    private $operationResultFactory;

    /**
     * @param IsFulfillable $isFulfillable
     * @param OrderRepositoryInterface $orderRepository
     * @param OperationResultInterfaceFactory $operationResultFactory
     */
    public function __construct(
        IsFulfillable $isFulfillable,
        OrderRepositoryInterface $orderRepository,
        OperationResultInterfaceFactory $operationResultFactory
    ) {
        $this->isFulfillable = $isFulfillable;
        $this->orderRepository = $orderRepository;
        $this->operationResultFactory = $operationResultFactory;
    }

    /**
     * Check if order can be shipped and the pickup location has enough QTY.
     *
     * @param int[] $orderIds
     * @return OperationResultInterface
     */
    public function execute(array $orderIds): OperationResultInterface
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
            if (!$this->canShip($order)) {
                $errors[] = [
                    'order_id' => $orderId,
                    'message' => __('Cannot ship the order.')
                ];
                continue;
            }

            if (!$this->isFulfillable->execute($order)) {
                $errors[] = [
                    'order_id' => $orderId,
                    'message' => __('Cannot fulfill the order.')
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

    /**
     * Retrieve order shipment availability.
     *
     * @param OrderInterface $order
     * @return bool
     */
    private function canShip(OrderInterface $order): bool
    {
        if ($order instanceof Order) {
            return $order->canShip();
        }

        return true;
    }
}
