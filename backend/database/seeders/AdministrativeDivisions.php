<?php

namespace Database\Seeders;

use App\Models;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Province;

class AdministrativeDivisions extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            ['name' => 'Tây Bắc Bộ', 'eng_name' => 'Northwest'],
            ['name' => 'Đông Nam Bộ', 'eng_name' => 'Southeast'],
            ['name' => 'Đồng Bằng Sông Hồng', 'eng_name' => 'Red River Delta'],
            ['name' => 'Bắc Trung Bộ', 'eng_name' => 'North Central'],
            ['name' => 'Duyên Hải Nam Trung Bộ', 'eng_name' => 'South Central Coast'],
            ['name' => 'Tây Nguyên', 'eng_name' => 'Central Highlands'],
            ['name' => 'Đông Bắc Bộ', 'eng_name' => 'Northeast'],
            ['name' => 'Đồng Bằng Sông Cửu Long', 'eng_name' => 'Mekong River Delta'],
        ];

        foreach($regions as $region){
            Region::create($region);
        }

        $provinces = [
            ['name' => 'Hồ Chí Minh', 'abbreviation' => '01_HCM', 'old_area_code' => 8, 'area_code' => 28, 'region_id' => 2],
            ['name' => 'Hà Nội', 'abbreviation' => '02_HN', 'old_area_code' => 4, 'area_code' => 24, 'region_id' => 3],
            ['name' => 'Đà Nẵng', 'abbreviation' => '03_DN', 'old_area_code' => 511, 'area_code' => 236, 'region_id' => 5],
            ['name' => 'Cần Thơ', 'abbreviation' => '04_CT', 'old_area_code' => 710, 'area_code' => 292, 'region_id' => 8],
            ['name' => 'Khánh Hòa', 'abbreviation' => '05_KH', 'old_area_code' => 58, 'area_code' => 258, 'region_id' => 5],
            ['name' => 'Đồng Nai', 'abbreviation' => '06_DN', 'old_area_code' => 61, 'area_code' => 251, 'region_id' => 2],
            ['name' => 'Hải Phòng', 'abbreviation' => '07_HP', 'old_area_code' => 31, 'area_code' => 225, 'region_id' => 3],
            ['name' => 'Tiền Giang', 'abbreviation' => '08_TG', 'old_area_code' => 73, 'area_code' => 273, 'region_id' => 8],
            ['name' => 'An Giang', 'abbreviation' => '09_AG', 'old_area_code' => 76, 'area_code' => 296, 'region_id' => 8],
            ['name' => 'Bắc Giang', 'abbreviation' => '10_BG', 'old_area_code' => 240, 'area_code' => 204, 'region_id' => 7],
            ['name' => 'Nam Định', 'abbreviation' => '11_ND', 'old_area_code' => 350, 'area_code' => 228, 'region_id' => 3],
            ['name' => 'Thanh Hóa', 'abbreviation' => '12_TH', 'old_area_code' => 37, 'area_code' => 237, 'region_id' => 4],
            ['name' => 'Nghệ An', 'abbreviation' => '13_NA', 'old_area_code' => 38, 'area_code' => 238, 'region_id' => 4],
            ['name' => 'Thừa Thiên - Huế', 'abbreviation' => '14_TTH', 'old_area_code' => 54, 'area_code' => 234, 'region_id' => 4],
            ['name' => 'Thái Nguyên', 'abbreviation' => '15_TN', 'old_area_code' => 280, 'area_code' => 208, 'region_id' => 7],
            ['name' => 'Vĩnh Phúc', 'abbreviation' => '16_VP', 'old_area_code' => 211, 'area_code' => 211, 'region_id' => 3],
            ['name' => 'Bình Dương', 'abbreviation' => '17_BD', 'old_area_code' => 650, 'area_code' => 274, 'region_id' => 2],
            ['name' => 'Quảng Ninh', 'abbreviation' => '18_QN', 'old_area_code' => 33, 'area_code' => 203, 'region_id' => 7],
            ['name' => 'Sơn La', 'abbreviation' => '19_SL', 'old_area_code' => 22, 'area_code' => 212, 'region_id' => 1],
            ['name' => 'Bình Định', 'abbreviation' => '20_BD', 'old_area_code' => 56, 'area_code' => 256, 'region_id' => 5],
            ['name' => 'Kiên Giang', 'abbreviation' => '21_KG', 'old_area_code' => 77, 'area_code' => 297, 'region_id' => 8],
            ['name' => 'Vĩnh Long', 'abbreviation' => '22_VL', 'old_area_code' => 70, 'area_code' => 270, 'region_id' => 8],
            ['name' => 'Đắk Lắk', 'abbreviation' => '23_DL', 'old_area_code' => 500, 'area_code' => 262, 'region_id' => 6],
            ['name' => 'Long An', 'abbreviation' => '24_LA', 'old_area_code' => 72, 'area_code' => 272, 'region_id' => 8],
            ['name' => 'Lâm Đồng', 'abbreviation' => '25_LD', 'old_area_code' => 63, 'area_code' => 263, 'region_id' => 6],
            ['name' => 'Đồng Tháp', 'abbreviation' => '26_DT', 'old_area_code' => 67, 'area_code' => 277, 'region_id' => 8],
            ['name' => 'Bình Thuận', 'abbreviation' => '27_BT', 'old_area_code' => 62, 'area_code' => 252, 'region_id' => 5],
            ['name' => 'Cà Mau', 'abbreviation' => '28_CM', 'old_area_code' => 780, 'area_code' => 290, 'region_id' => 8],
            ['name' => 'Phú Thọ', 'abbreviation' => '29_PT', 'old_area_code' => 210, 'area_code' => 210, 'region_id' => 7],
            ['name' => 'Tây Ninh', 'abbreviation' => '30_TN', 'old_area_code' => 66, 'area_code' => 276, 'region_id' => 2],
            ['name' => 'Hưng Yên', 'abbreviation' => '31_HY', 'old_area_code' => 321, 'area_code' => 221, 'region_id' => 3],
            ['name' => 'Thái Bình', 'abbreviation' => '32_TB', 'old_area_code' => 36, 'area_code' => 227, 'region_id' => 3],
            ['name' => 'Quảng Nam', 'abbreviation' => '33_QN', 'old_area_code' => 510, 'area_code' => 235, 'region_id' => 5],
            ['name' => 'Bến Tre', 'abbreviation' => '34_BT', 'old_area_code' => 75, 'area_code' => 275, 'region_id' => 8],
            ['name' => 'Quảng Ngãi', 'abbreviation' => '35_QN', 'old_area_code' => 55, 'area_code' => 255, 'region_id' => 5],
            ['name' => 'Lạng Sơn', 'abbreviation' => '36_LS', 'old_area_code' => 25, 'area_code' => 205, 'region_id' => 7],
            ['name' => 'Hải Dương', 'abbreviation' => '37_HD', 'old_area_code' => 320, 'area_code' => 220, 'region_id' => 3],
            ['name' => 'Ninh Bình', 'abbreviation' => '38_NB', 'old_area_code' => 30, 'area_code' => 229, 'region_id' => 3],
            ['name' => 'Bắc Kạn', 'abbreviation' => '39_BK', 'old_area_code' => 281, 'area_code' => 209, 'region_id' => 7],
            ['name' => 'Cao Bằng', 'abbreviation' => '40_CB', 'old_area_code' => 26, 'area_code' => 206, 'region_id' => 7],
            ['name' => 'Hà Giang', 'abbreviation' => '41_HG', 'old_area_code' => 219, 'area_code' => 219, 'region_id' => 7],
            ['name' => 'Tuyên Quang', 'abbreviation' => '42_TQ', 'old_area_code' => 27, 'area_code' => 207, 'region_id' => 7],
            ['name' => 'Điện Biên', 'abbreviation' => '43_DB', 'old_area_code' => 230, 'area_code' => 215, 'region_id' => 1],
            ['name' => 'Hòa Bình', 'abbreviation' => '44_HB', 'old_area_code' => 218, 'area_code' => 218, 'region_id' => 1],
            ['name' => 'Lai Châu', 'abbreviation' => '45_LC', 'old_area_code' => 231, 'area_code' => 213, 'region_id' => 1],
            ['name' => 'Lào Cai', 'abbreviation' => '46_LC', 'old_area_code' => 20, 'area_code' => 214, 'region_id' => 1],
            ['name' => 'Yên Bái', 'abbreviation' => '47_YB', 'old_area_code' => 29, 'area_code' => 216, 'region_id' => 1],
            ['name' => 'Bắc Ninh', 'abbreviation' => '48_BN', 'old_area_code' => 241, 'area_code' => 222, 'region_id' => 3],
            ['name' => 'Hà Nam', 'abbreviation' => '49_HN', 'old_area_code' => 351, 'area_code' => 226, 'region_id' => 3],
            ['name' => 'Ninh Thuận', 'abbreviation' => '50_NT', 'old_area_code' => 68, 'area_code' => 259, 'region_id' => 5],
            ['name' => 'Phú Yên', 'abbreviation' => '51_PY', 'old_area_code' => 57, 'area_code' => 257, 'region_id' => 5],
            ['name' => 'Hà Tĩnh', 'abbreviation' => '52_HT', 'old_area_code' => 39, 'area_code' => 239, 'region_id' => 4],
            ['name' => 'Quảng Bình', 'abbreviation' => '53_QB', 'old_area_code' => 52, 'area_code' => 232, 'region_id' => 4],
            ['name' => 'Quảng Trị', 'abbreviation' => '54_QT', 'old_area_code' => 53, 'area_code' => 233, 'region_id' => 4],
            ['name' => 'Đắk Nông', 'abbreviation' => '55_DN', 'old_area_code' => 501, 'area_code' => 261, 'region_id' => 6],
            ['name' => 'Gia Lai', 'abbreviation' => '56_GL', 'old_area_code' => 59, 'area_code' => 269, 'region_id' => 6],
            ['name' => 'Kon Tum', 'abbreviation' => '57_KT', 'old_area_code' => 60, 'area_code' => 260, 'region_id' => 6],
            ['name' => 'Bà Rịa - Vũng Tàu', 'abbreviation' => '58_BR_VT', 'old_area_code' => 64, 'area_code' => 254, 'region_id' => 2],
            ['name' => 'Bình Phước', 'abbreviation' => '59_BP', 'old_area_code' => 651, 'area_code' => 271, 'region_id' => 2],
            ['name' => 'Bạc Liêu', 'abbreviation' => '60_BL', 'old_area_code' => 781, 'area_code' => 291, 'region_id' => 8],
            ['name' => 'Hậu Giang', 'abbreviation' => '61_HG', 'old_area_code' => 711, 'area_code' => 293, 'region_id' => 8],
            ['name' => 'Sóc Trăng', 'abbreviation' => '62_ST', 'old_area_code' => 79, 'area_code' => 299, 'region_id' => 8],
            ['name' => 'Trà Vinh', 'abbreviation' => '63_TV', 'old_area_code' => 74, 'area_code' => 294, 'region_id' => 8],
        ];

        foreach($provinces as $province){
            Province::create($province);
        }
    }
}
