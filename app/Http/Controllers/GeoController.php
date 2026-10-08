<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeoController extends Controller
{
    /**
     * Danh sách 34 Tỉnh/TP mới (Chuẩn 2 Cấp 2025)
     */
    protected array $newProvinces = [
        ['code' => 'HN', 'name' => 'Thành phố Hà Nội', 'short_name' => 'Hà Nội'],
        ['code' => 'HCM', 'name' => 'Thành phố Hồ Chí Minh', 'short_name' => 'Hồ Chí Minh'],
        ['code' => 'HP', 'name' => 'Thành phố Hải Phòng', 'short_name' => 'Hải Phòng'],
        ['code' => 'DN', 'name' => 'Thành phố Đà Nẵng', 'short_name' => 'Đà Nẵng'],
        ['code' => 'CT', 'name' => 'Thành phố Cần Thơ', 'short_name' => 'Cần Thơ'],
        ['code' => 'HUE', 'name' => 'Thành phố Huế', 'short_name' => 'Huế'],
        ['code' => 'NA', 'name' => 'Tỉnh Nghệ An', 'short_name' => 'Nghệ An'],
        ['code' => 'HT', 'name' => 'Tỉnh Hà Tĩnh', 'short_name' => 'Hà Tĩnh'],
        ['code' => 'TH', 'name' => 'Tỉnh Thanh Hóa', 'short_name' => 'Thanh Hóa'],
        ['code' => 'QN', 'name' => 'Tỉnh Quảng Ninh', 'short_name' => 'Quảng Ninh'],
        ['code' => 'BN', 'name' => 'Tỉnh Bắc Ninh', 'short_name' => 'Bắc Ninh'],
        ['code' => 'HY', 'name' => 'Tỉnh Hưng Yên', 'short_name' => 'Hưng Yên'],
        ['code' => 'NB', 'name' => 'Tỉnh Ninh Bình', 'short_name' => 'Ninh Bình'],
        ['code' => 'PT', 'name' => 'Tỉnh Phú Thọ', 'short_name' => 'Phú Thọ'],
        ['code' => 'TN', 'name' => 'Tỉnh Thái Nguyên', 'short_name' => 'Thái Nguyên'],
        ['code' => 'TQ', 'name' => 'Tỉnh Tuyên Quang', 'short_name' => 'Tuyên Quang'],
        ['code' => 'LC', 'name' => 'Tỉnh Lào Cai', 'short_name' => 'Lào Cai'],
        ['code' => 'LS', 'name' => 'Tỉnh Lạng Sơn', 'short_name' => 'Lạng Sơn'],
        ['code' => 'CB', 'name' => 'Tỉnh Cao Bằng', 'short_name' => 'Cao Bằng'],
        ['code' => 'DB', 'name' => 'Tỉnh Điện Biên', 'short_name' => 'Điện Biên'],
        ['code' => 'LCH', 'name' => 'Tỉnh Lai Châu', 'short_name' => 'Lai Châu'],
        ['code' => 'SL', 'name' => 'Tỉnh Sơn La', 'short_name' => 'Sơn La'],
        ['code' => 'HB', 'name' => 'Tỉnh Hòa Bình', 'short_name' => 'Hòa Bình'],
        ['code' => 'QT', 'name' => 'Tỉnh Quảng Trị', 'short_name' => 'Quảng Trị'],
        ['code' => 'QNG', 'name' => 'Tỉnh Quảng Ngãi', 'short_name' => 'Quảng Ngãi'],
        ['code' => 'GL', 'name' => 'Tỉnh Gia Lai', 'short_name' => 'Gia Lai'],
        ['code' => 'DL', 'name' => 'Tỉnh Đắk Lắk', 'short_name' => 'Đắk Lắk'],
        ['code' => 'KH', 'name' => 'Tỉnh Khánh Hòa', 'short_name' => 'Khánh Hòa'],
        ['code' => 'LD', 'name' => 'Tỉnh Lâm Đồng', 'short_name' => 'Lâm Đồng'],
        ['code' => 'DN_T', 'name' => 'Tỉnh Đồng Nai', 'short_name' => 'Đồng Nai'],
        ['code' => 'TN_T', 'name' => 'Tỉnh Tây Ninh', 'short_name' => 'Tây Ninh'],
        ['code' => 'LA', 'name' => 'Tỉnh Long An', 'short_name' => 'Long An'],
        ['code' => 'AG', 'name' => 'Tỉnh An Giang', 'short_name' => 'An Giang'],
        ['code' => 'CM', 'name' => 'Tỉnh Cà Mau', 'short_name' => 'Cà Mau'],
    ];

    /**
     * API: Lấy danh sách 63 Tỉnh/TP cũ (3 cấp truyền thống)
     */
    public function getOldProvinces()
    {
        return Cache::remember('geo_provinces_v3', 86400 * 7, function () {
            try {
                $response = Http::timeout(4)->get('https://provinces.open-api.vn/api/?depth=1');
                if ($response->successful()) {
                    $data = $response->json();
                    return array_map(function ($p) {
                        $short = preg_replace('/^(Thành phố|Tỉnh)\s+/ui', '', $p['name']);
                        return [
                            'code' => $p['code'],
                            'name' => $p['name'],
                            'short_name' => $short,
                        ];
                    }, $data);
                }
            } catch (\Throwable $e) {}

            return $this->fallbackProvinces();
        });
    }

    /**
     * API: Lấy danh sách Quận/Huyện cũ theo Tỉnh/TP
     */
    public function getOldDistricts(Request $request)
    {
        $code = $request->input('province_code');
        $name = trim($request->input('province_name', ''));

        if (!$code && $name) {
            $provinces = $this->getOldProvinces();
            foreach ($provinces as $p) {
                if (mb_stripos($p['name'], $name) !== false || mb_stripos($name, $p['short_name']) !== false || mb_stripos($p['short_name'], $name) !== false) {
                    $code = $p['code'];
                    break;
                }
            }
        }

        if (!$code) {
            return response()->json([]);
        }

        $cacheKey = "geo_districts_v3_{$code}";
        $districts = Cache::remember($cacheKey, 86400 * 7, function () use ($code) {
            try {
                $response = Http::timeout(4)->get("https://provinces.open-api.vn/api/p/{$code}?depth=2");
                if ($response->successful()) {
                    $data = $response->json();
                    return array_map(function ($d) {
                        $short = preg_replace('/^(Quận|Huyện|Thị xã|Thành phố)\s+/ui', '', $d['name']);
                        return [
                            'code' => $d['code'],
                            'name' => $d['name'],
                            'short_name' => $short,
                        ];
                    }, $data['districts'] ?? []);
                }
            } catch (\Throwable $e) {}

            return [];
        });

        return response()->json($districts);
    }

    /**
     * API: Lấy danh sách Xã/Phường cũ theo Quận/Huyện
     */
    public function getOldWards(Request $request)
    {
        $code = $request->input('district_code');
        $name = trim($request->input('district_name', ''));
        $provinceCode = $request->input('province_code');

        if (!$code && $name && $provinceCode) {
            $req = new Request(['province_code' => $provinceCode]);
            $districts = $this->getOldDistricts($req)->getData(true);
            foreach ($districts as $d) {
                if (mb_stripos($d['name'], $name) !== false || mb_stripos($name, $d['short_name']) !== false || mb_stripos($d['short_name'], $name) !== false) {
                    $code = $d['code'];
                    break;
                }
            }
        }

        if (!$code) {
            return response()->json([]);
        }

        $cacheKey = "geo_wards_v3_{$code}";
        $wards = Cache::remember($cacheKey, 86400 * 7, function () use ($code) {
            try {
                $response = Http::timeout(4)->get("https://provinces.open-api.vn/api/d/{$code}?depth=2");
                if ($response->successful()) {
                    $data = $response->json();
                    return array_map(function ($w) {
                        $short = preg_replace('/^(Phường|Xã|Thị trấn)\s+/ui', '', $w['name']);
                        return [
                            'code' => $w['code'],
                            'name' => $w['name'],
                            'short_name' => $short,
                        ];
                    }, $data['wards'] ?? []);
                }
            } catch (\Throwable $e) {}

            return [];
        });

        return response()->json($wards);
    }

    /**
     * API: Lấy danh sách 34 Tỉnh/TP mới (Chuẩn 2 cấp 2025)
     */
    public function getNewProvinces()
    {
        return response()->json($this->newProvinces);
    }

    /**
     * API: Gợi ý Xã/Phường mới (theo từ khóa hoặc tỉnh thành)
     */
    public function getNewWards(Request $request)
    {
        $q = trim($request->input('q', ''));
        $provinceParam = trim($request->input('province', ''));

        // 1. Nếu có tên tỉnh/TP
        $provinceCode = null;
        if ($provinceParam) {
            $provinces = $this->getOldProvinces();
            $cleanProv = preg_replace('/^(Thành phố|Tỉnh)\s+/ui', '', $provinceParam);
            foreach ($provinces as $p) {
                if ($p['name'] === $provinceParam || 
                    $p['short_name'] === $provinceParam || 
                    $p['short_name'] === $cleanProv || 
                    mb_stripos($p['name'], $cleanProv) !== false ||
                    mb_stripos($cleanProv, $p['short_name']) !== false) {
                    $provinceCode = $p['code'];
                    break;
                }
            }
        }

        if ($provinceCode) {
            $cacheKey = "geo_all_wards_p_{$provinceCode}";
            $wards = Cache::remember($cacheKey, 86400 * 7, function () use ($provinceCode) {
                try {
                    $response = Http::timeout(5)->get("https://provinces.open-api.vn/api/p/{$provinceCode}?depth=3");
                    if ($response->successful()) {
                        $pData = $response->json();
                        $list = [];
                        foreach ($pData['districts'] ?? [] as $d) {
                            $dName = $d['name'];
                            foreach ($d['wards'] ?? [] as $w) {
                                $short = preg_replace('/^(Phường|Xã|Thị trấn)\s+/ui', '', $w['name']);
                                $list[] = [
                                    'code' => $w['code'],
                                    'name' => $w['name'],
                                    'short_name' => $short,
                                    'district_name' => $dName,
                                ];
                            }
                        }
                        return $list;
                    }
                } catch (\Throwable $e) {}
                return [];
            });

            if ($q !== '') {
                $qLower = mb_strtolower($q);
                $qAscii = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($q));

                $filtered = array_filter($wards, function ($w) use ($qLower, $qAscii) {
                    $nameLower = mb_strtolower($w['name']);
                    $nameAscii = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($w['name']));
                    $shortAscii = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($w['short_name']));
                    $distAscii = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($w['district_name'] ?? ''));

                    return str_contains($nameLower, $qLower) || 
                           str_contains($nameAscii, $qAscii) || 
                           str_contains($shortAscii, $qAscii) ||
                           str_contains($distAscii, $qAscii);
                });

                return response()->json(array_values(array_slice($filtered, 0, 50)));
            }

            return response()->json(array_slice($wards, 0, 60));
        }

        // 2. Nếu không truyền tỉnh nhưng có từ khóa $q (tìm kiếm toàn quốc)
        if ($q && mb_strlen($q) >= 2) {
            try {
                $response = Http::timeout(4)->get('https://provinces.open-api.vn/api/w/search/?q=' . urlencode($q));
                if ($response->successful()) {
                    $results = array_map(function ($w) {
                        return [
                            'name' => $w['name'],
                            'code' => $w['code'],
                            'short_name' => preg_replace('/^(Phường|Xã|Thị trấn)\s+/ui', '', $w['name']),
                        ];
                    }, array_slice($response->json(), 0, 40));
                    return response()->json($results);
                }
            } catch (\Throwable $e) {}
        }

        return response()->json([]);
    }

    /**
     * Fallback danh sách 63 tỉnh thành phòng trường hợp mạng ngoài gián đoạn
     */
    protected function fallbackProvinces(): array
    {
        return [
            ['code' => 1, 'name' => 'Thành phố Hà Nội', 'short_name' => 'Hà Nội'],
            ['code' => 79, 'name' => 'Thành phố Hồ Chí Minh', 'short_name' => 'Hồ Chí Minh'],
            ['code' => 31, 'name' => 'Thành phố Hải Phòng', 'short_name' => 'Hải Phòng'],
            ['code' => 48, 'name' => 'Thành phố Đà Nẵng', 'short_name' => 'Đà Nẵng'],
            ['code' => 92, 'name' => 'Thành phố Cần Thơ', 'short_name' => 'Cần Thơ'],
            ['code' => 42, 'name' => 'Tỉnh Hà Tĩnh', 'short_name' => 'Hà Tĩnh'],
            ['code' => 40, 'name' => 'Tỉnh Nghệ An', 'short_name' => 'Nghệ An'],
            ['code' => 38, 'name' => 'Tỉnh Thanh Hóa', 'short_name' => 'Thanh Hóa'],
            ['code' => 22, 'name' => 'Tỉnh Quảng Ninh', 'short_name' => 'Quảng Ninh'],
            ['code' => 27, 'name' => 'Tỉnh Bắc Ninh', 'short_name' => 'Bắc Ninh'],
            ['code' => 33, 'name' => 'Tỉnh Hưng Yên', 'short_name' => 'Hưng Yên'],
            ['code' => 37, 'name' => 'Tỉnh Ninh Bình', 'short_name' => 'Ninh Bình'],
            ['code' => 25, 'name' => 'Tỉnh Phú Thọ', 'short_name' => 'Phú Thọ'],
            ['code' => 19, 'name' => 'Tỉnh Thái Nguyên', 'short_name' => 'Thái Nguyên'],
            ['code' => 74, 'name' => 'Tỉnh Bình Dương', 'short_name' => 'Bình Dương'],
            ['code' => 75, 'name' => 'Tỉnh Đồng Nai', 'short_name' => 'Đồng Nai'],
        ];
    }
}
