/**
 * HTML to Markdown converter for TipTap editor
 * Converts TipTap HTML output to Markdown format for storage
 */

interface ConversionRule {
  selector: string;
  replacement: (element: Element, content: string) => string;
}

const rules: ConversionRule[] = [
  // Headers
  {
    selector: 'h1',
    replacement: (_, content) => `# ${content}\n\n`,
  },
  {
    selector: 'h2',
    replacement: (_, content) => `## ${content}\n\n`,
  },
  {
    selector: 'h3',
    replacement: (_, content) => `### ${content}\n\n`,
  },
  {
    selector: 'h4',
    replacement: (_, content) => `#### ${content}\n\n`,
  },
  {
    selector: 'h5',
    replacement: (_, content) => `##### ${content}\n\n`,
  },
  {
    selector: 'h6',
    replacement: (_, content) => `###### ${content}\n\n`,
  },
  // Paragraphs
  {
    selector: 'p',
    replacement: (_, content) => `${content}\n\n`,
  },
  // Bold
  {
    selector: 'strong, b',
    replacement: (_, content) => `**${content}**`,
  },
  // Italic
  {
    selector: 'em, i',
    replacement: (_, content) => `*${content}*`,
  },
  // Underline (no markdown equivalent, use HTML)
  {
    selector: 'u',
    replacement: (_, content) => `<u>${content}</u>`,
  },
  // Strikethrough
  {
    selector: 's, strike, del',
    replacement: (_, content) => `~~${content}~~`,
  },
  // Code inline
  {
    selector: 'code',
    replacement: (element, content) => {
      if (element.parentElement?.tagName.toLowerCase() === 'pre') {
        return content;
      }
      return `\`${content}\``;
    },
  },
  // Code block
  {
    selector: 'pre',
    replacement: (element) => {
      const code = element.querySelector('code');
      const content = code?.textContent || element.textContent || '';
      const lang = code?.className?.match(/language-(\w+)/)?.[1] || '';
      return `\`\`\`${lang}\n${content}\n\`\`\`\n\n`;
    },
  },
  // Links
  {
    selector: 'a',
    replacement: (element, content) => {
      const href = element.getAttribute('href') || '';
      const title = element.getAttribute('title');
      if (title) {
        return `[${content}](${href} "${title}")`;
      }
      return `[${content}](${href})`;
    },
  },
  // Images
  {
    selector: 'img',
    replacement: (element) => {
      const src = element.getAttribute('src') || '';
      const alt = element.getAttribute('alt') || '';
      const title = element.getAttribute('title');
      if (title) {
        return `![${alt}](${src} "${title}")`;
      }
      return `![${alt}](${src})`;
    },
  },
  // Unordered lists
  {
    selector: 'ul',
    replacement: (_, content) => `${content}\n`,
  },
  // Ordered lists
  {
    selector: 'ol',
    replacement: (_, content) => `${content}\n`,
  },
  // List items
  {
    selector: 'li',
    replacement: (element, content) => {
      const parent = element.parentElement;
      const isOrdered = parent?.tagName.toLowerCase() === 'ol';
      if (isOrdered) {
        const index = Array.from(parent?.children || []).indexOf(element) + 1;
        return `${index}. ${content.trim()}\n`;
      }
      return `- ${content.trim()}\n`;
    },
  },
  // Blockquote
  {
    selector: 'blockquote',
    replacement: (_, content) => {
      const lines = content.trim().split('\n');
      return lines.map((line) => `> ${line}`).join('\n') + '\n\n';
    },
  },
  // Horizontal rule
  {
    selector: 'hr',
    replacement: () => `---\n\n`,
  },
  // Line break
  {
    selector: 'br',
    replacement: () => `\n`,
  },
];

const processNode = (node: Node): string => {
  if (node.nodeType === Node.TEXT_NODE) {
    return node.textContent || '';
  }

  if (node.nodeType !== Node.ELEMENT_NODE) {
    return '';
  }

  const element = node as Element;
  const tagName = element.tagName.toLowerCase();

  // Process children first
  let childContent = '';
  element.childNodes.forEach((child) => {
    childContent += processNode(child);
  });

  // Find matching rule
  for (const rule of rules) {
    const selectors = rule.selector.split(', ');
    if (selectors.includes(tagName)) {
      return rule.replacement(element, childContent);
    }
  }

  // No rule found, return content as-is
  return childContent;
};

export const htmlToMarkdown = (html: string): string => {
  if (!html || html.trim() === '') {
    return '';
  }

  // Create a temporary DOM element
  const container = document.createElement('div');
  container.innerHTML = html;

  // Process the content
  let markdown = '';
  container.childNodes.forEach((node) => {
    markdown += processNode(node);
  });

  // Clean up extra whitespace
  markdown = markdown
    .replace(/\n{3,}/g, '\n\n') // Remove excess newlines
    .replace(/^\s+|\s+$/g, ''); // Trim

  return markdown;
};

export const useHtmlToMarkdown = () => {
  return {
    htmlToMarkdown,
  };
};
