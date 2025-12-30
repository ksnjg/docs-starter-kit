import type { AppPageProps } from '@/types/index';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
  interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    [key: string]: string | boolean | undefined;
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv;
    readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
  }
}

declare module '@inertiajs/core' {
  interface PageProps extends InertiaPageProps, AppPageProps {}
}

declare module 'vue' {
  interface ComponentCustomProperties {
    $inertia: typeof Router;
    $page: Page;
    $headManager: ReturnType<typeof createHeadManager>;
  }
}

declare module 'sortablejs' {
  export interface SortableEvent {
    oldIndex?: number;
    newIndex?: number;
    item: HTMLElement;
    from: HTMLElement;
    to: HTMLElement;
  }

  export interface SortableOptions {
    group?: string | { name: string; pull?: boolean | string; put?: boolean | string[] };
    animation?: number;
    handle?: string;
    ghostClass?: string;
    chosenClass?: string;
    dragClass?: string;
    onEnd?: (evt: SortableEvent) => void;
    onStart?: (evt: SortableEvent) => void;
    onChange?: (evt: SortableEvent) => void;
  }

  export default class Sortable {
    static create(el: HTMLElement, options?: SortableOptions): Sortable;
    destroy(): void;
  }

  export { Sortable, SortableEvent, SortableOptions };
}

// Cloudflare Turnstile types
declare global {
  interface Window {
    turnstile?: {
      render: (container: Element, options: TurnstileOptions) => string;
      reset: (widgetId: string) => void;
      remove: (widgetId: string) => void;
    };
  }

  interface TurnstileOptions {
    sitekey: string;
    theme?: 'light' | 'dark' | 'auto';
    callback?: (token: string) => void;
    'error-callback'?: () => void;
    'expired-callback'?: () => void;
  }
}
