<?php

namespace idoit\Component\Upload;

/**
 * Class UploadType
 *
 * @package idoit\Component\Upload
 */
class UploadType
{
    /**
     * @var int
     */
    private $sizeLimit = 5242880;

    /**
     * @var string
     */
    private $uploadDirectory = '/upload/file';

    /**
     * @var array
     */
    private $validExtensions = [];

    /**
     * @var callable
     */
    private $callbackAfterUpload = null;

    /**
     * @return int
     */
    public function getSizeLimit(): int
    {
        return $this->sizeLimit;
    }

    /**
     * @param int $sizeLimit
     *
     * @return UploadType
     */
    public function setSizeLimit(int $sizeLimit): UploadType
    {
        $this->sizeLimit = $sizeLimit;

        return $this;
    }

    /**
     * @return string
     */
    public function getUploadDirectory(): string
    {
        return $this->uploadDirectory;
    }

    /**
     * @param string $uploadDirectory
     *
     * @return UploadType
     */
    public function setUploadDirectory(string $uploadDirectory): UploadType
    {
        $this->uploadDirectory = $uploadDirectory;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidExtensions(): array
    {
        return $this->validExtensions;
    }

    /**
     * @param array $validExtensions
     *
     * @return UploadType
     */
    public function setValidExtensions(array $validExtensions): UploadType
    {
        $this->validExtensions = $validExtensions;

        return $this;
    }

    /**
     * @return ?callable
     */
    public function getCallbackAfterUpload(): ?callable
    {
        return $this->callbackAfterUpload;
    }

    /**
     * @param callable $callbackAfterUpload
     *
     * @return UploadType
     */
    public function setCallbackAfterUpload(callable $callbackAfterUpload): UploadType
    {
        $this->callbackAfterUpload = $callbackAfterUpload;

        return $this;
    }
}
