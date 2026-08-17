export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginatedResponse<T> = {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
};

export type RoleType = {
    id: number;
    name: string;
};

export type AdminType = {
    id: number;
    name: string;
    username: string;
    email: string;
    phone_number: string;
    is_active: boolean;

    role_id: number;
    role: RoleType;

    outlet_id: number;
    outlet: OutletType;
};

export type OutletType = {
    id: number;
    image_url: string;
    image_public_id: string;
    name: string;
    address: string;
    phone_number: string;
};

export type CustomerType = {
    id: number;
    name: string;
    phone_number: string;
    created_at: string; // For 'Bergabung pada' on DataTable Column
};

export type ServiceType = {
    id: number;
    name: string;
    description: string | null;
    price: number;
    estimated_days: number;
};

export type ExpenseCategoryType = {
    id: number;
    category_name: string;
};

export type ExpenseType = {
    id: number;
    expense_date: string;
    description?: string | null;
    amount: number;
    image_url?: string | null;
    image_public_id?: string | null;

    expense_category_id: string;
    expense_category: ExpenseCategoryType;

    user_id: number;
    user: AdminType;

    outlet_id: number;
    outlet: OutletType;
};

export enum PaymentStatusEnum {
    Unpaid = 'unpaid',
    Paid = 'paid',
}

export enum PaymentMethodEnum {
    Cash = 'cash',
    Transfer = 'transfer',
}

export enum StatusTypeEnum {
    TransactionStatus = 'transaction_status',
    ShoeStatus = 'shoes_status',
}

export type StatusType = {
    id: number;
    name: string;
    type: StatusTypeEnum;
    step: number;
};

export type TransactionType = {
    id: string;
    invoice_number: string;
    total_price: string;
    payment_status: PaymentStatusEnum;
    payment_method: PaymentMethodEnum;
    notes: string;
    created_at: string;
    overdue_date: string;

    status_id: number;
    status: StatusType;

    customer_id: number;
    customer: CustomerType;

    outlet_id: number;
    outlet: OutletType;
    transaction_shoes?: TransactionShoesType[];
};

export type TransactionShoesType = {
    id: string;

    transaction_id: number;
    transaction: TransactionType;

    shoe_brand: string;
    shoe_color: string;
    shoe_size: string;

    status_id: number;
    status: StatusType;

    shoe_condition: string;
    shoe_services?: ShoeServices[]; // Rantai relasi layanan
};

export type ShoeServices = {
    id: string;

    transaction_shoe_id: number;
    transaction_shoe: TransactionShoesType;

    service_id: number;
    service: ServiceType;

    subtotal_price: string;
};
