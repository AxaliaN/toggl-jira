<?php
declare(strict_types=1);

namespace TogglJira\Utils;

abstract class ConfigKeyValidator
{
    /**
     * @param array $expectedStructure
     * @param array $config
     * @throws \InvalidArgumentException
     */
    public static function validateConfig(array $expectedStructure, array $config): void
    {
        foreach (array_keys($expectedStructure) as $key) {
            $secondKey = !is_numeric($key) ? $key : $expectedStructure[$key];
            $path[] = $secondKey;

            if (\array_key_exists($key, $config) && \is_array($expectedStructure[$key]) && \is_array($config[$key])) {
                self::checkKeysInStruct($expectedStructure[$key], $config[$key]);
            } elseif (!array_key_exists($secondKey, $config)) {
                throw new \InvalidArgumentException(
                    'Configuration does not match expected configuration for: ' . PHP_EOL . $secondKey
                );
            }
        }
    }

    /**
     * @param array $expectedStructure
     * @param array $config
     * @throws \InvalidArgumentException
     */
    private static function checkKeysInStruct(array $expectedStructure, array $config): void
    {
        foreach (array_keys($expectedStructure) as $key) {
            // if key is present, but not set, the index is numeric so we might need to check the value of the key
            $secondKey = !is_numeric($key) ? $key : $expectedStructure[$key];

            if (\array_key_exists($key, $config) && \is_array($expectedStructure[$key]) && \is_array($config[$key])) {
                self::checkKeysInStruct($expectedStructure[$key], $config[$key]);
            } elseif (!array_key_exists($secondKey, $config)) {
                throw new \InvalidArgumentException(
                    'Configuration does not match expected configuration for: ' . PHP_EOL . $secondKey
                );
            }
        }
    }
}
