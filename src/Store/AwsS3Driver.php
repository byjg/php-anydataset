<?php
/**
 * User: jg
 * Date: 08/02/17
 * Time: 19:55
 */

namespace ByJG\AnyDataset\Store;

use Aws\S3\S3Client;
use ByJG\AnyDataset\NoSqlKeyValueInterface;
use ByJG\AnyDataset\Dataset\ArrayDataset;
use ByJG\AnyDataset\IteratorInterface;
use ByJG\Util\Uri;

class AwsS3Driver implements NoSqlKeyValueInterface
{

    /**
     * @var S3Client
     */
    protected $s3Client;

    /**
     * @var string
     */
    protected $bucket;

    /**
     * AwsS3Driver constructor.
     *
     *  s3://key:secret@region/bucket
     *
     * @param string $connectionString
     */
    public function __construct($connectionString)
    {
        $uri = new Uri($connectionString);

        $this->s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => $uri->getHost(),
            'credentials' => [
                'key'    => $uri->getUsername(),
                'secret' => $uri->getPassword(),
            ],
        ]);

        $this->bucket = preg_replace('~^/~', '', $uri->getPath());
    }

    /**
     * @param array $options
     * @return IteratorInterface
     */
    public function getIterator($options = [])
    {
        $data = array_merge(
            [
                'Bucket' => $this->bucket,
            ],
            $options
        );

        $result = $this->s3Client->listObjects($data);

        return (new ArrayDataset($result['Contents']))->getIterator();

    }

    public function get($key, $options = [])
    {
        $data = array_merge(
            [
                'Bucket' => $this->bucket,
                'Key'    => $key
            ],
            $options
        );

        return $this->s3Client->getObject($data);
    }

    public function put($key, $value, $contentType = null, $options = [])
    {
        $data = array_merge(
            [
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'Body'   => $value,
            ],
            $options
        );

        if (!empty($contentType)) {
            $data['ContentType'] = $contentType;
        }

        if (!isset($data['ACL'])) {
            $data['ACL'] = 'private';
        }

        return $this->s3Client->putObject($data);
    }

    public function remove($key, $options = [])
    {
        $data = array_merge(
            [
                'Bucket' => $this->bucket,
                'Key'    => $key
            ],
            $options
        );

        $this->s3Client->deleteObject($data);
    }

    public function getDbConnection()
    {
        return $this->s3Client;
    }


}