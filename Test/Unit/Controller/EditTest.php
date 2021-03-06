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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Umc\Crud\Controller\Adminhtml\Edit;
use Umc\Crud\Ui\EntityUiConfig;
use Umc\Crud\Ui\EntityUiManagerInterface;

class EditTest extends TestCase
{
    /**
     * @var Context | MockObject
     */
    private $context;
    /**
     * @var EntityUiManagerInterface | MockObject
     */
    private $entityUiManager;
    /**
     * @var EntityUiConfig | MockObject
     */
    private $uiConfig;
    /**
     * @var RequestInterface | MockObject
     */
    private $request;
    /**
     * @var Config | MockObject
     */
    private $pageConfig;
    /**
     * @var Title | MockObject
     */
    private $pageTitle;
    /**
     * @var ResultFactory | MockObject
     */
    private $resultFactory;
    /**
     * @var Page | MockObject
     */
    private $resultPage;
    /**
     * @var AbstractModel | MockObject
     */
    private $entity;
    /**
     * @var Edit
     */
    private $edit;

    /**
     * setup tests
     */
    protected function setUp()
    {
        $this->context = $this->createMock(Context::class);
        $this->entityUiManager = $this->createMock(EntityUiManagerInterface::class);
        $this->uiConfig = $this->createMock(EntityUiConfig::class);
        $this->request = $this->createMock(RequestInterface::class);
        $this->resultPage = $this->createMock(Page::class);
        $this->pageConfig = $this->createMock(Config::class);
        $this->pageTitle = $this->createMock(Title::class);
        $this->entity = $this->createMock(AbstractModel::class);
        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->context->method('getRequest')->willReturn($this->request);
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);
        $this->pageConfig->method('getTitle')->willReturn($this->pageTitle);
        $this->resultFactory->method('create')->willReturn($this->resultPage);
        $this->resultPage->method('getConfig')->willReturn($this->pageConfig);
        $this->entityUiManager->method('get')->willReturn($this->entity);
        $this->edit = new Edit(
            $this->context,
            $this->entityUiManager,
            $this->uiConfig
        );
    }

    /**
     * @covers \Umc\Crud\Controller\Adminhtml\Edit::execute
     * @covers \Umc\Crud\Controller\Adminhtml\Edit::__construct
     */
    public function testExecuteEdit()
    {
        $this->uiConfig->expects($this->once())->method('getMenuItem')->willReturn('SelectedMenu');
        $this->uiConfig->expects($this->once())->method('getListPageTitle')->willReturn('PageTitle');
        $this->resultPage->expects($this->once())->method('setActiveMenu')->with('SelectedMenu');
        $this->pageTitle->expects($this->exactly(2))->method('prepend')->withConsecutive(
            $this->equalTo('PageTitle'),
            $this->equalTo('name')
        );
        $this->entity->method('getId')->willReturn(1);
        $this->entity->method('getData')->willReturn('name');
        $this->uiConfig->expects($this->never())->method('getNewLabel');
        $this->assertEquals($this->resultPage, $this->edit->execute());
    }

    /**
     * @covers \Umc\Crud\Controller\Adminhtml\Edit::execute
     * @covers \Umc\Crud\Controller\Adminhtml\Edit::__construct
     */
    public function testExecuteMew()
    {
        $this->uiConfig->expects($this->once())->method('getMenuItem')->willReturn('SelectedMenu');
        $this->uiConfig->expects($this->once())->method('getListPageTitle')->willReturn('PageTitle');
        $this->resultPage->expects($this->once())->method('setActiveMenu')->with('SelectedMenu');
        $this->uiConfig->expects($this->once())->method('getNewLabel')->willReturn('new');
        $this->pageTitle->expects($this->exactly(2))->method('prepend')->withConsecutive(
            $this->equalTo('PageTitle'),
            $this->equalTo('new')
        );
        $this->entity->method('getId')->willReturn(null);
        $this->entity->expects($this->never())->method('getData');

        $this->assertEquals($this->resultPage, $this->edit->execute());
    }
}
