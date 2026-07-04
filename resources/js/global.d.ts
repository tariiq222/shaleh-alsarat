/// <reference types="vite/client" />

import { Config } from 'ziggy-js';

declare global {
    interface Window {
        Ziggy: Config;
    }
}

interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}