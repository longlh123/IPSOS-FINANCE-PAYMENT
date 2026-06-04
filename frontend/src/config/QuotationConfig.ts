export interface UserDetail {
    id: number,
    name: string,
    email: string
};

export interface FeedbackThreadEntry {
    type: 'feedback' | 'response';
    content: string | null;
    user_id: number;
    user_name: string;
    created_at: string;
    // only present when type === 'response'
    status?: 'resolved' | 'rejected';
}

export type FeedbackThread = FeedbackThreadEntry[];
export type FeedbacksMap = Record<string, FeedbackThread>;

export interface QuotationVersionData {
    id: number,
    version: number,
    status: string,
    quotation_data: any,
    feedbacks: FeedbacksMap,
    created_user: UserDetail | null,
    created_at: Date | null,
    fm_confirmed_user: UserDetail | null,
    fm_confirmed_at: Date | null,
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