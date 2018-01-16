<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Fixtures;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\TestFramework\Fixture\FixtureInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

class SwatchAttribute implements FixtureInterface
{
    /**
     * @var \Magento\Framework\DataObject
     */
    private $fixtureData;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * SwatchAttribute constructor.
     */
    public function __construct()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * {@inheritdoc}
     */
    public function getData() : \Magento\Framework\DataObject
    {
        if ($this->fixtureData === null) {
            $this->fixtureData = new \Magento\Framework\DataObject();
            $this->fixtureData->setAttribute($this->prepareAttribute());
        }
        return $this->fixtureData;
    }

    /**
     * {@inheritdoc}
     */
    public function persist() : void
    {
        $this->getData()->getAttribute()->save();
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    private function prepareAttribute() : \Magento\Catalog\Api\Data\ProductAttributeInterface
    {
        $optionsPerAttribute = 3;
        $data = [
            'is_required' => 1,
            'is_visible_on_front' => 1,
            'is_visible_in_advanced_search' => 0,
            'attribute_code' => 'color_swatch_' . microtime(),
            'backend_type' => '',
            'is_searchable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'frontend_label' => 'Attribute ',
            'entity_type_id' => 4,
            'frontend_input' => 'swatch_visual',
            'swatch_input_type' => 'visual'
        ];

        $data['swatchvisual']['value'] = array_reduce(
            range(1, $optionsPerAttribute),
            function ($values, $index) use ($optionsPerAttribute) {
                $values['option_' . $index] = '#'
                    . str_repeat(
                        dechex(255 * $index / $optionsPerAttribute),
                        3
                    );
                return $values;
            },
            []
        );
        $data['optionvisual']['value'] = array_reduce(
            range(1, $optionsPerAttribute),
            function ($values, $index) use ($optionsPerAttribute) {
                $values['option_' . $index] = ['option ' . $index];
                return $values;
            },
            []
        );

        $data['options']['option'] = array_reduce(
            range(1, $optionsPerAttribute),
            function ($values, $index) use ($optionsPerAttribute) {
                $values[] = [
                    'label' => 'option ' . $index,
                    'value' => 'option_' . $index,
                ];
                return $values;
            },
            []
        );

        $options = [];
        foreach ($data['options']['option'] as $optionData) {
            $options[] = $this->objectManager->get(AttributeOptionInterface::class)
                ->setLabel($optionData['label'])
                ->setValue($optionData['value']);
        }

        $attribute = $this->objectManager->create(ProductAttributeInterface::class, ['data' => $data]);
        $attribute->setOptions($options);
        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function rollback() : void
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);

        $attribute->loadByCode(4, $this->getData()->getAttribute()->getAttributeCode());

        if ($attribute->getId()) {
            $attribute->delete();
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}
