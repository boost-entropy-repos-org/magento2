<?php
/**
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\ObjectManager;


require __DIR__ . '/../../_files/ChildInterface.php';
require __DIR__ . '/../../_files/DiParent.php';
require __DIR__ . '/../../_files/Child.php';
require __DIR__ . '/../../_files/Child/A.php';
require __DIR__ . '/../../_files/Child/Circular.php';
require __DIR__ . '/../../_files/Aggregate/AggregateInterface.php';
require __DIR__ . '/../../_files/Aggregate/AggregateParent.php';
require __DIR__ . '/../../_files/Aggregate/Child.php';
require __DIR__ . '/../../_files/Aggregate/WithOptional.php';
class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $_object;

    protected function setUp()
    {
        $config = new \Magento\Framework\ObjectManager\Config\Config(
            new \Magento\Framework\ObjectManager\Relations\Runtime()
        );
        $factory = new Factory\Dynamic\Developer($config, null, null, array(
            'first_param' => 'first_param_value',
            'second_param' => 'second_param_value'
        ));
        $this->_object = new \Magento\Framework\ObjectManager\ObjectManager($factory, $config);
        $factory->setObjectManager($this->_object);
    }

    public function testCreateCreatesNewInstanceEveryTime()
    {
        $objectA = $this->_object->create('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $objectA);
        $objectB = $this->_object->create('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $objectB);
        $this->assertNotSame($objectA, $objectB);
    }

    public function testGetCreatesNewInstanceOnlyOnce()
    {
        $objectA = $this->_object->get('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $objectA);
        $objectB = $this->_object->get('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $objectB);
        $this->assertSame($objectA, $objectB);
    }

    public function testCreateCreatesPreferredImplementation()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                )
            )
        );
        $interface = $this->_object->create('Magento\Test\Di\DiInterface');
        $parent = $this->_object->create('Magento\Test\Di\DiParent');
        $child = $this->_object->create('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $interface);
        $this->assertInstanceOf('Magento\Test\Di\Child', $parent);
        $this->assertInstanceOf('Magento\Test\Di\Child', $child);
        $this->assertNotSame($interface, $parent);
        $this->assertNotSame($interface, $child);
    }

    public function testGetCreatesPreferredImplementation()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                )
            )
        );
        $interface = $this->_object->get('Magento\Test\Di\DiInterface');
        $parent = $this->_object->get('Magento\Test\Di\DiParent');
        $child = $this->_object->get('Magento\Test\Di\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $interface);
        $this->assertInstanceOf('Magento\Test\Di\Child', $parent);
        $this->assertInstanceOf('Magento\Test\Di\Child', $child);
        $this->assertSame($interface, $parent);
        $this->assertSame($interface, $child);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Missing required argument $scalar of Magento\Test\Di\Aggregate\AggregateParent
     */
    public function testCreateThrowsExceptionIfRequiredConstructorParameterIsNotProvided()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                )
            )
        );
        $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
    }

    public function testCreateResolvesScalarParametersAutomatically()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                ),
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array(
                        'child' => array('instance' => 'Magento\Test\Di\Child\A'),
                        'scalar' => 'scalarValue'
                    )
                )
            )
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
        $this->assertInstanceOf('Magento\Test\Di\Aggregate\AggregateParent', $result);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->interface);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->parent);
        $this->assertInstanceOf('Magento\Test\Di\Child\A', $result->child);
        $this->assertEquals('scalarValue', $result->scalar);
        $this->assertEquals('1', $result->optionalScalar);
    }

    public function testGetCreatesSharedInstancesEveryTime()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                ),
                'Magento\Test\Di\DiInterface' => array('shared' => 0),
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array('scalar' => 'scalarValue')
                )
            )
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
        $this->assertInstanceOf('Magento\Test\Di\Aggregate\AggregateParent', $result);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->interface);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->parent);
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->child);
        $this->assertNotSame($result->interface, $result->parent);
        $this->assertNotSame($result->interface, $result->child);
        $this->assertSame($result->parent, $result->child);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Circular dependency: Magento\Test\Di\Aggregate\AggregateParent depends on
     * Magento\Test\Di\Child\Circular and vice versa.
     */
    public function testGetDetectsCircularDependency()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child\Circular'
                )
            )
        );
        $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
    }

    public function testCreateIgnoresOptionalArguments()
    {
        $instance = $this->_object->create('Magento\Test\Di\Aggregate\WithOptional');
        $this->assertNull($instance->parent);
        $this->assertNull($instance->child);
    }

    public function testCreateCreatesPreconfiguredInstance()
    {
        $this->_object->configure(
            array(
                'preferences' => array(
                    'Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent',
                    'Magento\Test\Di\DiParent' => 'Magento\Test\Di\Child'
                ),
                'customChildType' => array(
                    'type' => 'Magento\Test\Di\Aggregate\Child',
                    'arguments' => array(
                        'scalar' => 'configuredScalar',
                        'secondScalar' => 'configuredSecondScalar',
                        'secondOptionalScalar' => 'configuredOptionalScalar'
                    )
                )
            )
        );
        $customChild = $this->_object->get('customChildType');
        $this->assertInstanceOf('Magento\Test\Di\Aggregate\Child', $customChild);
        $this->assertEquals('configuredScalar', $customChild->scalar);
        $this->assertEquals('configuredSecondScalar', $customChild->secondScalar);
        $this->assertEquals(1, $customChild->optionalScalar);
        $this->assertEquals('configuredOptionalScalar', $customChild->secondOptionalScalar);
        $this->assertSame($customChild, $this->_object->get('customChildType'));
    }

    public function testParameterShareabilityConfigurationIsApplied()
    {
        $this->_object->configure(
            array(
                'customChildType' => array(
                    'type' => 'Magento\Test\Di\Aggregate\Child',
                    'arguments' => array(
                        'interface' => array('instance' => 'Magento\Test\Di\DiParent'),
                        'scalar' => 'configuredScalar',
                        'secondScalar' => 'configuredSecondScalar'
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);

        $this->_object->configure(
            array(
                'customChildType' => array(
                    'arguments' => array(
                        'interface' => array(
                            'instance' => 'Magento\Test\Di\DiParent',
                            'shared' => false
                        )
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertNotSame($childA->interface, $childB->interface);
    }

    public function testTypeShareabilityConfigurationIsApplied()
    {
        $this->_object->configure(
            array(
                'customChildType' => array(
                    'type' => 'Magento\Test\Di\Aggregate\Child',
                    'arguments' => array(
                        'interface' => array('instance' => 'Magento\Test\Di\DiParent'),
                        'scalar' => 'configuredScalar',
                        'secondScalar' => 'configuredSecondScalar'
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);

        $this->_object->configure(array('Magento\Test\Di\DiParent' => array('shared' => false)));

        $parent1 = $this->_object->create('Magento\Test\Di\DiParent');
        $parent2 = $this->_object->create('Magento\Test\Di\DiParent');
        $this->assertNotSame($parent1, $parent2);

        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
    }

    public function testParameterShareabilityConfigurationOverridesTypeShareability()
    {
        $this->_object->configure(
            array(
                'Magento\Test\Di\DiParent' => array('shared' => false),
                'customChildType' => array(
                    'type' => 'Magento\Test\Di\Aggregate\Child',
                    'arguments' => array(
                        'interface' => array('instance' => 'Magento\Test\Di\DiParent'),
                        'scalar' => 'configuredScalar',
                        'secondScalar' => 'configuredSecondScalar'
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertNotSame($childA->interface, $childB->interface);

        $this->_object->configure(
            array(
                'customChildType' => array(
                    'arguments' => array(
                        'interface' => array(
                            'instance' => 'Magento\Test\Di\DiParent',
                            'shared' => true
                        )
                    )
                )
            )
        );
        $childA = $this->_object->create('customChildType');
        $childB = $this->_object->create('customChildType');
        $this->assertNotSame($childA, $childB);
        $this->assertSame($childA->interface, $childB->interface);
    }

    public function testGlobalArgumentsCanBeConfigured()
    {
        $this->_object->configure(
            array(
                'preferences' => array('Magento\Test\Di\DiInterface' => 'Magento\Test\Di\DiParent'),
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array(
                        'scalar' => array('argument' => 'first_param'),
                        'optionalScalar' => array('argument' => 'second_param')
                    )
                )
            )
        );
        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\AggregateParent');
        $this->assertEquals('first_param_value', $result->scalar);
        $this->assertEquals('second_param_value', $result->optionalScalar);
    }

    public function testConfiguredArgumentsAreInherited()
    {
        $this->_object->configure(
            array(
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array(
                        'interface' => array('instance' => 'Magento\Test\Di\DiParent'),
                        'scalar' => array('argument' => 'first_param'),
                        'optionalScalar' => 'parentOptionalScalar'
                    )
                ),
                'Magento\Test\Di\Aggregate\Child' => array(
                    'arguments' => array(
                        'secondScalar' => 'childSecondScalar'
                    )
                )
            )
        );

        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\Child');
        $this->assertInstanceOf('Magento\Test\Di\DiParent', $result->interface);
        $this->assertEquals('first_param_value', $result->scalar);
        $this->assertEquals('childSecondScalar', $result->secondScalar);
        $this->assertEquals('parentOptionalScalar', $result->optionalScalar);
    }

    public function testConfiguredArgumentsOverrideInheritedArguments()
    {
        $this->_object->configure(
            array(
                'Magento\Test\Di\Aggregate\AggregateParent' => array(
                    'arguments' => array(
                        'interface' => array('instance' => 'Magento\Test\Di\DiParent'),
                        'scalar' => array('argument' => 'first_param'),
                        'optionalScalar' => 'parentOptionalScalar'
                    )
                ),
                'Magento\Test\Di\Aggregate\Child' => array(
                    'arguments' => array(
                        'interface' => array('instance' => 'Magento\Test\Di\Child'),
                        'scalar' => array('argument' => 'second_param'),
                        'secondScalar' => 'childSecondScalar',
                        'optionalScalar' => 'childOptionalScalar'
                    )
                )
            )
        );

        /** @var $result \Magento\Test\Di\Aggregate\AggregateParent */
        $result = $this->_object->create('Magento\Test\Di\Aggregate\Child');
        $this->assertInstanceOf('Magento\Test\Di\Child', $result->interface);
        $this->assertEquals('second_param_value', $result->scalar);
        $this->assertEquals('childSecondScalar', $result->secondScalar);
        $this->assertEquals('childOptionalScalar', $result->optionalScalar);
    }

    public function testGetIgnoresFirstSlash()
    {
        $this->assertSame($this->_object->get('Magento\Test\Di\Child'), $this->_object->get('\Magento\Test\Di\Child'));
    }
}
