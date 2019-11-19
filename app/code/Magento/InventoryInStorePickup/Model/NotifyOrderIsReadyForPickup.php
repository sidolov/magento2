<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickup\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryInStorePickup\Model\Order\AddCommentToOrder;
use Magento\InventoryInStorePickup\Model\Order\Email\ReadyForPickupNotifier;
use Magento\InventoryInStorePickupApi\Api\Data\OperationResultInterface;
use Magento\InventoryInStorePickupApi\Api\Data\OperationResultInterfaceFactory;
use Magento\InventoryInStorePickupApi\Api\IsOrderReadyForPickupInterface;
use Magento\InventoryInStorePickupApi\Api\NotifyOrderIsReadyForPickupInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterface;
use Magento\Sales\Api\Data\ShipmentCreationArgumentsInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;

/**
 * Send an email to the customer and ship the order to reserve (deduct) pickup location`s QTY.
 */
class NotifyOrderIsReadyForPickup implements NotifyOrderIsReadyForPickupInterface
{
    /**
     * @var IsOrderReadyForPickupInterface
     */
    private $isOrderReadyForPickup;

    /**
     * @var ShipOrderInterface
     */
    private $shipOrder;

    /**
     * @var ReadyForPickupNotifier
     */
    private $emailNotifier;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ShipmentCreationArgumentsInterfaceFactory
     */
    private $shipmentArgumentsFactory;

    /**
     * @var ShipmentCreationArgumentsExtensionInterfaceFactory
     */
    private $argumentExtensionFactory;

    /**
     * @var Order\AddCommentToOrder
     */
    private $addCommentToOrder;

    /**
     * @var OperationResultInterfaceFactory
     */
    private $operationResultFactory;

    /**
     * @param IsOrderReadyForPickupInterface $isOrderReadyForPickup
     * @param ShipOrderInterface $shipOrder
     * @param ReadyForPickupNotifier $emailNotifier
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentCreationArgumentsInterfaceFactory $shipmentArgumentsFactory
     * @param ShipmentCreationArgumentsExtensionInterfaceFactory $argumentExtensionFactory
     * @param AddCommentToOrder $addCommentToOrder
     * @param OperationResultInterfaceFactory $operationResultFactory
     */
    public function __construct(
        IsOrderReadyForPickupInterface $isOrderReadyForPickup,
        ShipOrderInterface $shipOrder,
        ReadyForPickupNotifier $emailNotifier,
        OrderRepositoryInterface $orderRepository,
        ShipmentCreationArgumentsInterfaceFactory $shipmentArgumentsFactory,
        ShipmentCreationArgumentsExtensionInterfaceFactory $argumentExtensionFactory,
        AddCommentToOrder $addCommentToOrder,
        OperationResultInterfaceFactory $operationResultFactory
    ) {
        $this->isOrderReadyForPickup = $isOrderReadyForPickup;
        $this->shipOrder = $shipOrder;
        $this->emailNotifier = $emailNotifier;
        $this->orderRepository = $orderRepository;
        $this->shipmentArgumentsFactory = $shipmentArgumentsFactory;
        $this->argumentExtensionFactory = $argumentExtensionFactory;
        $this->addCommentToOrder = $addCommentToOrder;
        $this->operationResultFactory = $operationResultFactory;
    }

    /**
     * Send an email to the customer and ship the order to reserve (deduct) pickup location`s QTY.
     *
     * Notify customer that the order is ready for pickup by sending notification email. Ship the order to deduct the
     * item quantity from the appropriate source.
     *
     * @inheritDoc
     */
    public function execute(array $orderIds): OperationResultInterface
    {
        $operationResult = $this->isOrderReadyForPickup->execute($orderIds);

        $success = $operationResult->getSucceeded();
        $errors = $operationResult->getFailed();
        foreach ($operationResult->getSucceeded() as $orderId) {
            try {
                $order = $this->orderRepository->get($orderId);
            } catch (NoSuchEntityException $exception) {
                $errors[] = [
                    'order_id' => $orderId,
                    'message' => $exception->getMessage()
                ];
                continue;
            }

            if (!$this->emailNotifier->notify($order)) {
                $errors[] = [
                    'order_id' => $orderId,
                    'message' => __('Cannot notify customer via email.')
                ];
                continue;
            }

            try {
                $this->shipOrder->execute(
                    $orderId,
                    [],
                    false,
                    false,
                    null,
                    [],
                    [],
                    $this->getShipmentArguments($order)
                );
                $this->addCommentToOrder->execute($order);
            } catch (LocalizedException $exception) {
                $errors[] = [
                    'order_id' => $orderId,
                    'message' => $exception->getMessage()
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
     * Get shipping arguments from the Order extension attributes.
     *
     * @param OrderInterface $order
     * @return ShipmentCreationArgumentsInterface
     */
    private function getShipmentArguments(OrderInterface $order): ShipmentCreationArgumentsInterface
    {
        $arguments = $this->shipmentArgumentsFactory->create();
        /* We have already checked that PickupLocationCode exists */
        $extension = $this->argumentExtensionFactory
            ->create()
            ->setSourceCode(
                $order->getExtensionAttributes()->getPickupLocationCode()
            );
        $arguments->setExtensionAttributes($extension);

        return $arguments;
    }
}
