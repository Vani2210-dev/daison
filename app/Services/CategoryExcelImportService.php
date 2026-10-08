<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class CategoryExcelImportService
{
    /**
     * Import categories, brands, category-brand relations, and subcategories from an XLSX file.
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

        // 4. Tìm vị trí cột Header
        $catCol = 'C';
        $brandCol = 'D';
        $subcatCol = 'E';
        $startRow = 3;

        foreach ($rowsData as $rIndex => $cols) {
            foreach ($cols as $colLetter => $val) {
                $vLower = mb_strtolower($val, 'UTF-8');
                if ($vLower === 'nhóm' || $vLower === 'ngành hàng' || $vLower === 'loại hàng') {
                    $catCol = $colLetter;
                    $startRow = $rIndex + 1;
                } elseif (str_contains($vLower, 'nhóm cha') || str_contains($vLower, 'thương hiệu') || str_contains($vLower, 'nhãn hiệu')) {
                    $brandCol = $colLetter;
                } elseif (str_contains($vLower, 'nhóm con') || str_contains($vLower, 'nhóm nhỏ')) {
                    $subcatCol = $colLetter;
                }
            }
            if ($startRow > 1 && $rIndex >= $startRow) {
                break;
            }
        }

        // 5. Quét dữ liệu và fill-down
        $importedCategories = 0;
        $importedBrands = 0;
        $importedRelations = 0;
        $importedSubcategories = 0;

        $categoryCache = [];
        $brandCache = [];

        $currentCatName = '';
        $currentBrandName = '';

        DB::beginTransaction();
        try {
            foreach ($rowsData as $rIndex => $cols) {
                if ($rIndex < $startRow) {
                    continue;
                }

                $cellCat = $cols[$catCol] ?? '';
                $cellBrand = $cols[$brandCol] ?? '';
                $cellSubcat = $cols[$subcatCol] ?? '';

                // Fill down
                if ($cellCat !== '') {
                    $currentCatName = $cellCat;
                }
                if ($cellBrand !== '') {
                    $currentBrandName = $cellBrand;
                }

                // Bỏ qua dòng trống cả 3 cột
                if (!$currentCatName && !$currentBrandName && !$cellSubcat) {
                    continue;
                }

                // A. Xử lý Category (Loại)
                $category = null;
                if ($currentCatName) {
                    if (!isset($categoryCache[$currentCatName])) {
                        $catCode = $this->generateUniqueCode($currentCatName, 'CAT', Category::class);
                        $category = Category::firstOrCreate(
                            ['name' => $currentCatName],
                            [
                                'code' => $catCode,
                                'description' => "Phân loại {$currentCatName} nhập từ Excel",
                                'is_active' => true,
                            ]
                        );
                        if ($category->wasRecentlyCreated) {
                            $importedCategories++;
                        }
                        $categoryCache[$currentCatName] = $category;
                    } else {
                        $category = $categoryCache[$currentCatName];
                    }
                }

                // B. Xử lý Brand (Thương hiệu)
                $brand = null;
                if ($currentBrandName) {
                    if (!isset($brandCache[$currentBrandName])) {
                        $brandCode = $this->generateUniqueCode($currentBrandName, 'TH', Brand::class);
                        $brand = Brand::firstOrCreate(
                            ['name' => $currentBrandName],
                            [
                                'code' => $brandCode,
                                'country' => 'Việt Nam',
                                'description' => "Thương hiệu {$currentBrandName} nhập từ Excel",
                                'is_active' => true,
                            ]
                        );
                        if ($brand->wasRecentlyCreated) {
                            $importedBrands++;
                        }
                        $brandCache[$currentBrandName] = $brand;
                    } else {
                        $brand = $brandCache[$currentBrandName];
                    }
                }

                // C. Liên kết Category <-> Brand (category_brands)
                if ($category && $brand) {
                    $exists = DB::table('category_brands')
                        ->where('category_id', $category->id)
                        ->where('brand_id', $brand->id)
                        ->exists();

                    if (!$exists) {
                        $category->brands()->syncWithoutDetaching([$brand->id]);
                        $importedRelations++;
                    }
                }

                // D. Xử lý Subcategory (Nhóm nhỏ)
                if ($cellSubcat && $category) {
                    $subcatCode = $this->generateUniqueCode($cellSubcat, 'NN', Subcategory::class);
                    $subcat = Subcategory::firstOrCreate(
                        [
                            'category_id' => $category->id,
                            'brand_id' => $brand ? $brand->id : null,
                            'name' => $cellSubcat,
                        ],
                        [
                            'code' => $subcatCode,
                            'description' => "Nhóm nhỏ {$cellSubcat} thuộc {$category->name}" . ($brand ? " - {$brand->name}" : ""),
                            'is_active' => true,
                        ]
                    );

                    if ($subcat->wasRecentlyCreated) {
                        $importedSubcategories++;
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Import dữ liệu Excel thành công!',
                'stats' => [
                    'categories_created' => $importedCategories,
                    'brands_created' => $importedBrands,
                    'relations_created' => $importedRelations,
                    'subcategories_created' => $importedSubcategories,
                    'total_categories' => Category::count(),
                    'total_brands' => Brand::count(),
                    'total_subcategories' => Subcategory::count(),
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Tạo mã duy nhất dạng ASCII từ tên tiếng Việt
     */
    protected function generateUniqueCode(string $name, string $prefix, string $modelClass): string
    {
        $baseCode = $this->toAsciiSlug($name);
        if (empty($baseCode)) {
            $baseCode = $prefix . '_' . strtoupper(substr(uniqid(), -4));
        }

        // Giới hạn độ dài mã
        if (strlen($baseCode) > 30) {
            $baseCode = substr($baseCode, 0, 30);
        }

        $code = $baseCode;
        $counter = 1;

        while ($modelClass::where('code', $code)->exists()) {
            $code = $baseCode . '_' . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Chuyển đổi chuỗi tiếng Việt có dấu thành chuỗi ASCII viết hoa không dấu
     */
    protected function toAsciiSlug(string $str): string
    {
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];
        foreach ($unicode as $nonUni => $uni) {
            $str = preg_replace("/($uni)/u", $nonUni, $str);
        }
        $str = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', trim($str)));
        return trim($str, '_');
    }
}
