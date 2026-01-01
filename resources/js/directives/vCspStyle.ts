import type { ObjectDirective } from 'vue';

/**
 * Retrieves the CSP nonce from the meta tag.
 * ```html
 * <meta name="csp-nonce" content="your-nonce-value">
 * ```
 * @returns {string | null} The CSP nonce value or null if not found
 * @since 1.0.0
 */
function getCspNonce(): string | null {
  if (typeof document === 'undefined') {
    return null;
  }
  const nonceMeta = document.querySelector('meta[name="csp-nonce"]');
  return nonceMeta ? nonceMeta.getAttribute('content') : null;
}

/**
 * Escapes CSS selectors to prevent injection attacks.
 * @param {string} id - The CSS selector or ID to escape
 * @returns {string} The escaped CSS selector
 * @since 1.0.0
 * @example
 * ```typescript
 * escapeCssSelector('123'); // '\3 1 '
 * escapeCssSelector('element.class'); // 'element\.class'
 * ```
 */
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

/**
 * Validates CSS property names.
 * @param {string} prop - The CSS property name to validate
 * @returns {boolean} True if valid, false otherwise
 * @since 1.0.0
 * @example
 * ```typescript
 * isValidCssProperty('color'); // true
 * isValidCssProperty('--main-color'); // true
 * isValidCssProperty('invalid-prop!'); // false
 * ```
 */
function isValidCssProperty(prop: string): boolean {
  return /^--[\w-]+$/.test(prop) || /^[a-z][\w-]*$/i.test(prop);
}

/**
 * Sanitizes CSS values to prevent XSS attacks and CSS injection.
 * @param {string | number} value - The CSS value to sanitize
 * @returns {string} The sanitized CSS value
 * @since 1.0.0
 * @example
 * ```typescript
 * sanitizeCssValue('red'); // 'red'
 * sanitizeCssValue('javascript:alert(1)'); // '' (blocked)
 * sanitizeCssValue('12px'); // '12px'
 * ```
 */
