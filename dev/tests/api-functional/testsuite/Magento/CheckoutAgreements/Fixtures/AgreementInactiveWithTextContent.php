<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Fixtures;

class AgreementInactiveWithTextContent implements \Magento\TestFramework\Fixture\FixtureInterface
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

            /** @var \Magento\CheckoutAgreements\Model\Agreement $agreement */
            $agreement = $this->agreementFactory->create();
            $agreement->setData(
                [
                    'name' => 'Checkout Agreement (inactive)',
                    'content' => 'Checkout agreement content: TEXT',
                    'content_height' => '200px',
                    'checkbox_text' => 'Checkout agreement checkbox text.',
                    'is_active' => false,
                    'is_html' => false,
                    'stores' => [0, 1],
                ]
            );
            $this->fixtureData->setAgreement($agreement);
        }
        return $this->fixtureData;
    }

    /**
     * @inheritdoc
     */
    public function persist() : void
    {
        $this->agreementRepository->save($this->getData()->getAgreement());
    }

    /**
     * @inheritdoc
     */
    public function rollback() : void
    {
        try {
            $this->agreementRepository->deleteById($this->getData()->getAgreement()->getId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            // Nothing to delete.
        }
    }
}