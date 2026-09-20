export type UserRole = 'member' | 'moderator' | 'admin';

export type User = {
    id: string;
    name: string;
    email: string;
    pending_email: string | null;
    role: UserRole;
    locale: 'en' | 'bn';
    is_staff: boolean;
    is_admin: boolean;
    photo_url: string;
};

export type Auth = {
    user: User | null;
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
