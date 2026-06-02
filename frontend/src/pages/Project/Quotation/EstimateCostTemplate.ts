import { EstimateCostNode } from './EstimateCostTable';

const uid = () => crypto.randomUUID();

const item = (chi_phi: string, unit: string): EstimateCostNode => ({
    type: 'item',
    id: uid(),
    chi_phi,
    unit,
    quantity: '',
    price: '',
    amount: 0,
    actual_expense: '',
    note: '',
});

export const createDefaultEstimateCost = (): EstimateCostNode[] => [
    {
        type: 'group',
        id: uid(),
        label: 'INTERVIEWER',
        children: [
            item('Ho Chi Minh - Chi phí phiếu pilot', 'Phiếu'),
            item('Ho Chi Minh - Chi phí gửi xe Hiệp Nhất', 'Ngày'),
            item('Ho Chi Minh - Chi phí phiếu recruit - lên lần 1', 'Phiếu'),
            item('Ho Chi Minh - Chi phí phiếu phỏng vấn tại CLT - ĐV lên lần 1', 'Phiếu'),
            item('Ho Chi Minh - Thuê laptop (tính trên ngày CLT)', 'Ngày'),
        ],
    },
    {
        type: 'group',
        id: uid(),
        label: 'SUPERVISOR/ASSISTANT',
        children: [
            item('Ho Chi Minh - Chi phí quản lý recruit - On-field', 'Ngày'),
            item('Ho Chi Minh - Chi phí quản lý ngồi bàn - On-field', 'Ngày'),
            item('Ho Chi Minh - Chi phí Assitant - set up', 'Ngày'),
            item('Ho Chi Minh - Chi phí Assitant - on field', 'Ngày'),
        ],
    },
    {
        type: 'group',
        id: uid(),
        label: 'QC',
        children: [
            item('Ho Chi Minh - Chi phí QC tại nhà', 'Phiếu'),
            item('Thuê laptop', 'Phiếu'),
            item('Ho Chi Minh - Chi phí QC tại CLT', 'Phiếu'),
        ],
    },
    {
        type: 'group',
        id: uid(),
        label: 'DP',
        children: [
            item('Ho Chi Minh - Chi phí coding', 'Câu'),
            item('Ho Chi Minh - Chi phí Sup DP', 'Dự án'),
        ],
    },
    {
        type: 'group',
        id: uid(),
        label: 'INCENTIVE',
        children: [
            // Thêm các item của INCENTIVE vào đây
        ],
    },
];
