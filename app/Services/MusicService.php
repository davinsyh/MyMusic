<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MusicService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('PYTHON_MICROSERVICE_URL', 'http://127.0.0.1:8001');
    }

    /**
     * Search for tracks by query.
     */
    public function search(string $query)
    {
        $cacheKey = 'ytmusic:search_v2:' . md5($query);

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($query) {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/search', [
                    'q' => $query
                ]);

                if ($response->successful()) {
                    return $response->json('data') ?? [];
                }
                
                Log::error('Python Microservice Search Error: ' . $response->body());
                return [];
            } catch (\Exception $e) {
                Log::error('Python Microservice Connection Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get top charts.
     */
    public function getCharts()
    {
        $cacheKey = 'ytmusic:charts';

        return Cache::remember($cacheKey, now()->addMinutes(60), function () {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/charts');

                if ($response->successful()) {
                    return $response->json('data') ?? [];
                }
                
                Log::error('Python Microservice Charts Error: ' . $response->body());
                return [];
            } catch (\Exception $e) {
                Log::error('Python Microservice Connection Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get all home sections.
     */
    public function getHomeSections()
    {
        $cacheKey = 'ytmusic:home_sections_v2';

        return Cache::remember($cacheKey, now()->addMinutes(60), function () {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/home');

                if ($response->successful()) {
                    return $response->json('data') ?? [];
                }
                
                Log::error('Python Microservice Home Error: ' . $response->body());
                return [];
            } catch (\Exception $e) {
                Log::error('Python Microservice Connection Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get playlist or album details by ID.
     */
    public function getPlaylistDetails(string $id)
    {
        $cacheKey = "ytmusic:playlist:{$id}";

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($id) {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . "/playlist/{$id}");

                if ($response->successful()) {
                    return $response->json('data') ?? null;
                }
                
                Log::error('Python Microservice Playlist Error: ' . $response->body());
                return null;
            } catch (\Exception $e) {
                Log::error('Python Microservice Connection Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Get track details by ID.
     */
    public function getTrack(string $id)
    {
        $cacheKey = 'ytmusic:track:' . $id;

        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($id) {
            try {
                $response = Http::timeout(10)->get($this->baseUrl . '/track/' . $id);

                if ($response->successful()) {
                    return $response->json('data') ?? null;
                }
                
                Log::error('Python Microservice Track Error: ' . $response->body());
                return null;
            } catch (\Exception $e) {
                Log::error('Python Microservice Connection Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Resolve YouTube Music internal ID (lp-, lm-) to actual YouTube video ID.
     */
    public function resolveVideoId(string $id)
    {
        $cacheKey = 'ytmusic:resolve:' . $id;

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($id) {
            try {
                $response = Http::timeout(8)->get($this->baseUrl . '/resolve/' . $id);

                if ($response->successful()) {
                    return $response->json();
                }

                // Fallback: strip prefix
                $videoId = preg_match('/^(lp-|lm-)(.+)$/', $id, $m) ? $m[2] : $id;
                return ['videoId' => $videoId, 'resolved' => false];
            } catch (\Exception $e) {
                Log::error('Python Microservice Resolve Error: ' . $e->getMessage());
                $videoId = preg_match('/^(lp-|lm-)(.+)$/', $id, $m) ? $m[2] : $id;
                return ['videoId' => $videoId, 'resolved' => false];
            }
        });
    }
}
