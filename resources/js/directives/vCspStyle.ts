import type { ObjectDirective } from 'vue';

/** Gets CSP nonce from `<meta name="csp-nonce">` tag. */
function getCspNonce(): string | null {
  if (typeof document === 'undefined') {
    return null;
  }
  const nonceMeta = document.querySelector('meta[name="csp-nonce"]');
  return nonceMeta ? nonceMeta.getAttribute('content') : null;
}

/** Escapes CSS selectors to prevent injection. Uses `CSS.escape` with manual fallback. */
function escapeCssSelector(id: string): string {
  if (typeof CSS !== 'undefined' && CSS.escape) {
    return CSS.escape(id);
  }

  // Fallback: Spec-compliant manual escaping
  return id
    .replace(/^(\d)/, '\\3$1 ') // Leading digit
    .replace(/^(-(\d|--))/, '\\$1')
    .replace(/([\0-\x1f\x7f-\x9f!"#$%&'()*+,./:;<=>?@[\\\]^`{|}~])/g, '\\$1');
}

/** Validates CSS property names (standard properties and CSS variables). */
function isValidCssProperty(prop: string): boolean {
  return /^--[\w-]+$/.test(prop) || /^[a-z][\w-]*$/i.test(prop);
}

/** Sanitizes CSS values to prevent XSS and CSS injection. */
function sanitizeCssValue(value: string | number): string {
  const strValue = String(value).trim();

  const dangerousPatterns = [
    /javascript:/gi,
    /vbscript:/gi,
    /data:text\/html/gi,
    /expression\s*\(/gi,
    /behavior\s*:/gi,
    /on\w+\s*=/gi,
    /<\/style/gi,
    /@import/gi,
  ];

  for (const pattern of dangerousPatterns) {
    if (pattern.test(strValue)) {
      console.warn(`[vCspStyle] Blocked malicious CSS value: ${strValue.substring(0, 50)}`);
      return '';
    }
  }

  // URL Validation
  const urlMatch = strValue.match(/url\s*\((.*?)\)/i);
  if (urlMatch) {
    let url = urlMatch[1].trim();
    if ((url.startsWith('"') && url.endsWith('"')) || (url.startsWith("'") && url.endsWith("'"))) {
      url = url.slice(1, -1).trim();
    }

    if (!/^(https?:\/\/|\/|\.\/|data:image\/)/i.test(url)) {
      console.warn(`[vCspStyle] Blocked unsafe URL: ${url}`);
      return '';
    }
  }

  // Structural Sanitization
  // We allow quotes (") and (') but remove structural chars that allow injection breakouts:
  // ; (end property), { (start block), } (end block)
  // We also remove NULL bytes and control chars.
  const sanitized = strValue
    .replace(/[\x00-\x1F\x7F-\x9F]/g, '') // Control chars
    .replace(/[{};]/g, '') // Structural chars only (Quotes are allowed now)
    .replace(/\/\*|\*\//g, ''); // Comment markers

  // Length limit
  if (sanitized.length > 1000) {
    return sanitized.substring(0, 1000);
  }

  return sanitized;
}

/** Manages CSP-compliant dynamic CSS injection via a single `<style>` tag. */
class StyleManager {
  private styleElement: HTMLStyleElement | null = null;
  private styles = new Map<string, string>();
  private updateScheduled = false;

  private ensureStyleElement(): HTMLStyleElement {
    if (!this.styleElement || !document.head.contains(this.styleElement)) {
      this.styleElement = document.createElement('style');
      this.styleElement.setAttribute('data-vue-csp-styles', '');
      this.styleElement.type = 'text/css';

      const nonce = getCspNonce();
      if (nonce) {
        this.styleElement.setAttribute('nonce', nonce);
      }

      document.head.appendChild(this.styleElement);
    }
    return this.styleElement;
  }

  setStyles(id: string, cssText: string): void {
    // Only schedule if actually changed
    if (this.styles.get(id) === cssText) {
      return;
    }

    this.styles.set(id, cssText);
    this.scheduleUpdate();
  }

  removeStyles(id: string): void {
    if (this.styles.delete(id)) {
      this.scheduleUpdate();
    }
  }

  private scheduleUpdate(): void {
    if (this.updateScheduled) {
      return;
    }

    this.updateScheduled = true;
    requestAnimationFrame(() => {
      this.updateStyleElement();
      this.updateScheduled = false;
    });
  }

  private updateStyleElement(): void {
    const styleEl = this.ensureStyleElement();

    const rules: string[] = [];
    this.styles.forEach((css, id) => {
      const escapedId = escapeCssSelector(id);
      rules.push(`#${escapedId} { ${css} }`);
    });

    styleEl.textContent = rules.join('\n');
  }

  clear(): void {
    this.styles.clear();
    if (this.styleElement?.parentNode) {
      this.styleElement.parentNode.removeChild(this.styleElement);
    }
    this.styleElement = null;
  }
}

const styleManager = new StyleManager();

let idCounter = 0;
const ID_PREFIX = 'v-csp-';

function isSameStyle(
  a: Record<string, unknown> | null | undefined,
  b: Record<string, unknown> | null | undefined,
): boolean {
  if (a === b) {
    return true;
  }
  if (!a || !b) {
    return false;
  }

  const keysA = Object.keys(a);
  const keysB = Object.keys(b);

  if (keysA.length !== keysB.length) {
    return false;
  }

  for (const key of keysA) {
    if (a[key] !== b[key]) {
      return false;
    }
  }
  return true;
}

/**
 * Vue directive for CSP-compliant dynamic styles.
 * @example
 * ```vue
 * <div v-csp-style="{ color: 'red', fontSize: '16px' }">Content</div>
 * ```
 */
export const vCspStyle: ObjectDirective<HTMLElement, Record<string, string | number>> = {
  mounted(el, binding) {
    applyStyles(el, binding.value);
  },

  updated(el, binding) {
    if (!isSameStyle(binding.value, binding.oldValue)) {
      applyStyles(el, binding.value);
    }
  },

  unmounted(el) {
    removeStyles(el);
  },

  getSSRProps() {
    return {};
  },
};

function applyStyles(el: HTMLElement, styles: Record<string, string | number> | undefined): void {
  if (typeof document === 'undefined') {
    return;
  }

  if (!styles || Object.keys(styles).length === 0) {
    removeStyles(el);
    return;
  }

  try {
    let id = el.id;
    if (!id) {
      id = `${ID_PREFIX}${++idCounter}`;
      el.id = id;
    }

    const cssDeclarations: string[] = [];

    for (const [prop, value] of Object.entries(styles)) {
      const cssProp = prop.startsWith('--') ? prop : prop.replace(/([A-Z])/g, '-$1').toLowerCase();

      if (!isValidCssProperty(cssProp)) {
        console.warn(`[vCspStyle] Invalid CSS property: ${cssProp}`);
        continue;
      }

      const sanitizedValue = sanitizeCssValue(value);
      if (!sanitizedValue && value !== '') {
        continue;
      }

      cssDeclarations.push(`${cssProp}: ${sanitizedValue}`);
    }

    const cssText = cssDeclarations.join('; ');

    if (cssText) {
      styleManager.setStyles(id, cssText);
    } else {
      styleManager.removeStyles(id);
    }
  } catch (error) {
    console.error('[vCspStyle] Failed to apply styles:', error);
  }
}

function removeStyles(el: HTMLElement): void {
  if (typeof document === 'undefined' || !el.id) {
    return;
  }
  styleManager.removeStyles(el.id);
}

export { styleManager };
export default vCspStyle;
