export interface UserDetail {
    id: number,
    name: string,
    email: string
};

export interface QuotationVersionData {
    id: number,
    version: number,
    status: string,
    quotation_data: any,
    created_user: UserDetail | null, 
    created_at: Date | null,
    approved_user: UserDetail | null,
    approved_at: Date | null
}

export interface OperationsData {
    id: number,
    status: string,
    operations_data: any,
    created_user: UserDetail | null, 
    created_at: Date | null,
    approved_user: UserDetail | null,
    approved_at: Date | null
}