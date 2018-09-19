<?php
/**
 * Qiniu plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace gocraft\qiniu\volumes;

use Craft;
use craft\base\Volume;
use craft\errors\VolumeException;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use yii\helpers\StringHelper;

/**
 * Class QiniuVolume
 *
 * @package gocraft\qiniu\volumes
 * @author Panlatent <panlatent@gmail.com>
 */
class QiniuVolume extends Volume
{
    /**
     * @var string
     */
    public $accessKey = '';

    /**
     * @var string
     */
    public $secretKey = '';

    /**
     * @var array
     */
    public $uploadTokens = [];

    /**
     * @var string
     */
    public $bucket = '';

    /**
     * @var string
     */
    public $isPublic = true;

    /**
     * @var string
     */
    public $root = '';

    /**
     * @var string
     */
    public $delimiter = '/';

    /**
     * @var bool
     */
    public $serverHttpsDownload = false;

    /**
     * @var int
     */
    public $serverDownloadExpires = 300;

    /**
     * @var int
     */
    public $clientDownloadExpires = 3600;

    /**
     * @var Auth
     */
    private $_auth;

    /**
     * @var BucketManager
     */
    private $_bucketManager;

    /**
     * @var UploadManager
     */
    private $_uploadManager;

    // Static
    // =========================================================================

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('qiniu', 'Qiniu Volume');
    }

    /**
     * Init.
     */
    public function init()
    {
        parent::init();

        $this->root = trim($this->root, '/');
    }

    /**
     * @param string $url
     * @return string
     */
    public function grantServerPrivateDownload(string $url): string
    {
        if ($this->isPublic) {
            return $url;
        }
        if ($url[0] == '/') {
            $schema = $this->serverHttpsDownload ? 'https' : 'http';
            $url = $schema . '://' . ltrim($url, '/');
        }
        $url = $this->getAuth()->privateDownloadUrl($url, $this->serverDownloadExpires);

        return $url;
    }

    /**
     * @param string $url
     * @return string
     */
    public function grantClientPrivateDownload(string $url): string
    {
        if ($this->isPublic) {
            return $url;
        }
        if ($url[0] == '/') {
            $schema = Craft::$app->request->isSecureConnection ? 'https' : 'http';
            $url = $schema . '://' . ltrim($url, '/');
        }
        $url = $this->getAuth()->privateDownloadUrl($url, $this->clientDownloadExpires);

        return $url;
    }

    // Public Methods
    // =========================================================================

    /**
     * @param string $directory
     * @param bool $recursive
     * @return array
     */
    public function getFileList(string $directory, bool $recursive): array
    {
        /** @noinspection PhpParamsInspection */
        $list = $this->getBucketManager()->listFiles($this->bucket, $this->resolvePath($directory), '', 1000, $this->delimiter);

        $output = [];

        foreach ($list as $block) {
            if (empty($block)) {
                continue;
            }
            foreach ($block['items'] as $object) {
                $output[$object['key']] = [
                    'path' => $object['key'],
                    'dirname' => StringHelper::dirname($object['key']),
                    'basename' => StringHelper::basename($object['key']),
                    'size' => $object['fsize'],
                    'timestamp' => $object['putTime'],
                    'mimeType' => $object['mimeType'],
                ];
            }
        }

        return $output;
    }

    /**
     * @param string $uri
     * @return array
     */
    public function getFileMetadata(string $uri): array
    {
        /** @noinspection PhpParamsInspection */
        return $this->getBucketManager()->stat($this->bucket, $this->resolvePath($uri));
    }

    /**
     * @param string $path
     * @param resource $stream
     * @param array $config
     * @return array
     */
    public function createFileByStream(string $path, $stream, array $config): array
    {
        $token = $this->getUploadToken($this->bucket);

        /** @noinspection PhpParamsInspection */
        return $this->getUploadManager()->put($token, $this->resolvePath($path), stream_get_contents($stream));
    }

    /**
     * @param string $path
     * @param resource $stream
     * @param array $config
     * @return array
     */
    public function updateFileByStream(string $path, $stream, array $config): array
    {
        $token = $this->getUploadToken($this->bucket);

        /** @noinspection PhpParamsInspection */
        return $this->getUploadManager()->put($token, $this->resolvePath($path), stream_get_contents($stream));
    }

    /**
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        /** @noinspection PhpParamsInspection */
        list(, $error) = $this->getBucketManager()->stat($this->bucket, $this->resolvePath($path));

        return $error !== null;
    }

    /**
     * @param string $path
     */
    public function deleteFile(string $path)
    {
        /** @noinspection PhpParamsInspection */
        $error = $this->getBucketManager()->delete($this->bucket, $this->resolvePath($path));

        if ($error !== null) {
            Craft::warning("Delete a file failed: " . $error->message());
        }
    }

    /**
     * @param string $path
     * @param string $newPath
     */
    public function renameFile(string $path, string $newPath)
    {
        /** @noinspection PhpParamsInspection */
        $this->getBucketManager()->rename(
            $this->bucket,
            $this->resolvePath($path),
            $this->resolvePath($newPath)
        );
    }

    /**
     * @param string $path
     * @param string $newPath
     */
    public function copyFile(string $path, string $newPath)
    {
        /** @noinspection PhpParamsInspection */
        $this->getBucketManager()->copy(
            $this->bucket,
            $this->resolvePath($path),
            $this->resolvePath($newPath)
        );
    }

    /**
     * @param string $uriPath
     * @param string $targetPath
     * @return int
     */
    public function saveFileLocally(string $uriPath, string $targetPath): int
    {
        $url = $this->getRootUrl() . $uriPath;
        if ($url[0] == '/') {
            $schema = $this->serverHttpsDownload ? 'https' : 'http';
            $url = $schema . '://' . ltrim($url, '/');
        }
        if (!$this->isPublic) {
            $url = $this->grantServerPrivateDownload($url);
        }
        copy($url, $targetPath);

        return filesize($targetPath);
    }

    public function getFileStream(string $uriPath)
    {

    }

    /**
     * @param string $path
     * @return bool
     */
    public function folderExists(string $path): bool
    {
        return true;
    }

    public function createDir(string $path)
    {
        // Empty
        // Cloud Storage only using object name.
    }

    /**
     * @inheritdoc
     */
    public function deleteDir(string $path)
    {
        throw new VolumeException('No support delete folder');
    }

    /**
     * @inheritdoc
     */
    public function renameDir(string $path, string $newName)
    {
        throw new VolumeException('No support remame folder');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $templateString = file_get_contents(__DIR__ . '/../templates/volumen-settings.twig');

        return Craft::$app->getView()->renderString($templateString, [
            'volume' => $this,
        ]);
    }

    /**
     * @return Auth
     */
    public function getAuth(): Auth
    {
        if ($this->_auth === null) {
            $this->_auth = new Auth($this->accessKey, $this->secretKey);
        }

        return $this->_auth;
    }

    /**
     * @param Auth $auth
     */
    public function setAuth(Auth $auth)
    {
        $this->_auth = $auth;
    }

    /**
     * @return BucketManager
     */
    public function getBucketManager(): BucketManager
    {
        if ($this->_bucketManager === null) {
            $this->_bucketManager = new BucketManager($this->getAuth());
        }

        return $this->_bucketManager;
    }

    /**
     * @param BucketManager $bucketManager
     */
    public function setBucketManager(BucketManager $bucketManager)
    {
        $this->_bucketManager = $bucketManager;
    }


    /**
     * @return UploadManager
     */
    public function getUploadManager(): UploadManager
    {
        if ($this->_uploadManager === null) {
            $this->_uploadManager = new UploadManager();
        }

        return $this->_uploadManager;
    }

    /**
     * @param UploadManager $uploadManager
     */
    public function setUploadManager(UploadManager $uploadManager)
    {
        $this->_uploadManager = $uploadManager;
    }

    /**
     * @param string $bucket
     * @return string
     */
    public function getUploadToken(string $bucket): string
    {
        if (!isset($this->uploadTokens[$bucket])) {
            $this->uploadTokens[$bucket] = $this->getAuth()->uploadToken($bucket);
        }

        return $this->uploadTokens[$bucket];
    }

    /**
     * @param string $path
     * @return string
     */
    protected function resolvePath(string $path): string
    {
        if ($this->root == '') {
            return $path;
        }

        return $this->root . '/' . ltrim($path, '/');
    }
}