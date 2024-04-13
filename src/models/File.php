<?php

namespace app\models;

use app\behaviors\UuidBehavior;
use app\helpers\Url;
use app\traits\FindModelTrait;
use JsonSerializable;
use League\Flysystem\FilesystemException;
use thamtech\uuid\validators\UuidValidator;
use Throwable;
use Yii;
use yii\base\ErrorException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\web\UploadedFile;

/**
 * This is the model class for table "file".
 *
 * @property string      $id
 * @property string      $target_class
 * @property string      $target_id
 * @property string      $target_attribute
 * @property string|null $key
 * @property string      $name
 * @property string      $mime_type
 * @property int         $size
 * @property int         $created_at
 * @property int         $updated_at
 *
 * @property string $content
 * @see self::getContent()
 * @see self::setContent()
 *
 * @property-read string $dataUri
 * @see self::getDataUri()
 */
class File extends ActiveRecord implements JsonSerializable
{
    use FindModelTrait;

    private string|null $_content = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            UuidBehavior::class,
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'target_class', 'target_id', 'target_attribute', 'name', 'mime_type', 'size'], 'required'],
            [['id'], UuidValidator::class],
            [['target_class', 'target_id', 'target_attribute', 'key', 'name', 'mime_type'], 'string', 'max' => 255],
            [['size', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id'               => 'ID',
            'target_class'     => 'Target Class',
            'target_id'        => 'Target ID',
            'target_attribute' => 'Target Attribute',
            'key'              => 'Key',
            'name'             => 'Name',
            'mime_type'        => 'Mime Type',
            'size'             => 'Size',
            'created_at'       => 'Created At',
            'updated_at'       => 'Updated At',
        ];
    }

    /**
     * @param File $file
     *
     * @return bool
     */
    public function isSameFile(File $file): bool
    {
        return (($this->target_class === $file->target_class)
            && ($this->target_id === $file->target_id)
            && ($this->target_attribute === $file->target_attribute)
            && ($this->key === $file->key));
    }

    /**
     * @param UploadedFile $uploadedFile
     *
     * @return static
     */
    public static function createFromUploadedFile(UploadedFile $uploadedFile): static
    {
        $file = new static();

        $file->name      = $uploadedFile->name;
        $file->mime_type = $uploadedFile->type;
        $file->size      = $uploadedFile->size;
        $file->content   = file_get_contents($uploadedFile->tempName);

        return $file;
    }

    /**
     * @param File      $fromFile
     * @param File|null $oldFile
     *
     * @return static
     */
    public static function createFromFile(File $fromFile, File|null $oldFile): static
    {
        if ($oldFile && $oldFile->isSameFile($fromFile)) {
            return $fromFile;
        }

        $file = new static();

        $file->name      = $fromFile->name;
        $file->mime_type = $fromFile->mime_type;
        $file->size      = $fromFile->size;
        $file->content   = $fromFile->content;

        return $file;
    }

    /**
     * @param string $dataUri
     *
     * @return static
     * @throws ErrorException
     * @throws \Exception
     */
    public static function createFromDataUri(string $dataUri): static
    {
        if (!Url::parseDataUri($dataUri, type: $type, data: $data)) {
            throw new ErrorException('Invalid DataUri');
        }

        $file = new static();

        $file->name      = 'file.dat';
        $file->mime_type = $type;
        $file->size      = strlen($data);
        $file->content   = $data;

        return $file;
    }

    /**
     * @param string $url
     *
     * @return static
     * @throws ErrorException
     * @throws Exception
     */
    public static function createFromUrl(string $url): static
    {
        if (str_starts_with($url, 'data:')) {
            return static::createFromDataUri($url);
        }

        $client = new Client();
        $response = $client->get($url)->send();

        if (!$response->isOk) {
            throw new ErrorException('Can\'t get file from url');
        }

        $mimeType = $response->headers->get('content-type', 'application/octet-stream');

        $file = new static();

        $file->name      = basename($url);
        $file->mime_type = $mimeType;
        $file->size      = strlen($response->content);
        $file->content   = $response->content;

        return $file;
    }

    /**
     * @param mixed $condition
     * @param array $params
     *
     * @return int
     * @throws Throwable
     */
    public static function deleteAll($condition = null, $params = []): int
    {
        $ids = [];

        $result = Yii::$app->db->transaction(function () use ($condition, $params, &$ids) {
            $ids = static::find()
                ->select(['id'])
                ->where($condition, $params)
                ->column();

            return parent::deleteAll(['id' => $ids]);
        });

        if (!$result) {
            return $result;
        }

        foreach ($ids as $id) {
            try {
                Yii::$app->file->delete($id);
            } catch (Throwable $e) {
                Yii::$app->errorHandler->logException($e);
            }
        }

        return $result;
    }

    /**
     * @return void
     */
    public function afterDelete(): void
    {
        parent::afterDelete();

        try {
            Yii::$app->file->delete($this->id);
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
        }
    }

    /**
     * @param $insert
     * @param $changedAttributes
     *
     * @return void
     * @throws FilesystemException
     */
    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->_content !== null) {
            Yii::$app->file->write($this->id, $this->_content, true);

            $this->_content = null;
        }
    }

    /**
     * @return string
     * @throws FilesystemException
     */
    public function getContent(): string
    {
        if ($this->_content !== null) {
            return $this->_content;
        }

        return Yii::$app->file->read($this->id);
    }

    /**
     * @param string $content
     *
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->_content = $content;
    }

    /**
     * @return string
     */
    public function getDataUri(): string
    {
        return 'data:' . $this->mime_type . ';base64,' . base64_encode($this->content);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->dataUri;
    }

    /**
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->dataUri;
    }
}
