<?php

namespace App\Services;

use App\Models\Courierapi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoCourierService
{
    protected Courierapi $config;

    public function __construct()
    {
        $this->config = Courierapi::where('type', 'pathao')->firstOrFail();
    }

    public function getToken(): array
    {
        $baseUrl = rtrim($this->config->url ?: 'https://api-hermes.pathao.com', '/');

        if (
            empty($this->config->client_id) ||
            empty($this->config->client_secret) ||
            empty($this->config->username) ||
            empty($this->config->password)
        ) {
            return [
                'success' => false,
                'message' => 'Pathao Client ID, Client Secret, Username and Password are required.',
            ];
        }

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post($baseUrl . '/aladdin/api/v1/issue-token', [
                    'client_id' => $this->config->client_id,
                    'client_secret' => $this->config->client_secret,
                    'username' => $this->config->username,
                    'password' => $this->config->password,
                    'grant_type' => $this->config->grant_type ?: 'password',
                ]);

            $data = $response->json();

            if (!$response->successful()) {
                Log::error('Pathao token request failed', [
                    'status' => $response->status(),
                    'response' => $data,
                ]);

                return [
                    'success' => false,
                    'message' => $data['message']
                        ?? $data['error']
                        ?? 'Pathao token request failed.',
                    'status' => $response->status(),
                    'response' => $data,
                ];
            }

            $token = $data['access_token'] ?? $data['token'] ?? null;
            $refreshToken = $data['refresh_token'] ?? null;

            if (!$token) {
                Log::error('Pathao token missing from response', [
                    'response' => $data,
                ]);

                return [
                    'success' => false,
                    'message' => 'Pathao API did not return an access token.',
                    'response' => $data,
                ];
            }

            $expiresIn = (int) ($data['expires_in'] ?? 0);

            $this->config->token = $token;
            $this->config->refresh_token = $refreshToken;

            if ($expiresIn > 0) {
                $this->config->token_expires_at = now()->addSeconds($expiresIn);
            }

            $this->config->save();

            return [
                'success' => true,
                'message' => 'Pathao access token generated successfully.',
                'token' => $token,
                'expires_in' => $expiresIn,
                'expires_at' => $this->config->token_expires_at,
            ];

        } catch (\Throwable $e) {

            Log::error('Pathao token exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Pathao connection error: ' . $e->getMessage(),
            ];
        }
    }

    public function testConnection(): array
    {
        if (
            !empty($this->config->token) &&
            $this->config->token_expires_at &&
            $this->config->token_expires_at->isFuture()
        ) {
            return [
                'success' => true,
                'message' => 'Pathao access token is active.',
            ];
        }

        return $this->getToken();
    }
}