function sanitizeCssValue(value: string | number): string {
  const strValue = String(value).trim();

  // 1. Block known dangerous payloads immediately
  const dangerousPatterns = [
    /javascript:/gi,
    /vbscript:/gi,
    /data:text\/html/gi,
    /expression\s*\(/gi,
    /behavior\s*:/gi,
    /on\w+\s*=/gi, // Event handlers
    /<\/style/gi, // HTML breakout
    /@import/gi, // External resource loading
  ];

  for (const pattern of dangerousPatterns) {
    if (pattern.test(strValue)) {
      console.warn(`[vCspStyle] Blocked malicious CSS value: ${strValue.substring(0, 50)}`);
      return '';
    }
  }

  // Deep URL Validation
  // Matches url('...') or url(...)
  const urlMatch = strValue.match(/url\s*\((.*?)\)/i);
  if (urlMatch) {
    // Extract inner content and strip quotes for protocol checking
    let url = urlMatch[1].trim();
    if ((url.startsWith('"') && url.endsWith('"')) || (url.startsWith("'") && url.endsWith("'"))) {
      url = url.slice(1, -1).trim();
    }

    // Only allow safe schemas (http, https, relative, data:image)
    if (!/^(https?:\/\/|\/|\.\/|data:image\/)/i.test(url)) {
      console.warn(`[vCspStyle] Blocked unsafe URL: ${url}`);
      return '';
    }
  }

  // 3. Structural Sanitization
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

/**
 * Centralized style manager for CSP-compliant dynamic CSS injection.
 * Batches DOM updates to a single `<style>` tag.
 * @class StyleManager
 * @since 1.0.0
 */
class StyleManager {
  private styleElement: HTMLStyleElement | null = null;
  private styles = new Map<string, string>();
  private updateScheduled = false;

  /**
   * Ensures a style element exists with proper CSP nonce.
   * @private
   * @returns {HTMLStyleElement} The style element
   */
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

  /**
   * Sets or updates CSS styles for an element ID.
   * @param {string} id - The unique element identifier
   * @param {string} cssText - The CSS declarations to apply
   * @returns {void}
   */
  setStyles(id: string, cssText: string): void {
    // Only schedule if actually changed
    if (this.styles.get(id) === cssText) {
      return;
    }

    this.styles.set(id, cssText);
    this.scheduleUpdate();
  }

  /**
   * Removes CSS styles for an element ID.
   * @param {string} id - The unique element identifier
   * @returns {void}
   */
  removeStyles(id: string): void {
    if (this.styles.delete(id)) {
      this.scheduleUpdate();
    }
  }

  /**
   * Schedules style element update using requestAnimationFrame.
   * @private
   * @returns {void}
   */
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

  /**
   * Updates the style element with all current CSS rules.
   * @private
   * @returns {void}
   */
  private updateStyleElement(): void {
    const styleEl = this.ensureStyleElement();

    const rules: string[] = [];
    this.styles.forEach((css, id) => {
      const escapedId = escapeCssSelector(id);
      rules.push(`#${escapedId} { ${css} }`);
    });

    styleEl.textContent = rules.join('\n');
  }

  /**
   * Clears all stored styles and removes the style element.
   * @returns {void}
   */
  clear(): void {
    this.styles.clear();
    if (this.styleElement?.parentNode) {
      this.styleElement.parentNode.removeChild(this.styleElement);
    }
    this.styleElement = null;
  }
}

/**
 * Global style manager instance for CSP-compliant dynamic styles.
 * @type {StyleManager}
 * @since 1.0.0
 * @example
 * ```typescript
 * import { styleManager } from '@/directives/vCspStyle';
 * styleManager.setStyles('my-element', 'color: red; font-size: 16px;');
 * styleManager.clear();
 * ```
 */
const styleManager = new StyleManager();

// Unique ID Management
let idCounter = 0;
const ID_PREFIX = 'v-csp-';

/**
 * Performs shallow comparison of two style objects.
 * @param {Record<string, unknown> | null | undefined} a - First style object
 * @param {Record<string, unknown> | null | undefined} b - Second style object
 * @returns {boolean} True if objects are shallowly equal
 * @since 1.0.0
 * @example
 * ```typescript
 * isSameStyle({color: 'red'}, {color: 'red'}); // true
 * isSameStyle({color: 'red'}, {color: 'blue'}); // false
 * isSameStyle(null, null); // true
 * ```
 */
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
 * <div v-csp-style="{ color: 'red', fontSize: '16px' }">
 *   Styled content
 * </div>
 * ```
 * @const {ObjectDirective<HTMLElement, Record<string, string | number>>} vCspStyle
 * @since 1.0.0
 */
export const vCspStyle: ObjectDirective<HTMLElement, Record<string, string | number>> = {
  /**
   * Called when the directive is mounted.
   * @param {HTMLElement} el - The element
   * @param {import('vue').DirectiveBinding<Record<string, string | number>>} binding - The directive binding
   * @returns {void}
   */
  mounted(el, binding) {
    applyStyles(el, binding.value);
  },

  /**
   * Called when the binding value changes.
   * @param {HTMLElement} el - The element
   * @param {import('vue').DirectiveBinding<Record<string, string | number>>} binding - The directive binding
   * @returns {void}
   */
  updated(el, binding) {
    if (!isSameStyle(binding.value, binding.oldValue)) {
      applyStyles(el, binding.value);
    }
  },

  /**
   * Called when the element is unmounted.
   * @param {HTMLElement} el - The element
   * @returns {void}
   */
  unmounted(el) {
    removeStyles(el);
  },
};

/**
 * Applies CSS styles to an element using the CSP-compliant style manager.
 * @param {HTMLElement} el - The element to apply styles to
 * @param {Record<string, string | number> | undefined} styles - CSS properties and values
 * @returns {void}
 * @since 1.0.0
 */
function applyStyles(el: HTMLElement, styles: Record<string, string | number> | undefined): void {
  if (typeof document === 'undefined') {
    return;
  }

  // Cleanup if binding is empty
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
        // Allow empty string resets if needed
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

/**
 * Removes CSS styles from an element.
 * @param {HTMLElement} el - The element to remove styles from
 * @returns {void}
 * @since 1.0.0
 */
function removeStyles(el: HTMLElement): void {
  if (typeof document === 'undefined' || !el.id) {
    return;
  }
  styleManager.removeStyles(el.id);
}

export { styleManager };
/**
 * Default export of the vCspStyle directive.
 * @type {ObjectDirective<HTMLElement, Record<string, string | number>>}
 * @since 1.0.0
 * @example
 * ```typescript
 * import vCspStyle from '@/directives/vCspStyle';
 * app.directive('csp-style', vCspStyle);
 * ```
 */
export default vCspStyle;
