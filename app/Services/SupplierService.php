<?php

    namespace App\Services;

    use App\Enums\SupplierStatus;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Log;

    class SupplierService
    {
        private string $baseUrl;

        public function __construct()
        {
            $this->baseUrl = config('services.supplier.base_url', 'http://localhost:8000');
        }

        /**
         * Request stock reservation from supplier.
         *
         * @return array{accepted: bool, ref: string}
         */
        public function reserve(string $sku, int $qty): array
        {
            try {
                $response = Http::post("{$this->baseUrl}/supplier/reserve", [
                    'sku' => $sku,
                    'qty' => $qty,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                return ['accepted' => false, 'ref' => ''];
            } catch (\Throwable $e) {
                Log::error("Supplier reserve failed: " . $e->getMessage());
                return ['accepted' => false, 'ref' => ''];
            }
        }

        /**
         * Check supplier delivery status by reference.
         *
         * @return SupplierStatus
         */
        public function checkStatus(string $ref): SupplierStatus
        {
            try {
                $response = Http::get("{$this->baseUrl}/supplier/status/{$ref}");

                if ($response->successful()) {
                    $status = $response->json('status', 'fail');
                    return SupplierStatus::tryFrom($status) ?? SupplierStatus::FAIL;
                }

                return SupplierStatus::FAIL;
            } catch (\Throwable $e) {
                Log::error("Supplier status check failed: " . $e->getMessage());
                return SupplierStatus::FAIL;
            }
        }
    }
