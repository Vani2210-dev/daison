<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\PriceTier;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ProductExcelImportService
{
    /**
     * Import products from an XLSX file.
     *
     * @param string $filePath
     * @return array
     * @throws \Exception
     */
    public function import(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File không tồn tại: {$filePath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception("Không thể mở file Excel định dạng .xlsx");
        }

        // 1. Đọc sharedStrings.xml
        $sharedStrings = [];
        if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xml = simplexml_load_string($zip->getFromIndex($index));
            foreach ($xml->si as $si) {
                $t = '';
                if (isset($si->t)) {
                    $t = (string)$si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $r) {
                        $t .= (string)$r->t;
                    }
                }
                $sharedStrings[] = $t;
            }
        }

        // 2. Tìm sheet đầu tiên
        $sheetXml = null;
        if (($index = $zip->locateName('xl/worksheets/sheet1.xml')) !== false) {
            $sheetXml = simplexml_load_string($zip->getFromIndex($index));
        }

        if (!$sheetXml) {
            $zip->close();
            throw new \Exception("Không tìm thấy dữ liệu Sheet1 trong file Excel");
        }

        // 3. Phân tích các dòng
        $rowsData = [];
        foreach ($sheetXml->sheetData->row as $row) {
            $rIndex = (int)$row['r'];
            $cols = [];
            foreach ($row->c as $c) {
                $cellRef = (string)$c['r'];
                $colLetter = preg_replace('/[0-9]/', '', $cellRef);
                $type = (string)$c['t'];
                $val = (string)$c->v;
                if ($type === 's') {
                    $val = $sharedStrings[(int)$val] ?? $val;
                }
                $cols[$colLetter] = trim($val);
            }
            $rowsData[$rIndex] = $cols;
        }
        $zip->close();

        // 4. Nhận diện các cột theo tiêu đề Header (Row 1 hoặc Row 2)
        $headerRow = 1;
        foreach ($rowsData as $rIndex => $cols) {
            foreach ($cols as $colLetter => $val) {
                $vLower = mb_strtolower($val, 'UTF-8');
                if (str_contains($vLower, 'mã sản phẩm') || str_contains($vLower, 'tên ngắn') || str_contains($vLower, 'nhóm sản phẩm')) {
                    $headerRow = $rIndex;
                    break 2;
                }
            }
        }

        $colMap = [
            'sku' => 'A',
            'name' => 'C',
            'tax_name' => 'D',
            'subcategory' => 'E',
            'brand' => 'F',
            'unit' => 'H',
            'conversion_unit' => 'I',
            'rate' => 'J',
            'cost1' => 'K',
            'origin1' => 'L',
            'bc1_1' => 'M',
            'bc2_1' => 'N',
            'bc3_1' => 'O',
            'retail1' => 'P',
            'cost2' => 'Q',
            'origin2' => 'R',
            'bc1_2' => 'S',
            'bc2_2' => 'T',
            'bc3_2' => 'U',
            'retail2' => 'V',
            'min_stock' => 'W',
            'max_stock' => 'X',
            'vat_rate' => 'AB',
        ];

        $headerCols = $rowsData[$headerRow] ?? [];
        foreach ($headerCols as $colLetter => $val) {
            $vLower = mb_strtolower(trim($val), 'UTF-8');
            if (str_contains($vLower, 'mã sản phẩm')) $colMap['sku'] = $colLetter;
            elseif ($vLower === 'tên ngắn' || str_contains($vLower, 'tên thương mại') || str_contains($vLower, 'tên sản phẩm')) $colMap['name'] = $colLetter;
            elseif (str_contains($vLower, 'hoá đơn') || str_contains($vLower, 'hóa đơn')) $colMap['tax_name'] = $colLetter;
            elseif (str_contains($vLower, 'nhóm sản phẩm') || str_contains($vLower, 'nhóm con') || str_contains($vLower, 'nhóm nhỏ')) $colMap['subcategory'] = $colLetter;
            elseif (str_contains($vLower, 'thương hiệu') || str_contains($vLower, 'nhãn hiệu')) $colMap['brand'] = $colLetter;
            elseif (str_contains($vLower, 'đvt tính') || str_contains($vLower, 'đơn vị tính') || str_contains($vLower, 'đvt cơ bản') || $vLower === 'đvt') $colMap['unit'] = $colLetter;
            elseif (str_contains($vLower, 'đv quy đổi') || str_contains($vLower, 'quy đổi') || str_contains($vLower, 'đvt lẻ') || str_contains($vLower, 'đv lẻ') || str_contains($vLower, 'đơn vị lẻ')) $colMap['conversion_unit'] = $colLetter;
            elseif (str_contains($vLower, 'quy cách') || str_contains($vLower, 'tỉ lệ quy đổi') || str_contains($vLower, 'tỷ lệ quy đổi')) $colMap['rate'] = $colLetter;
            elseif (str_contains($vLower, 'giá nhập') && (str_contains($vLower, 'cây') || !str_contains($vLower, 'mét'))) $colMap['cost1'] = $colLetter;
            elseif (str_contains($vLower, 'nguyên giá') && (str_contains($vLower, 'cây') || !str_contains($vLower, 'mét'))) $colMap['origin1'] = $colLetter;
            elseif (str_contains($vLower, 'bc1') && (str_contains($vLower, 'cây') || !str_contains($vLower, 'mét'))) $colMap['bc1_1'] = $colLetter;
            elseif (str_contains($vLower, 'bc2') && (str_contains($vLower, 'cây') || !str_contains($vLower, 'mét'))) $colMap['bc2_1'] = $colLetter;
            elseif (str_contains($vLower, 'bc3') && (str_contains($vLower, 'cây') || !str_contains($vLower, 'mét'))) $colMap['bc3_1'] = $colLetter;
            elseif (str_contains($vLower, 'khách lẻ') && (str_contains($vLower, 'cây') || !str_contains($vLower, 'mét'))) $colMap['retail1'] = $colLetter;
            elseif (str_contains($vLower, 'giá nhập') && str_contains($vLower, 'mét')) $colMap['cost2'] = $colLetter;
            elseif (str_contains($vLower, 'nguyên giá') && str_contains($vLower, 'mét')) $colMap['origin2'] = $colLetter;
            elseif (str_contains($vLower, 'bc1') && str_contains($vLower, 'mét')) $colMap['bc1_2'] = $colLetter;
            elseif (str_contains($vLower, 'bc2') && str_contains($vLower, 'mét')) $colMap['bc2_2'] = $colLetter;
            elseif (str_contains($vLower, 'bc3') && str_contains($vLower, 'mét')) $colMap['bc3_2'] = $colLetter;
            elseif (str_contains($vLower, 'khách lẻ') && str_contains($vLower, 'mét')) $colMap['retail2'] = $colLetter;
            elseif (str_contains($vLower, 'tồn tối thiểu') || str_contains($vLower, 'tối thiểu') || str_contains($vLower, 'min stock')) $colMap['min_stock'] = $colLetter;
            elseif (str_contains($vLower, 'tồn tối đa') || str_contains($vLower, 'tối đa') || str_contains($vLower, 'max stock')) $colMap['max_stock'] = $colLetter;
            elseif (str_contains($vLower, 'thuế suất') || str_contains($vLower, 'vat')) $colMap['vat_rate'] = $colLetter;
        }

        // Cache Master Data
        $brands = Brand::all()->keyBy(function ($item) {
            return mb_strtolower(trim($item->name), 'UTF-8');
        });

        $categories = Category::all()->keyBy(function ($item) {
            return mb_strtolower(trim($item->name), 'UTF-8');
        });

        $subcategories = Subcategory::with('category')->get()->keyBy(function ($item) {
            return mb_strtolower(trim($item->name), 'UTF-8');
        });

        $defaultCategory = Category::first() ?: Category::create([
            'code' => 'ONG',
            'name' => 'ỐNG',
            'description' => 'Ống nhựa các loại',
            'is_active' => true,
        ]);

        $createdCount = 0;
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($rowsData as $rIndex => $cols) {
                if ($rIndex <= $headerRow) {
                    continue;
                }

                $sku = trim($cols[$colMap['sku']] ?? '');
                $name = trim($cols[$colMap['name']] ?? '');
                $taxName = trim($cols[$colMap['tax_name']] ?? '');
                $subcatName = trim($cols[$colMap['subcategory']] ?? '');
                $brandName = trim($cols[$colMap['brand']] ?? '');

                // Bỏ qua dòng không có mã và không có tên
                if (!$sku && !$name) {
                    continue;
                }

                if (!$name && $sku) {
                    $name = $sku;
                }
                if (!$sku && $name) {
                    $sku = 'SP-' . strtoupper(substr(uniqid(), -6));
                }
                if (!$taxName) {
                    $taxName = $name;
                }

                // 1. Xử lý Brand
                $brandId = null;
                $brandKey = mb_strtolower($brandName, 'UTF-8');
                if ($brandName) {
                    if ($brands->has($brandKey)) {
                        $brandId = $brands->get($brandKey)->id;
                    } else {
                        $newBrand = Brand::create([
                            'code' => 'TH_' . strtoupper(substr(uniqid(), -4)),
                            'name' => $brandName,
                            'country' => 'Việt Nam',
                            'is_active' => true,
                        ]);
                        $brands->put($brandKey, $newBrand);
                        $brandId = $newBrand->id;
                    }
                }

                // 2. Xử lý Subcategory & Category
                $subcatId = null;
                $categoryId = $defaultCategory->id;
                $subcatKey = mb_strtolower($subcatName, 'UTF-8');

                if ($subcatName) {
                    if ($subcategories->has($subcatKey)) {
                        $sub = $subcategories->get($subcatKey);
                        $subcatId = $sub->id;
                        $categoryId = $sub->category_id ?: $categoryId;
                    } else {
                        $newSub = Subcategory::create([
                            'category_id' => $categoryId,
                            'brand_id' => $brandId,
                            'code' => 'NN_' . strtoupper(substr(uniqid(), -4)),
                            'name' => $subcatName,
                            'is_active' => true,
                        ]);
                        $subcategories->put($subcatKey, $newSub);
                        $subcatId = $newSub->id;
                    }
                }

                // 3. Xử lý Đơn vị tính & Quy đổi theo yêu cầu cụ thể của Người dùng:
                // "nếu đơn vị tính và đơn vị quy đổi giống nhau thì giá không phân biệt đơn vị. còn khi khác nhau thì mới phân biệt giá theo đơn vị tính"
                $unit1 = trim($cols[$colMap['unit']] ?? '') ?: 'Cây';
                $unit2 = trim($cols[$colMap['conversion_unit']] ?? '');
                $rawRate = (float)($cols[$colMap['rate']] ?? 1);
                $rate = $rawRate > 0 ? $rawRate : 1.0;

                $isSameUnit = empty($unit2) || (mb_strtolower($unit1, 'UTF-8') === mb_strtolower($unit2, 'UTF-8'));

                // Giá theo ĐVT 1 (Cây / ĐVT cơ bản)
                $cost1 = (float)($cols[$colMap['cost1']] ?? 0);
                $origin1 = (float)($cols[$colMap['origin1']] ?? 0);
                $bc1_1 = (float)($cols[$colMap['bc1_1']] ?? 0);
                $bc2_1 = (float)($cols[$colMap['bc2_1']] ?? 0);
                $bc3_1 = (float)($cols[$colMap['bc3_1']] ?? 0);
                $retail1 = (float)($cols[$colMap['retail1']] ?? 0);

                // Giá theo ĐVT 2 (Mét / ĐVT quy đổi)
                $cost2 = (float)($cols[$colMap['cost2']] ?? 0);
                $origin2 = (float)($cols[$colMap['origin2']] ?? 0);
                $bc1_2 = (float)($cols[$colMap['bc1_2']] ?? 0);
                $bc2_2 = (float)($cols[$colMap['bc2_2']] ?? 0);
                $bc3_2 = (float)($cols[$colMap['bc3_2']] ?? 0);
                $retail2 = (float)($cols[$colMap['retail2']] ?? 0);

                // Hàm bổ trợ quy đổi giá 2 chiều (nếu chỉ có giá ở 1 đơn vị thì tự động tính đơn vị còn lại theo hệ số quy cách)
                $resolvePricePair = function($val1, $val2, $convRate) {
                    $p1 = (float)$val1;
                    $p2 = (float)$val2;
                    if ($p1 > 0 && $p2 <= 0 && $convRate > 0) {
                        $p2 = $convRate > 1 ? (float)round($p1 / $convRate) : $p1;
                    } elseif ($p2 > 0 && $p1 <= 0 && $convRate > 0) {
                        $p1 = (float)round($p2 * $convRate);
                    }
                    return [(float)round($p1), (float)round($p2)];
                };

                if ($isSameUnit) {
                    // Đơn vị tính và đơn vị quy đổi GIỐNG NHAU: giá KHÔNG phân biệt đơn vị (giá quy đổi = giá chuẩn)
                    $unit = $unit1;
                    $conversionUnit = $unit1;
                    $conversionRate = 1.0;

                    [$costVal, $_] = $resolvePricePair($cost1, $cost2, 1.0);
                    [$originVal, $_] = $resolvePricePair($origin1, $origin2, 1.0);
                    [$bc1Val, $_] = $resolvePricePair($bc1_1, $bc1_2, 1.0);
                    [$bc2Val, $_] = $resolvePricePair($bc2_1, $bc2_2, 1.0);
                    [$bc3Val, $_] = $resolvePricePair($bc3_1, $bc3_2, 1.0);
                    [$retailVal, $_] = $resolvePricePair($retail1, $retail2, 1.0);

                    $conversionPrice = $costVal;
                    $spec = $unit;

                    $priceTiers = [
                        [
                            'name' => 'Giá Đại lý Cấp 1',
                            'price' => $bc1Val,
                            'conversion_price' => $bc1Val,
                        ],
                        [
                            'name' => 'Giá Đại lý Cấp 2',
                            'price' => $bc2Val,
                            'conversion_price' => $bc2Val,
                        ],
                        [
                            'name' => 'giá đại lý cấp 3',
                            'price' => $bc3Val,
                            'conversion_price' => $bc3Val,
                        ],
                        [
                            'name' => 'Giá Bán lẻ',
                            'price' => $retailVal,
                            'conversion_price' => $retailVal,
                        ],
                        [
                            'name' => 'Nguyên Giá',
                            'price' => $originVal,
                            'conversion_price' => $originVal,
                        ],
                    ];
                    $costPrice = $costVal;
                    $sellingPrice = $retailVal > 0 ? $retailVal : ($originVal > 0 ? $originVal : $costVal);
                } else {
                    // Đơn vị tính và đơn vị quy đổi KHÁC NHAU: PHÂN BIỆT GIÁ theo từng đơn vị tính (Cây vs Mét...)
                    $unit = $unit1;
                    $conversionUnit = $unit2;
                    $conversionRate = $rate;

                    [$cost1Val, $cost2Val] = $resolvePricePair($cost1, $cost2, $rate);
                    [$origin1Val, $origin2Val] = $resolvePricePair($origin1, $origin2, $rate);
                    [$bc1_1Val, $bc1_2Val] = $resolvePricePair($bc1_1, $bc1_2, $rate);
                    [$bc2_1Val, $bc2_2Val] = $resolvePricePair($bc2_1, $bc2_2, $rate);
                    [$bc3_1Val, $bc3_2Val] = $resolvePricePair($bc3_1, $bc3_2, $rate);
                    [$retail1Val, $retail2Val] = $resolvePricePair($retail1, $retail2, $rate);

                    $conversionPrice = $cost2Val;
                    $spec = "1 {$unit} = {$conversionRate} {$conversionUnit}";

                    $priceTiers = [
                        [
                            'name' => 'Giá Đại lý Cấp 1',
                            'price' => $bc1_1Val,
                            'conversion_price' => $bc1_2Val,
                        ],
                        [
                            'name' => 'Giá Đại lý Cấp 2',
                            'price' => $bc2_1Val,
                            'conversion_price' => $bc2_2Val,
                        ],
                        [
                            'name' => 'giá đại lý cấp 3',
                            'price' => $bc3_1Val,
                            'conversion_price' => $bc3_2Val,
                        ],
                        [
                            'name' => 'Giá Bán lẻ',
                            'price' => $retail1Val,
                            'conversion_price' => $retail2Val,
                        ],
                        [
                            'name' => 'Nguyên Giá',
                            'price' => $origin1Val,
                            'conversion_price' => $origin2Val,
                        ],
                    ];
                    $costPrice = $cost1Val;
                    $sellingPrice = $retail1Val > 0 ? $retail1Val : ($origin1Val > 0 ? $origin1Val : $cost1Val);
                }

                $vatRate = (isset($colMap['vat_rate']) && isset($cols[$colMap['vat_rate']]) && is_numeric($cols[$colMap['vat_rate']]))
                    ? (float)$cols[$colMap['vat_rate']]
                    : 8;

                $minStock = (isset($colMap['min_stock']) && isset($cols[$colMap['min_stock']]) && is_numeric($cols[$colMap['min_stock']]))
                    ? (float)$cols[$colMap['min_stock']]
                    : 0;

                $maxStock = (isset($colMap['max_stock']) && isset($cols[$colMap['max_stock']]) && is_numeric($cols[$colMap['max_stock']]))
                    ? (float)$cols[$colMap['max_stock']]
                    : null;

                $data = [
                    'sku' => $sku,
                    'name' => $name,
                    'tax_name' => $taxName,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                    'subcategory_id' => $subcatId,
                    'subcategory' => $subcatName,
                    'unit' => $unit,
                    'conversion_unit' => $conversionUnit,
                    'conversion_rate' => $conversionRate,
                    'conversion_price' => $conversionPrice,
                    'specification' => $spec,
                    'cost_price' => $costPrice,
                    'price' => $sellingPrice,
                    'latest_cost_price' => $costPrice,
                    'vat_rate' => $vatRate,
                    'min_stock_alert' => $minStock,
                    'max_stock_alert' => $maxStock,
                    'price_tiers' => $priceTiers,
                    'location' => 'Kho Tổng Mê Linh',
                    'is_active' => true,
                ];

                $product = Product::where('sku', $sku)->first();
                if ($product) {
                    $product->update($data);
                    $updatedCount++;
                } else {
                    Product::create($data);
                    $createdCount++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Import danh sách sản phẩm thành công!',
                'stats' => [
                    'created_count' => $createdCount,
                    'updated_count' => $updatedCount,
                    'total_products' => Product::count(),
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
