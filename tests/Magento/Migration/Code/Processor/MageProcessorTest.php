<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Migration\Code\Processor;

class MageProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Migration\Code\Processor\MageProcessor
     */
    protected $obj;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Migration\Code\Processor\Mage\MageFunctionMatcher|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $matcherMock;

    /**
     * @var \Magento\Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Migration\Code\Processor\TokenHelper
     */
    protected $tokenHelper;

    public function setUp()
    {
        $this->loggerMock = $this->getMock('\Magento\Migration\Logger\Logger');

        $this->objectManagerMock = $this->getMockBuilder(
            '\Magento\Framework\ObjectManagerInterface'
        )->getMock();
        $this->matcherMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunctionMatcher'
        )->disableOriginalConstructor()
            ->getMock();

        $this->tokenHelper = $this->setupTokenHelper($this->loggerMock);

        $this->obj = new \Magento\Migration\Code\Processor\MageProcessor(
            $this->objectManagerMock,
            $this->tokenHelper,
            $this->matcherMock
        );
    }

    /**
     * @dataProvider processNoClassDataProvider
     */
    public function testProcessNoClass($inputFile, $expectedFile)
    {
        $file = __DIR__ . '/_files/mage_processor/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $this->matcherMock->expects($this->never())
            ->method('match');

        $processedTokens = $this->obj->process($tokens);

        $updatedContent = $this->tokenHelper->reconstructContent($processedTokens);

        $expectedFile = __DIR__ . '/_files/mage_processor/' . $expectedFile;
        $expected = file_get_contents($expectedFile);
        $this->assertEquals($expected, $updatedContent);
    }

    public function processNoClassDataProvider()
    {
        $data = [
            'no_class' => [
                'input' => 'no_class',
                'expected' => 'no_class_expected'
            ],
        ];
        return $data;
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($inputFile, $expectedFile)
    {
        $file = __DIR__ . '/_files/mage_processor/' . $inputFile;
        $fileContent = file_get_contents($file);

        $tokens = token_get_all($fileContent);

        $helperName = 'taxHelper';
        $helperClass = '\Magento\Tax\Helper\Data';
        $modelName = 'catalogCategory';
        $modelClass = '\Magento\Catalog\Model\Category';


        $helperMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\Helper'
        )->disableOriginalConstructor()
            ->getMock();
        $helperMock->expects($this->once())
            ->method('convertToM2');
        $helperMock->expects($this->atLeastOnce())
            ->method('getDiVariableName')
            ->willReturn($helperName);
        $helperMock->expects($this->once())
            ->method('getClass')
            ->willReturn($helperClass);

        $getModelMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\Mage\MageFunction\GetModel'
        )->disableOriginalConstructor()
            ->getMock();
        $getModelMock->expects($this->once())
            ->method('convertToM2');
        $getModelMock->expects($this->atLeastOnce())
            ->method('getDiVariableName')
            ->willReturn($modelName);
        $getModelMock->expects($this->once())
            ->method('getClass')
            ->willReturn($modelClass);

        $constructorMock = $this->getMockBuilder(
            '\Magento\Migration\Code\Processor\ConstructorHelper'
        )->disableOriginalConstructor()
            ->getMock();

        $constructorMock->expects($this->once())
            ->method('setContext')
            ->with($this->anything());
        $constructorMock->expects($this->once())
            ->method('injectArguments')
            ->with();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('\Magento\Migration\Code\Processor\ConstructorHelper', [])
            ->willReturn($constructorMock);

        $valueMap = [
            [$tokens, 29, $helperMock],
            [$tokens, 42, $getModelMock],
        ];
        $this->matcherMock->expects($this->any())
            ->method('match')
            ->willReturnMap($valueMap);

        $processedTokens = $this->obj->process($tokens);

        $updatedContent = $this->tokenHelper->reconstructContent($processedTokens);

        $expectedFile = __DIR__ . '/_files/mage_processor/' . $expectedFile;
        $expected = file_get_contents($expectedFile);
        $this->assertEquals($expected, $updatedContent);
    }

    public function processDataProvider()
    {
        $data = [
            'helper_and_getmodel' => [
                'input' => 'helper_and_getmodel',
                'expected' => 'helper_and_getmodel_expected'
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
