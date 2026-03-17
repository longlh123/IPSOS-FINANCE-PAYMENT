export interface UserDetail {
    id: number,
    name: string,
    email: string
};

export interface QuotationVersionData {
    id: number,
    version: number,
    status: string,
    quotation: any,
    created_user: UserDetail | null, 
    created_at: Date | null,
    approved_user: UserDetail | null,
    approved_at: Date | null
} 