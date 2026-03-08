export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role:
        | "admin"
        | "doctor"
        | "secretary"
        | "pharmacy"
        | "operating_room_manager"
        | "nurse"
        | "emergency"
        | "accountant"
        | "maintenance"
        | "paramedic";
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
