<?php

class DivisasHelper {
    private const CACHE_FILE = __DIR__ . '/../../tmp/tasas_bcv.json';
    private const CACHE_TTL = 36000; // 10 horas
    private const FALLBACK_USD = 36.50;
    private const FALLBACK_EUR = 39.50;

    public static function obtenerTasas(bool $forzar = false): array {
        $cache = self::leerCache();
        $cache_valido = $cache && (time() - $cache['_timestamp'] < self::CACHE_TTL);

        if ($cache_valido && !$forzar) {
            return $cache;
        }

        $tasas_api = self::consultarAPI();

        if ($tasas_api !== null) {
            $tasas_api['_timestamp'] = time();
            $tasas_api['origen'] = 'BCV (Tiempo Real)';
            self::guardarCache($tasas_api);
            return $tasas_api;
        }

        // API falló
        if ($cache) {
            $cache['origen'] = 'Cache (desactualizado)';
            $cache['_desactualizado'] = true;
            return $cache;
        }

        // Sin caché y sin API — fallback extremo
        return [
            'usd' => self::FALLBACK_USD,
            'eur' => self::FALLBACK_EUR,
            'fecha' => date('d/m/Y H:i'),
            'fecha_iso' => date('c'),
            'origen' => 'Valor estimado (sin conexión)',
            '_estimado' => true,
        ];
    }

    private static function solicitarHttp(string $url): ?string {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $res = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ($code === 200 && $res) ? $res : null;
        }

        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $data = @file_get_contents($url, false, $ctx);
        return $data ?: null;
    }

    private static function consultarAPI(): ?array {
        try {
            $data_usd = self::solicitarHttp('https://ve.dolarapi.com/v1/dolares/oficial');
            if (!$data_usd) return null;

            $json_usd = json_decode($data_usd, true);
            if (!$json_usd || !isset($json_usd['promedio'])) return null;

            $tasa_usd = round($json_usd['promedio'], 2);

            $tasa_eur = self::FALLBACK_EUR;
            $data_eur = self::solicitarHttp('https://ve.dolarapi.com/v1/euros/oficial');
            if ($data_eur) {
                $json_eur = json_decode($data_eur, true);
                if ($json_eur && isset($json_eur['promedio'])) {
                    $tasa_eur = round($json_eur['promedio'], 2);
                }
            }

            return [
                'usd' => $tasa_usd,
                'eur' => $tasa_eur,
                'fecha' => date('d/m/Y H:i'),
                'fecha_iso' => date('c'),
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    private static function leerCache(): ?array {
        if (!file_exists(self::CACHE_FILE)) return null;
        $data = @file_get_contents(self::CACHE_FILE);
        if (!$data) return null;
        $cache = json_decode($data, true);
        return is_array($cache) ? $cache : null;
    }

    private static function guardarCache(array $tasas): void {
        $dir = dirname(self::CACHE_FILE);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        @file_put_contents(self::CACHE_FILE, json_encode($tasas, JSON_PRETTY_PRINT));
    }

}
