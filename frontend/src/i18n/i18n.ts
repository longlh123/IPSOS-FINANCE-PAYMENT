import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

const resources = {
    en: {
        translation: {
            dashboard: {
                total_members  : "TOTAL MEMBERS",
                number_of_surveys: "NUMBER OF SURVEYS",
                response_rate: "RESPONSE RATE",
                completed_qualified: "COMPLETED / QUALIFIED",
                number_of_panelist: "NUMBER OF PANELIST",
                house_hold_income: "HOUSE HOLD INCOME",
                occupation: "OCCUPATION",
                main_bank: "MAIN BANK",
                products: "PRODUCTS",
                channels: "CHANNELS",
                age_group: "AGE GROUP",
                gender: "GENDER",
                gender_categories: {
                    "Nam": "Male",
                    "Nữ": "Female",
                    "Khác": "Other"
                },
                province: "Province",
                barchart: {
                    categories: {
                        "Từ 12.5 triệu đến dưới 25 triệu VND/tháng": "From 12.5 million to under 25 million VND/month",
                        "Từ 25 triệu đến dưới 35 triệu VND/tháng": "From 25 million to under 35 million VND/month",
                        "Từ 35 triệu đến dưới 45 triệu VND/tháng": "From 35 million to under 45 million VND/month",
                        "Từ 45 triệu đến dưới 60 triệu VND/tháng": "From 45 million to under 60 million VND/month",
                        "Từ 60 triệu đến dưới 80 triệu VND/tháng": "From 60 million to under 80 million VND/month",
                        "Từ 80 triệu đến dưới 110 triệu VND/tháng": "From 80 million to under 110 million VND/month",
                        "Chủ doanh nghiệp": "Business owner",
                        "Buôn bán nhỏ lẻ / Hộ kinh doanh cá thể": "Small retail business owner / Sole proprietorship",
                        "Làm việc tự do": "Freelancer",
                        "Nhân viên văn phòng": "Office employee",
                        "Khác (Công nhân/ Nhân viên không thuộc văn phòng/ chuyên gia/ ...)": "Others (Worker/ Non-office staff/ Specialist/ ...)",
                        "Từ 200 triệu đến dưới 1 tỷ": "From 200 million to under 1 billion VND",
                        "Dưới 200 triệu": "Under 200 million VND",
                        "Từ 1 tỷ trở lên": "From 1 billion VND and above",
                    }
                },
            }
        }
    },
    vi: {
        translation: {
            dashboard: {
                total_members  : "TỔNG SỐ THÀNH VIÊN",
                number_of_surveys: "SỐ LƯỢNG KHẢO SÁT",
                response_rate: "TỶ LỆ PHẢN HỒI",
                completed_qualified: "HOÀN THÀNH / ĐỦ ĐIỀU KIỆN",
                number_of_panelist: "DANH SÁCH ĐÁP VIÊN",
                house_hold_income: "THU NHẬP HỘ GIA ĐÌNH",
                occupation: "NGHỀ NGHIỆP",
                main_bank: "NGÂN HÀNG CHÍNH",
                products: "SẢN PHẨM",
                channels: "KÊNH GIAO DỊCH",
                age_group: "ĐỘ TUỔI",
                gender: "GIỚI TÍNH",
                gender_categories: {
                    "Nam": "Nam",
                    "Nữ": "Nữ",
                    "Khác": "Khác"
                },
                province: "Thành Phố",
                barchart: {
                    categories: {
                        "Từ 12.5 triệu đến dưới 25 triệu VND/tháng": "Từ 12.5 triệu đến dưới 25 triệu VND/tháng",
                        "Từ 25 triệu đến dưới 35 triệu VND/tháng": "Từ 25 triệu đến dưới 35 triệu VND/tháng",
                        "Từ 35 triệu đến dưới 45 triệu VND/tháng": "Từ 35 triệu đến dưới 45 triệu VND/tháng",
                        "Từ 45 triệu đến dưới 60 triệu VND/tháng": "Từ 45 triệu đến dưới 60 triệu VND/tháng",
                        "Từ 60 triệu đến dưới 80 triệu VND/tháng": "Từ 60 triệu đến dưới 80 triệu VND/tháng",
                        "Từ 80 triệu đến dưới 110 triệu VND/tháng": "Từ 80 triệu đến dưới 110 triệu VND/tháng",
                        "Chủ doanh nghiệp": "Chủ doanh nghiệp",
                        "Buôn bán nhỏ lẻ / Hộ kinh doanh cá thể": "Buôn bán nhỏ lẻ / Hộ kinh doanh cá thể",
                        "Làm việc tự do": "Làm việc tự do",
                        "Nhân viên văn phòng": "Nhân viên văn phòng",
                        "Khác (Công nhân/ Nhân viên không thuộc văn phòng/ chuyên gia/ ...)": "Khác (Công nhân/ Nhân viên không thuộc văn phòng/ chuyên gia/ ...)",
                        "Từ 200 triệu đến dưới 1 tỷ": "Từ 200 triệu đến dưới 1 tỷ",
                        "Dưới 200 triệu": "Dưới 200 triệu",
                        "Từ 1 tỷ trở lên": "Từ 1 tỷ trở lên",
                    },
                }
            }
        }
    }
};

i18n.use(initReactI18next).init({
  resources,
  lng: 'en', // default language
  fallbackLng: 'en', // fallback language if translation is missing
  interpolation: {
    escapeValue: false, // react already escapes values
  }
});

export default i18n;
