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

namespace Umc\Crud\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Umc\Crud\Ui\SaveDataProcessorInterface;
use Umc\Crud\Ui\EntityUiManagerInterface;

class InlineEdit extends Action implements HttpPostActionInterface
{
    /**
     * @var SaveDataProcessorInterface
     */
    private $dataProcessor;
    /**
     * @var EntityUiManagerInterface
     */
    private $entityUiManager;

    /**
     * InlineEdit constructor.
     * @param SaveDataProcessorInterface $dataProcessor
     * @param EntityUiManagerInterface $entityUiManager
     */
    public function __construct(
        Context $context,
        SaveDataProcessorInterface $dataProcessor,
        EntityUiManagerInterface $entityUiManager
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->entityUiManager = $entityUiManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json
     * |\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $id) {
                    try {
                        $entity = $this->entityUiManager->get($id);
                        $newData = $this->dataProcessor->modifyData($postItems[$id]);
                        // phpcs:disable Magento2.Performance.ForeachArrayMerge
                        $entity->setData(array_merge($entity->getData(), $newData));
                        // phpcs:enable
                        $this->entityUiManager->save($entity);
                    } catch (\Exception $e) {
                        $messages[] = '[' . __('Error') .  ': ' . $id . '] ' . $e->getMessage();
                        $error = true;
                    }
                }
            }
        }
        $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
        return $resultJson;
    }
}
