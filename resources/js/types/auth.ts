export type User = {
    id: number;
    username: string;
    name: string;
    email: string;
    password?: string;
    full_name: string;
    org_unit_id?: number;
    department?: string;
    position?: string;
    phone?: string;
    is_active: boolean;
    last_login_at?: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at?: string;
    org_unit?: {
        id: number;
        name: string;
        // Add other org unit properties as needed
    };
    roles?: Array<{
        id: number;
        name: string;
        // Add other role properties as needed
    }>;
    [key: string]: unknown; // This allows for additional properties...
};

export type Auth = {
    user: User;
};
