<?php

/**
 * Umc_Crud extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Umc
 * @package   Umc_Crud
 * @copyright 2020 Marius Strajeru
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Marius Strajeru
 */

declare(strict_types=1);

namespace Umc\Crud\Test\Unit\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\Model\AbstractModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Umc\Crud\Controller\Adminhtml\Save;
use Umc\Crud\Ui\EntityUiConfig;
use Umc\Crud\Ui\EntityUiManagerInterface;
use Umc\Crud\Ui\SaveDataProcessorInterface;

class SaveTest extends TestCase
{
    /**
     * @var Context | MockObject
     */
    private $context;
    /**
     * @var DataObjectHelper | MockObject
     */
    private $dataObjectHelper;
    /**
     * @var DataPersistorInterface | MockObject
     */
    private $dataPersistor;
    /**
     * @var SaveDataProcessorInterface | MockObject
     */
    private $dataProcessor;
    /**
     * @var EntityUiManagerInterface | MockObject
     */
    private $entityUiManager;
    /**
     * @var EntityUiConfig | MockObject
     */
    private $uiConfig;
    /**
     * @var Http | MockObject
     */
    private $request;
    /**
     * @var ResultFactory | MockObject
     */
    private $resultFactory;
    /**
     * @var Redirect | MockObject
     */
    private $redirectResult;
    /**
     * @var Manager | MockObject
     */
    private $messageManager;
    /**
     * @var AbstractModel
     */
    private $model;
    /**
     * @var Save
     */
    private $save;

    /**
     * setup tests
     */
    protected function setUp()
    {
        $this->context = $this->createMock(Context::class);
        $this->dataObjectHelper = $this->createMock(DataObjectHelper::class);
        $this->dataPersistor = $this->createMock(DataPersistorInterface::class);
        $this->dataProcessor = $this->createMock(SaveDataProcessorInterface::class);
        $this->entityUiManager = $this->createMock(EntityUiManagerInterface::class);
        $this->uiConfig = $this->createMock(EntityUiConfig::class);
        $this->request = $this->createMock(Http::class);
        $this->messageManager = $this->createMock(Manager::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->redirectResult = $this->createMock(Redirect::class);
        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);
        $this->context->method('getMessageManager')->willReturn($this->messageManager);
        $this->resultFactory->method('create')->willReturn($this->redirectResult);
        $this->model = $this->createMock(AbstractModel::class);
        $this->dataProcessor->method('modifyData')->willReturnArgument(0);
        $this->save = new Save(
            $this->context,
            $this->dataObjectHelper,
            $this->dataPersistor,
            $this->dataProcessor,
            $this->entityUiManager,
            $this->uiConfig
        );
    }

    /**
     * @covers \Umc\Crud\Controller\Adminhtml\Save::execute
     * @covers \Umc\Crud\Controller\Adminhtml\Save::__construct
     */
    public function testExecuteEditAndContinue()
    {
        $this->request->method('getPostValue')->willReturn(['id' => 1, 'dummy' => 'dummy']);
        $this->uiConfig->method('getRequestParamName')->willReturn('id');
        $this->request->method('getParam')->willReturnMap([
            ['id', null, 1],
            ['back', 'continue', 'continue']
        ]);
        $this->entityUiManager->expects($this->once())->method('get')->willReturn($this->model);
        $this->entityUiManager->expects($this->once())->method('save');
        $this->messageManager->expects($this->once())->method('addSuccessMessage');
        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $this->dataPersistor->expects($this->never())->method('set');
        $this->assertEquals($this->redirectResult, $this->save->execute());
    }

    /**
     * @covers \Umc\Crud\Controller\Adminhtml\Save::execute
     * @covers \Umc\Crud\Controller\Adminhtml\Save::__construct
     */
    public function testExecuteEditAndClose()
    {
        $this->request->method('getPostValue')->willReturn(['dummy' => 'dummy']);
        $this->uiConfig->method('getRequestParamName')->willReturn('id');
        $this->request->method('getParam')->willReturnMap([
            ['id', null, null],
            ['back', 'continue', 'close']
        ]);
        $this->uiConfig->method('getAllowSaveAndClose')->willReturn(true);
        $this->entityUiManager->expects($this->once())->method('get')->willReturn($this->model);
        $this->entityUiManager->expects($this->once())->method('save');
        $this->messageManager->expects($this->once())->method('addSuccessMessage');
        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $this->dataPersistor->expects($this->never())->method('set');
        $this->assertEquals($this->redirectResult, $this->save->execute());
    }

    /**
     * @covers \Umc\Crud\Controller\Adminhtml\Save::execute
     * @covers \Umc\Crud\Controller\Adminhtml\Save::__construct
     */
    public function testExecuteEditAndDuplicate()
    {
        $this->request->method('getPostValue')->willReturn(['dummy' => 'dummy']);
        $this->uiConfig->method('getRequestParamName')->willReturn('id');
        $this->request->method('getParam')->willReturnMap([
            ['id', null, null],
            ['back', 'continue', 'duplicate']
        ]);
        $this->uiConfig->method('getAllowSaveAndDuplicate')->willReturn(true);
        $this->entityUiManager->expects($this->exactly(2))->method('get')->willReturn($this->model);
        $this->entityUiManager->expects($this->exactly(2))->method('save');
        $this->messageManager->expects($this->exactly(2))->method('addSuccessMessage');
        $this->messageManager->expects($this->never())->method('addErrorMessage');
        $this->dataPersistor->expects($this->once())->method('set');
        $this->assertEquals($this->redirectResult, $this->save->execute());
    }

    /**
     * @covers \Umc\Crud\Controller\Adminhtml\Save::execute
     * @covers \Umc\Crud\Controller\Adminhtml\Save::__construct
     */
    public function testExecuteEditWithLocalizedException()
    {
        $this->request->method('getPostValue')->willReturn(['dummy' => 'dummy']);
        $this->uiConfig->method('getRequestParamName')->willReturn('id');
        $this->request->method('getParam')->willReturnMap([
            ['id', null, null],
        ]);
        $this->entityUiManager->expects($this->once())->method('get')->willThrowException(
            $this->createMock(LocalizedException::class)
        );
        $this->entityUiManager->expects($this->never())->method('save');
        $this->messageManager->expects($this->never())->method('addSuccessMessage');
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->dataPersistor->expects($this->once())->method('set');
        $this->assertEquals($this->redirectResult, $this->save->execute());
    }

    /**
     * @covers \Umc\Crud\Controller\Adminhtml\Save::execute
     * @covers \Umc\Crud\Controller\Adminhtml\Save::__construct
     */
    public function testExecuteEditWithException()
    {
        $this->request->method('getPostValue')->willReturn(['dummy' => 'dummy']);
        $this->uiConfig->method('getRequestParamName')->willReturn('id');
        $this->request->method('getParam')->willReturnMap([
            ['id', null, null],
        ]);
        $this->entityUiManager->expects($this->once())->method('get')->willReturn($this->model);
        $this->entityUiManager->expects($this->once())->method('save')->willThrowException(new \Exception());
        $this->messageManager->expects($this->never())->method('addSuccessMessage');
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->dataPersistor->expects($this->once())->method('set');
        $this->assertEquals($this->redirectResult, $this->save->execute());
    }
}
