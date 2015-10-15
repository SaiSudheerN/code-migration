<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor\Mage\MageFunction;

use Magento\Migration\Code\Processor;
class ConstructorHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\ConstructorHelper
     */
    protected $obj;

    /**
     * @var \Magento\Migration\Mapping\ClassMapping|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMapperMock;

    /**
     * @var \Magento\Migration\Mapping\Alias|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aliasMapperMock;

    /**
     * @var \Magento\Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentFactoryMock;

    public function setUp()
    {
        $this->loggerMock = $this->getMock('\Magento\Migration\Logger\Logger');

        $this->classMapperMock = $this->getMockBuilder(
            '\Magento\Migration\Mapping\ClassMapping'
        )->disableOriginalConstructor()
            ->getMock();
        $this->aliasMapperMock = $this->getMockBuilder(
            '\Magento\Migration\Mapping\Alias'
        )->disableOriginalConstructor()
            ->getMock();

        $this->tokenHelper = $this->setupTokenHelper($this->loggerMock);

        $this->argumentFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory'
        )->setMethods(['create'])
            ->getMock();

        $this->obj = new \Magento\Migration\Code\Processor\ConstructorHelper(
            $this->loggerMock,
            $this->tokenHelper,
            $this->argumentFactoryMock
        );
    }

    /**
     * @dataProvider getConstructorIndexDataProvider
     */
    public function testGetConstructorIndex($inputFile, $expected)
    {
        $file = __DIR__ . '/_files/constructor_helper/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $this->obj->setContext($tokens);

        $index = $this->obj->getConstructorIndex();
        $this->assertEquals($expected, $index);
    }

    public function getConstructorIndexDataProvider()
    {
        $data = [
            'no_constructor' => [
                'input' => 'no_constructor',
                'index' => -23,
            ],
            'with_constructor' => [
                'input' => 'with_constructor',
                'index' => 23,
            ],
        ];

        return $data;
    }

    /**
     * @dataProvider getParentClassDataProvider
     */
    public function testGetParentClass($inputFile, $expectedParentClass)
    {
        $file = __DIR__ . '/_files/constructor_helper/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $this->obj->setContext($tokens);

        $parentClass = $this->obj->getParentClass();
        $this->assertEquals($expectedParentClass, $parentClass);
    }

    public function getParentClassDataProvider()
    {
        $data = [
            'no_parent_class' => [
                'input' => 'no_parent_class',
                'parent_class' => null,
            ],
            'with_parent_class' => [
                'input' => 'with_parent_class',
                'parent_class' => 'ParentClass',
            ],
        ];

        return $data;
    }

    /**
     * @dataProvider injectArgumentDataProvider
     */
    public function testInjectArgument(
        $inputFile,
        $arguments,
        $expectedFile
    ) {
        $file = __DIR__ . '/_files/constructor_helper/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $this->obj->setContext($tokens);

        $this->obj->injectArguments($arguments);

        $updatedContent = $this->tokenHelper->reconstructContent($tokens);

        $expectedFile = __DIR__ . '/_files/constructor_helper/' . $expectedFile;
        $expected = file_get_contents($expectedFile);
        $this->assertEquals($expected, $updatedContent);
    }

    public function injectArgumentDataProvider()
    {
        $data = [
            'no_existing_constructor' => [
                'input_file' => 'no_existing_constructor',
                'arguments' => [
                    [
                        'type' => '\Magento\Framework\StoreManagerInterface',
                        'variable_name' => 'storeManager',
                    ],
                    [
                        'type' => '\Magento\Tax\Helper\Data',
                        'variable_name' => 'taxHelper',
                    ],
                ],
                'expected_file' => 'no_existing_constructor_expected'
            ],
            'existing_constructor_no_argument' => [
                'input_file' => 'existing_constructor_no_argument',
                'arguments' => [
                    [
                        'type' => '\Magento\Framework\StoreManagerInterface',
                        'variable_name' => 'storeManager',
                    ],
                    [
                        'type' => '\Magento\Tax\Helper\Data',
                        'variable_name' => 'taxHelper',
                    ],
                ],
                'expected_file' => 'existing_constructor_no_argument_expected'
            ],
            'existing_constructor_no_optional_argument' => [
                'input_file' => 'existing_constructor_no_optional_argument',
                'arguments' => [
                    [
                        'type' => '\Magento\Framework\StoreManagerInterface',
                        'variable_name' => 'storeManager',
                    ],
                    [
                        'type' => '\Magento\Tax\Helper\Data',
                        'variable_name' => 'taxHelper',
                    ],
                ],
                'expected_file' => 'existing_constructor_no_optional_argument_expected'
            ],
            'existing_constructor_no_optional_argument_oneline' => [
                'input_file' => 'existing_constructor_no_optional_argument_oneline',
                'arguments' => [
                    [
                        'type' => '\Magento\Framework\StoreManagerInterface',
                        'variable_name' => 'storeManager',
                    ],
                    [
                        'type' => '\Magento\Tax\Helper\Data',
                        'variable_name' => 'taxHelper',
                    ],
                ],
                'expected_file' => 'existing_constructor_no_optional_argument_oneline_expected'
            ],
            'existing_constructor_all_optional_argument' => [
                'input_file' => 'existing_constructor_all_optional_argument',
                'arguments' => [
                    [
                        'type' => '\Magento\Framework\StoreManagerInterface',
                        'variable_name' => 'storeManager',
                    ],
                    [
                        'type' => '\Magento\Tax\Helper\Data',
                        'variable_name' => 'taxHelper',
                    ],
                ],
                'expected_file' => 'existing_constructor_all_optional_argument_expected'
            ],
            'existing_constructor_all_optional_argument_oneline' => [
                'input_file' => 'existing_constructor_all_optional_argument_oneline',
                'arguments' => [
                    [
                        'type' => '\Magento\Framework\StoreManagerInterface',
                        'variable_name' => 'storeManager',
                    ],
                    [
                        'type' => '\Magento\Tax\Helper\Data',
                        'variable_name' => 'taxHelper',
                    ],
                ],
                'expected_file' => 'existing_constructor_all_optional_argument_oneline_expected'
            ],
            'existing_constructor_mixed_arguments' => [
                'input_file' => 'existing_constructor_mixed_arguments',
                'arguments' => [
                    [
                        'type' => '\Magento\Framework\StoreManagerInterface',
                        'variable_name' => 'storeManager',
                    ],
                    [
                        'type' => '\Magento\Tax\Helper\Data',
                        'variable_name' => 'taxHelper',
                    ],
                ],
                'expected_file' => 'existing_constructor_mixed_arguments_expected'
            ],
            'existing_constructor_mixed_arguments_oneline' => [
                'input_file' => 'existing_constructor_mixed_arguments_oneline',
                'arguments' => [
                    [
                        'type' => '\Magento\Framework\StoreManagerInterface',
                        'variable_name' => 'storeManager',
                    ],
                    [
                        'type' => '\Magento\Tax\Helper\Data',
                        'variable_name' => 'taxHelper',
                    ],
                ],
                'expected_file' => 'existing_constructor_mixed_arguments_oneline_expected'
            ],
        ];

        return $data;
    }

    /**
     * @param \Magento\Migration\Logger\Logger $loggerMock
     * @return \Magento\Migration\Code\Processor\TokenHelper
     */
    public function setupTokenHelper(\Magento\Migration\Logger\Logger $loggerMock)
    {
        $argumentFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\ArgumentFactory'
        )->setMethods(['create'])
            ->getMock();
        $argumentFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\Mage\MageFunction\Argument();
                }
            );
        $tokenFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\TokenArgumentFactory'
        )->setMethods(['create'])
            ->getMock();
        $tokenFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\TokenArgument();
                }
            );


        $tokenCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\TokenArgumentCollectionFactory'
        )->setMethods(['create'])
            ->getMock();
        $tokenCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\TokenArgumentCollection();
                }
            );

        $callCollectionFactoryMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\CallArgumentCollectionFactory'
        )->setMethods(['create'])
            ->getMock();
        $callCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function () {
                    return new \Magento\Migration\Code\Processor\CallArgumentCollection();
                }
            );

        $tokenHelper = new \Magento\Migration\Code\Processor\TokenHelper(
            $loggerMock,
            $argumentFactoryMock,
            $tokenFactoryMock,
            $tokenCollectionFactoryMock,
            $callCollectionFactoryMock
        );

        return $tokenHelper;
    }
}
