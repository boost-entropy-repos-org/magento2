<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tools\Di\Code\Generator;

use Magento\Framework\App\Area;
use Magento\Framework\ObjectManager\Config;
use Magento\Tools\Di\Code\Scanner;
use Magento\Framework\Interception\Config\Config as InterceptionConfig;
use Magento\Tools\Di\Code\Reader\Type;

class InterceptionConfigurationBuilder
{
    /**
     * Area code list: global, frontend, etc.
     *
     * @var array
     */
    private $areaCodesList = [];

    /**
     * @var InterceptionConfig
     */
    private $interceptionConfig;

    /**
     * @var PluginList
     */
    private $pluginList;

    /**
     * @var Type
     */
    private $typeReader;

    /**
     * @param InterceptionConfig $interceptionConfig
     * @param PluginList $pluginList
     * @param Type $typeReader
     */
    public function __construct(InterceptionConfig $interceptionConfig, PluginList $pluginList, Type $typeReader)
    {
        $this->interceptionConfig = $interceptionConfig;
        $this->pluginList = $pluginList;
        $this->typeReader = $typeReader;
    }

    /**
     * Adds area code
     *
     * @param string $areaCode
     * @return void
     */
    public function addAreaCode($areaCode)
    {
        if (empty($this->areaCodesList[$areaCode])) {
            $this->areaCodesList[] = $areaCode;
        }
    }

    /**
     * Builds interception configuration for all defined classes
     *
     * @param array $definedClasses
     * @return array
     */
    public function getInterceptionConfiguration($definedClasses)
    {
        $interceptedInstances = $this->getInterceptedClasses($definedClasses);
        $inheritedConfig = $this->getPluginsList($interceptedInstances);
        $mergedAreaPlugins = $this->mergeAreaPlugins($inheritedConfig);
        $interceptedMethods = $this->getInterceptedMethods($mergedAreaPlugins);

        return $interceptedMethods;
    }

    /**
     * Get intercepted instances from defined class list
     *
     * @param array $definedClasses
     * @return array
     */
    private function getInterceptedClasses($definedClasses)
    {
        $intercepted = [];
        foreach ($definedClasses as $definedClass) {
            if ($this->interceptionConfig->hasPlugins($definedClass) && $this->typeReader->isConcrete($definedClass)) {
                $intercepted[] = $definedClass;
            }
        }
        return $intercepted;
    }

    /**
     * Returns plugin list:
     * ['concrete class name' => ['plugin name' => [instance => 'instance name', 'order' => 'Order Number']]]
     *
     * @param array $interceptedInstances
     * @return array
     */
    private function getPluginsList($interceptedInstances)
    {
        $this->pluginList->setInterceptedClasses($interceptedInstances);

        $inheritedConfig = [];
        foreach ($this->areaCodesList as $areaKey) {
            $scopePriority = [Area::AREA_GLOBAL];
            $pluginListCloned = clone $this->pluginList;
            if ($areaKey != Area::AREA_GLOBAL) {
                $scopePriority[] = $areaKey;
                $pluginListCloned->setScopePriorityScheme($scopePriority);
            }
            $key = implode('', $scopePriority);
            $inheritedConfig[$key] = $this->filterNullInheritance($pluginListCloned->getPluginsConfig());
        }
        return $inheritedConfig;
    }

    /**
     * Filters plugin inheritance list for instances without plugins, and abstract/interface
     *
     * @param array $pluginInheritance
     * @return array
     */
    private function filterNullInheritance($pluginInheritance)
    {
        $filteredData = [];
        foreach ($pluginInheritance as $instance => $plugins) {
            if (is_null($plugins) || !$this->typeReader->isConcrete($instance)) {
                continue;
            }

            $pluginInstances = [];
            foreach ($plugins as $plugin) {
                if (in_array($plugin['instance'], $pluginInstances)) {
                    continue;
                }
                $pluginInstances[] = $plugin['instance'];
            }
            $filteredData[$instance] = $pluginInstances;

        }

        return $filteredData;
    }

    /**
     * Merge plugins in areas
     *
     * @param array $inheritedConfig
     * @return array
     */
    private function mergeAreaPlugins($inheritedConfig)
    {
        $mergedConfig = [];
        foreach ($inheritedConfig as $configuration) {
            $mergedConfig = array_merge_recursive($mergedConfig, $configuration);
        }
        foreach ($mergedConfig as &$plugins) {
            $plugins = array_unique($plugins);
        }

        return $mergedConfig;
    }

    /**
     * Returns interception configuration with plugin methods
     *
     * @param array $interceptionConfiguration
     * @return array
     */
    private function getInterceptedMethods($interceptionConfiguration)
    {
        $pluginDefinitionList = new \Magento\Framework\Interception\Definition\Runtime();
        foreach ($interceptionConfiguration as &$plugins) {
            $pluginsMethods = [];
            foreach ($plugins as $plugin) {
                $pluginsMethods = array_unique(
                    array_merge($pluginsMethods, array_keys($pluginDefinitionList->getMethodList($plugin)))
                );
            }
            $plugins = $pluginsMethods;
        }
        return $interceptionConfiguration;
    }
}
