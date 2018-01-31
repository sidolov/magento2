<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Fixtures;

class MultiAgreementsActiveWithText implements \Magento\TestFramework\Fixture\FixtureInterface
{
    /**
     * @var \Magento\Framework\DataObject
     */
    private $fixtureData;

    /**
     * @var \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface
     */
    private $agreementRepository;

    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementFactory
     */
    private $agreementFactory;

    /**
     * MultiAgreementsActiveWithText constructor.
     */
    public function __construct()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->agreementRepository = $objectManager->get(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface::class
        );
        $this->agreementFactory = $objectManager->get(\Magento\CheckoutAgreements\Model\AgreementFactory::class);
    }

    /**
     * @inheritdoc
     */
    public function getData() : \Magento\Framework\DataObject
    {
        if ($this->fixtureData === null) {
            $this->fixtureData = new \Magento\Framework\DataObject();

            $this->fixtureData->setFirstAgreement($this->createAgreement('First Checkout Agreement (active)'));
            $this->fixtureData->setSecondAgreement($this->createAgreement('Second Checkout Agreement (active)'));
        }
        return $this->fixtureData;
    }

    /**
     * @inheritdoc
     */
    public function persist() : void
    {
        $this->agreementRepository->save($this->getData()->getFirstAgreement());
        $this->agreementRepository->save($this->getData()->getSecondAgreement());
    }

    /**
     * @param string $name
     * @return \Magento\CheckoutAgreements\Model\Agreement
     * @throws \Exception
     */
    private function createAgreement(string $name) : \Magento\CheckoutAgreements\Model\Agreement
    {
        /** @var \Magento\CheckoutAgreements\Model\Agreement $agreement */
        $agreement = $this->agreementFactory->create();
        $agreement->setData(
            [
                'name' => $name,
                'content' => 'Checkout agreement content: TEXT',
                'content_height' => '200px',
                'checkbox_text' => 'Checkout agreement checkbox text.',
                'is_active' => true,
                'is_html' => false,
                'mode' => 1,
                'stores' => [0, 1],
                ]
        );
        return $agreement;
    }

    /**
     * @{inheritdoc}
     * @throws \Exception
     */
    public function rollback() : void
    {
        try {
            $this->agreementRepository->deleteById($this->getData()->getFirstAgreement()->getId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            // Nothing to delete.
        }

        try {
            $this->agreementRepository->deleteById($this->getData()->getSecondAgreement()->getId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            // Nothing to delete.
        }
    }
}
