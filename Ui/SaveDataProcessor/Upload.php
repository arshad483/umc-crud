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

namespace Umc\Crud\Ui\SaveDataProcessor;

use Umc\Crud\Model\FileInfo;
use Umc\Crud\Model\Uploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;
use Umc\Crud\Ui\SaveDataProcessorInterface;

class Upload implements SaveDataProcessorInterface
{
    /**
     * @var array
     */
    private $fields;
    /**
     * @var Uploader
     */
    private $uploader;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var FileInfo
     */
    private $fileInfo;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var bool
     */
    private $strict;

    /**
     * Upload constructor.
     * @param array $fields
     * @param Uploader $uploader
     * @param FileInfo $fileInfo
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     * @param bool $strict
     */
    public function __construct(
        array $fields,
        Uploader $uploader,
        FileInfo $fileInfo,
        Filesystem $filesystem,
        LoggerInterface $logger,
        bool $strict
    ) {
        $this->fields = $fields;
        $this->uploader = $uploader;
        $this->fileInfo = $fileInfo;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        $this->strict = $strict;
    }

    /**
     * @param $value
     * @return bool
     */
    private function isTmpFileAvailable($value)
    {
        return is_array($value) && isset($value[0]['tmp_name']);
    }

    /**
     * @param $value
     * @return string
     */
    private function getUploadedImageName($value)
    {
        return (is_array($value) && isset($value[0]['file'])) ? $value[0]['file'] : '';
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        foreach ($this->fields as $field) {
            if (!array_key_exists($field, $data)) {
                if ($this->strict) {
                    $data[$field] = '';
                }
                continue;
            }
            $value = $data[$field] ?? '';
            if ($this->isTmpFileAvailable($value) && $imageName = $this->getUploadedImageName($value)) {
                try {
                    $data[$field] = $this->uploader->moveFileFromTmp($imageName);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            } else {
                if ($this->fileResidesOutsideUploadDir($value)) {
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $value[0]['name'] = parse_url($value[0]['url'], PHP_URL_PATH);
                }
                $data[$field] = $value[0]['name'] ?? '';
            }
        }
        return $data;
    }

    /**
     * @param $value
     * @return bool
     */
    private function fileResidesOutsideUploadDir($value)
    {
        if (!is_array($value) || !isset($value[0]['url'])) {
            return false;
        }
        $fileUrl = ltrim($value[0]['url'], '/');
        $filePath = $this->fileInfo->getFilePath($fileUrl);
        $baseMediaDir = $this->filesystem->getUri(DirectoryList::MEDIA);
        return $baseMediaDir && strpos($filePath, $baseMediaDir) !== false;
    }
}
