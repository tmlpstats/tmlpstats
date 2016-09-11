<?php
namespace Tmlpstats\Domain;

use Illuminate\Contracts\Support\Arrayable;
use TmlpStats\Contracts\Referenceable;

class ValidationMessage implements Arrayable, \JsonSerializable
{
    /**
     * Message identifier
     * @var string
     */
    protected $id = null;

    /**
     * Message level. (error|warning|info)
     * @var string
     */
    protected $level = null;

    /**
     * Object/data message refers to
     * @var array|int|Referenceable
     */
    protected $reference = null;

    /**
     * Message parameters
     * @var array
     */
    protected $parameters = [];

    /**
     * Convenience method for creating an error message
     *
     * @param  array $data       Array of message data
     * @return ValidationMessage
     */
    public static function error($data)
    {
        return static::create(__FUNCTION__, $data);
    }

    /**
     * Convenience method for creating a warning message
     *
     * @param  array $data       Array of message data
     * @return ValidationMessage
     */
    public static function warning($data)
    {
        return static::create(__FUNCTION__, $data);
    }

    /**
     * Convenience method for creating an info message
     *
     * @param  array $data       Array of message data
     * @return ValidationMessage
     */
    public static function info($data)
    {
        return static::create(__FUNCTION__, $data);
    }

    /**
     * Create a new message object
     *
     * @param  string $level Message level
     * @param  array  $data  Array of message data
     *
     *     $data = [
     *         id    => Message identifier. Must match an entry in resources/lang/en/messages.php
     *         ref   => Some way of identifying the data this message refers to. This could be a
     *                  string, int, array of scalars or an object that implement Referenceable.
     *                  If it's the latter, we'll call $obj->getReference()
     *         params => An array of substitution arguments for the message
     *     ]
     *
     * @return ValidationMessage
     */
    public static function create($level = 'error', $data)
    {
        if (!isset($data['id'])) {
            throw new \Exception('id property is required.');
        }

        $id = $data['id'];
        $parameters = isset($data['params']) ? $data['params'] : [];
        $ref = isset($data['ref']) ? $data['ref'] : null;

        if ($ref instanceof Referenceable) {
            $ref = $ref->getReference();
        }

        return new static($level, $id, $parameters, $ref);
    }

    public function __construct($level, $id, $parameters = [], $reference = null)
    {
        $this->id = $id;
        $this->level = $level;
        $this->parameters = $parameters;
        $this->reference = $reference;
    }

    public function level()
    {
        return $this->level;
    }

    /**
     * Get resolved message text
     *
     * @param  string $id        Message key for translation lookup
     * @param  array  $arguments Array of substitution arguments
     * @return string            Message text with substituted fields
     */
    protected function formatMessage($id, $arguments)
    {
        return trans("messages.{$id}", $arguments);
    }

    /**
     * Implementation for Arrayable
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'reference' => $this->reference,
            'message' => $this->formatMessage($this->id, $this->parameters),
        ];
    }

    /**
     * Implementation for JsonSerializable interface
     *
     * @return array  Array that is serializable with json_encode()
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
