<?php
/**
 * Created by PhpStorm.
 * User: fanat
 * Date: 08.07.2016
 * Time: 17:25
 */

namespace CrazyFactory\Core\Serializers;

use CrazyFactory\Data\Serializers\Base\SerializerBase;

class DataToDataSerializer extends SerializerBase
{
    protected $restorePropertyMap = array();
    protected $serializePropertyMap = array();

    protected $postSerializeFn = null;
    protected $postRestoreFn = null;

    protected $preSerializeFn = null;
    protected $preRestoreFn = null;

    protected $restoreUnmappedByDefault = true;
    protected $serializeUnmappedByDefault = true;

    /**
     * DataToDataSerializer constructor.
     *
     * @param array $serializePropertyMap InputKey to OutputKey Map used during serialization. false-Values will be skipped.
     * @param array $restorePropertyMap
     * @param null|callable $postSerializeFn
     * @param null|callable $postRestoreFn
     * @param null|callable $preSerializeFn
     * @param null|callable $preRestoreFn
     */
    function __construct(
        $serializePropertyMap,
        $restorePropertyMap,
        $postSerializeFn = null,
        $postRestoreFn = null,
        $preSerializeFn = null,
        $preRestoreFn = null
    )
    {
        // Validate maps
        if (!is_array($serializePropertyMap)) {
            throw new \InvalidArgumentException('$serializePropertyMap is not array');
        }
        if (!is_array($restorePropertyMap)) {
            throw new \InvalidArgumentException('$restorePropertyMap is not array');
        }

        // Validate processors
        if ($postRestoreFn !== null && !is_callable($postRestoreFn)) {
            throw new \InvalidArgumentException('$postRestoreFn must be null or callable');
        }
        if ($postSerializeFn !== null && !is_callable($postSerializeFn)) {
            throw new \InvalidArgumentException('$postSerializeFn must be null or callable');
        }
        if ($preRestoreFn !== null && !is_callable($preRestoreFn)) {
            throw new \InvalidArgumentException('$preRestoreFn must be null or callable');
        }
        if ($preSerializeFn !== null && !is_callable($preSerializeFn)) {
            throw new \InvalidArgumentException('$preSerializeFn must be null or callable');
        }


        // Accept maps
        $this->restorePropertyMap = $restorePropertyMap;
        $this->serializePropertyMap = $serializePropertyMap;

        // Accept processors
        if ($postRestoreFn !== null)
            $this->postRestoreFn = $postRestoreFn;
        if ($postSerializeFn !== null)
            $this->postSerializeFn = $postSerializeFn;
        if ($preSerializeFn !== null)
            $this->preSerializeFn = $preSerializeFn;
        if ($preRestoreFn !== null)
            $this->preRestoreFn = $preRestoreFn;
    }

    protected function process($input, $map, $preProcessFn, $postProcessFn, $copyUnmappedProperties = true)
    {
        // Call preprocessor
        if ($preProcessFn) {
            $input = $preProcessFn($input);
        }

        // Reduce Map to existing values (because the data might be partial)
        $map = array_intersect_key($map, $input);

        // Create the initial result array
        $out = $copyUnmappedProperties
            ? array_diff_key($input, $map)
            : array();

        // Map available properties (or skip on false)
        if (is_array($map)) {
            foreach ($map as $inKey => $outKey) {
                if ($outKey !== false && key_exists($inKey, $input)) {
                    $out[$outKey] = $input[$inKey];
                }
            }
        }

        // Call postprocessor (or return)
        return is_callable($postProcessFn)
            ? $postProcessFn($out, $input)
            : $out;
    }

    /**
     * @param array $data data to be serialized
     * @return array The serialized data
     */
    public function serialize($data)
    {
        return $this->process(
            $data,
            $this->serializePropertyMap,
            $this->preSerializeFn,
            $this->postSerializeFn,
            $this->serializeUnmappedByDefault);
    }

    /**
     * @param array $data Serialized data to be restored
     * @return array The restored data
     */
    public function restore($data)
    {
        return $this->process(
            $data,
            $this->restorePropertyMap,
            $this->preRestoreFn,
            $this->postRestoreFn,
            $this->restoreUnmappedByDefault);
    }
}