export type * from './auth';

import type { Auth } from './auth';

export type SharedData = {
    name: string;
    auth: Auth;
    errors?: Record<string, string[]>;
    deferred?: Record<string, string[]>;
    [key: string]: unknown;
};
