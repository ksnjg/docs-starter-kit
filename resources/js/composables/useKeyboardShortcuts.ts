import { onMounted, onUnmounted } from 'vue';

export type ShortcutHandler = (e: KeyboardEvent) => void;

export interface ShortcutConfig {
  key: string;
  ctrl?: boolean;
  meta?: boolean;
  shift?: boolean;
  alt?: boolean;
  handler: ShortcutHandler;
  preventDefault?: boolean;
}

export function useKeyboardShortcuts(shortcuts: ShortcutConfig[]) {
  const handleKeyDown = (e: KeyboardEvent) => {
    for (const shortcut of shortcuts) {
      const ctrlOrMeta = shortcut.ctrl || shortcut.meta;
      const isCtrlOrMetaPressed = e.ctrlKey || e.metaKey;

      const keyMatches = e.key.toLowerCase() === shortcut.key.toLowerCase();
      const ctrlMatches = ctrlOrMeta ? isCtrlOrMetaPressed : !isCtrlOrMetaPressed;
      const shiftMatches = shortcut.shift ? e.shiftKey : !e.shiftKey;
      const altMatches = shortcut.alt ? e.altKey : !e.altKey;

      if (keyMatches && ctrlMatches && shiftMatches && altMatches) {
        if (shortcut.preventDefault !== false) {
          e.preventDefault();
        }
        shortcut.handler(e);
        return;
      }
    }
  };

  onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
  });

  onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
  });

  return { handleKeyDown };
}
