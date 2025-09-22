<?php
/**
 * Cache Class to consume the server local database using a Model of Table
 * @description This class is the base class for all database connections
 * @author Jorge Echeverria <jecheverria@bytes4run.com>
 * @category Class
 * @package CLASSES\Cache
 * @version 1.7.0
 * @date 2024-03-11 | 2025-07-29
 * @time 22:30:00
 * @copyright (c) 2024 - 2025 Bytes4Run
 */
declare (strict_types = 1);


namespace SIMA\CLASSES;

class Cache
{
    private static ?Cache $instance = null;
    private string $cacheDir;
    private int $defaultTTL = 3600; // 1 hour default
    
    private function __construct()
    {
        $this->cacheDir = __DIR__ . '/../../cache/queries/';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    public static function getInstance(): Cache
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Generate cache key from query and params
     */
    public function generateKey(string $query, array $params = []): string
    {
        return md5($query . serialize($params));
    }
    
    /**
     * Store data in cache
     */
    public function set(string $key, mixed $data, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $expiry = time() + $ttl;
        
        $cacheData = [
            'data' => $data,
            'expiry' => $expiry,
            'created' => time()
        ];
        
        $filename = $this->cacheDir . $key . '.cache';
        return file_put_contents($filename, serialize($cacheData)) !== false;
    }
    
    /**
     * Get data from cache
     */
    public function get(string $key): mixed
    {
        $filename = $this->cacheDir . $key . '.cache';
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $content = file_get_contents($filename);
        $cacheData = unserialize($content);
        
        // Check if expired
        if (time() > $cacheData['expiry']) {
            $this->delete($key);
            return null;
        }
        
        return $cacheData['data'];
    }
    
    /**
     * Check if cache exists and is valid
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
    
    /**
     * Delete cache entry
     */
    public function delete(string $key): bool
    {
        $filename = $this->cacheDir . $key . '.cache';
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }
    
    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $files = glob($this->cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    /**
     * Invalidate cache by pattern (for table-based invalidation)
     */
    public function invalidateByPattern(string $pattern): int
    {
        $files = glob($this->cacheDir . '*' . $pattern . '*.cache');
        $deleted = 0;
        foreach ($files as $file) {
            if (unlink($file)) {
                $deleted++;
            }
        }
        return $deleted;
    }
}
