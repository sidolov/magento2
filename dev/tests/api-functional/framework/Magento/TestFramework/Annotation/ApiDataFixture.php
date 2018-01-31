<?php
/**
 * Implementation of the magentoApiDataFixture DocBlock annotation.
 *
 * The difference of magentoApiDataFixture from magentoDataFixture is
 * that no transactions should be used for API data fixtures.
 * Otherwise fixture data will not be accessible to Web API functional tests.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Helper\Bootstrap;

class ApiDataFixture
{
    /**
     * @var string
     */
    protected $_fixtureBaseDir;

    /**
     * Fixtures that have been applied
     *
     * @var array
     */
    private $appliedFixtures = [];

    /**
     * @var \Magento\TestFramework\Fixture\Manager
     */
    private $fixtureManager;

    /**
     * Constructor
     *
     * @param string $fixtureBaseDir
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct($fixtureBaseDir)
    {
        if (!is_dir($fixtureBaseDir)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Fixture base directory '%1' does not exist.", $fixtureBaseDir)
            );
        }
        $this->_fixtureBaseDir = realpath($fixtureBaseDir);
    }

    /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        Bootstrap::getInstance()->reinitialize();
        /** Apply method level fixtures if thy are available, apply class level fixtures otherwise */
        $this->_applyFixtures($this->_getFixtures('method', $test) ?: $this->_getFixtures('class', $test));
    }

    /**
     * Handler for 'endTest' event
     */
    public function endTest()
    {
        $this->_revertFixtures();
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Customer\Model\Metadata\AttributeMetadataCache::class)->clean();
    }

    /**
     * Retrieve fixtures from annotation
     *
     * @param string $scope 'class' or 'method'
     * @param \PHPUnit\Framework\TestCase $test
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getFixtures($scope, \PHPUnit\Framework\TestCase $test)
    {
        $annotations = $test->getAnnotations();
        $result = [];
        if (!empty($annotations[$scope]['magentoApiDataFixture'])) {
            foreach ($annotations[$scope]['magentoApiDataFixture'] as $fixture) {
                if ($this->getFixtureObject($fixture) !== null) {
                    $result[] = $fixture;
                    continue;
                }

                if (strpos($fixture, '\\') !== false) {
                    // usage of a single directory separator symbol streamlines search across the source code
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Directory separator "\\" is prohibited in fixture declaration.')
                    );
                }
                $fixtureMethod = [get_class($test), $fixture];
                if (is_callable($fixtureMethod)) {
                    $result[] = $fixtureMethod;
                } else {
                    $result[] = $this->_fixtureBaseDir . '/' . $fixture;
                }
            }
        }
        return $result;
    }

    /**
     * Execute single fixture script
     *
     * @param string|array $fixture
     */
    protected function _applyOneFixture($fixture)
    {
        try {
            if (is_callable($fixture)) {
                call_user_func($fixture);
            } else {
                require $fixture;
            }
        } catch (\Exception $e) {
            echo 'Exception occurred when running the '
            . (is_array($fixture) || is_scalar($fixture) ? json_encode($fixture) : 'callback')
            . ' fixture: ', PHP_EOL, $e;
        }
        $this->appliedFixtures[] = $fixture;
    }

    /**
     * Execute fixture scripts if any
     *
     * @param array $fixtures
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _applyFixtures(array $fixtures)
    {
        $this->fixtureManager = Bootstrap::getObjectManager()->create(\Magento\TestFramework\Fixture\Manager::class);
        Bootstrap::getObjectManager()->addSharedInstance(
            $this->fixtureManager,
            \Magento\TestFramework\Fixture\Manager::class
        );

        /* Execute fixture scripts */
        foreach ($fixtures as $oneFixture) {
            $fixtureObject = $this->getFixtureObject($oneFixture);
            if ($fixtureObject !== null) {
                $fixtureObject->persist();
                $this->fixtureManager->add(ltrim($oneFixture, '\\'), $fixtureObject);
                continue;
            }

            /* Skip already applied fixtures */
            if (!in_array($oneFixture, $this->appliedFixtures, true)) {
                $this->_applyOneFixture($oneFixture);
            }
        }
    }

    /**
     * Revert changes done by fixtures
     */
    protected function _revertFixtures()
    {
        $this->fixtureManager->rollbackAll();
        Bootstrap::getObjectManager()->removeSharedInstance(\Magento\TestFramework\Fixture\Manager::class);

        foreach ($this->appliedFixtures as $fixture) {
            if (is_callable($fixture)) {
                $fixture[1] .= 'Rollback';
                if (is_callable($fixture)) {
                    $this->_applyOneFixture($fixture);
                }
            } else {
                $fileInfo = pathinfo($fixture);
                $extension = '';
                if (isset($fileInfo['extension'])) {
                    $extension = '.' . $fileInfo['extension'];
                }
                $rollbackScript = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '_rollback' . $extension;
                if (file_exists($rollbackScript)) {
                    $this->_applyOneFixture($rollbackScript);
                }
            }
        }
        $this->appliedFixtures = [];
    }

    /**
     * @param string $fixtureName
     * @return null|\Magento\TestFramework\Fixture\FixtureInterface
     */
    private function getFixtureObject(string $fixtureName)
    {
        try {
            $fixture = Bootstrap::getObjectManager()->get($fixtureName);
        } catch (\Exception $exception) {
            return null;
        }

        if ($fixture instanceof \Magento\TestFramework\Fixture\FixtureInterface) {
            return $fixture;
        }
        return null;
    }
}
