import { nextTick, type Ref } from 'vue';

export interface UseMarkdownEditorOptions {
  textareaRef: Ref<HTMLTextAreaElement | null>;
  content: Ref<string>;
}

export const useMarkdownEditor = ({ textareaRef, content }: UseMarkdownEditorOptions) => {
  const wrapSelection = (before: string, after: string) => {
    const textarea = textareaRef.value;
    if (!textarea) {
      return;
    }

    const { selectionStart, selectionEnd, value } = textarea;
    const selectedText = value.substring(selectionStart, selectionEnd);
    const beforeText = value.substring(0, selectionStart);
    const afterText = value.substring(selectionEnd);

    content.value = beforeText + before + selectedText + after + afterText;

    nextTick(() => {
      if (selectedText) {
        textarea.selectionStart = selectionStart + before.length;
        textarea.selectionEnd = selectionEnd + before.length;
      } else {
        textarea.selectionStart = textarea.selectionEnd = selectionStart + before.length;
      }
      textarea.focus();
    });
  };

  const insertAtCursor = (text: string) => {
    const textarea = textareaRef.value;
    if (!textarea) {
      return;
    }

    const { selectionStart, value } = textarea;
    const before = value.substring(0, selectionStart);
    const after = value.substring(selectionStart);

    content.value = before + text + after;

    nextTick(() => {
      textarea.selectionStart = textarea.selectionEnd = selectionStart + text.length;
      textarea.focus();
    });
  };

  const handleTab = (textarea: HTMLTextAreaElement) => {
    const { selectionStart, selectionEnd, value } = textarea;
    const before = value.substring(0, selectionStart);
    const after = value.substring(selectionEnd);
    content.value = before + '  ' + after;
    nextTick(() => {
      textarea.selectionStart = textarea.selectionEnd = selectionStart + 2;
    });
  };

  const handleEnter = (textarea: HTMLTextAreaElement): boolean => {
    const { selectionStart, selectionEnd, value } = textarea;
    const lineStart = value.lastIndexOf('\n', selectionStart - 1) + 1;
    const currentLine = value.substring(lineStart, selectionStart);
    const indent = currentLine.match(/^(\s*)/)?.[1] || '';

    const listMatch = currentLine.match(/^(\s*)([-*]|\d+\.)\s/);
    if (listMatch) {
      const listContent = currentLine.substring(listMatch[0].length).trim();
      if (!listContent) {
        const before = value.substring(0, lineStart);
        const after = value.substring(selectionStart);
        content.value = before + '\n' + after;
        nextTick(() => {
          textarea.selectionStart = textarea.selectionEnd = lineStart + 1;
        });
        return true;
      }
      const before = value.substring(0, selectionStart);
      const after = value.substring(selectionEnd);
      let listPrefix = listMatch[2];
      if (/^\d+\.$/.test(listPrefix)) {
        listPrefix = parseInt(listPrefix) + 1 + '.';
      }
      const newLine = '\n' + listMatch[1] + listPrefix + ' ';
      content.value = before + newLine + after;
      nextTick(() => {
        textarea.selectionStart = textarea.selectionEnd = selectionStart + newLine.length;
      });
      return true;
    }

    if (indent) {
      const before = value.substring(0, selectionStart);
      const after = value.substring(selectionEnd);
      content.value = before + '\n' + indent + after;
      nextTick(() => {
        textarea.selectionStart = textarea.selectionEnd = selectionStart + 1 + indent.length;
      });
      return true;
    }

    return false;
  };

  const handleKeyDown = (event: KeyboardEvent) => {
    const textarea = textareaRef.value;
    if (!textarea) {
      return;
    }

    const { selectionStart, selectionEnd, value } = textarea;

    if (event.key === 'Tab') {
      event.preventDefault();
      handleTab(textarea);
      return;
    }

    if (event.key === 'Enter' && handleEnter(textarea)) {
      event.preventDefault();
      return;
    }

    if ((event.ctrlKey || event.metaKey) && event.key === 'b') {
      event.preventDefault();
      wrapSelection('**', '**');
    }

    if ((event.ctrlKey || event.metaKey) && event.key === 'i') {
      event.preventDefault();
      wrapSelection('*', '*');
    }

    if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
      event.preventDefault();
      const selectedText = value.substring(selectionStart, selectionEnd);
      if (selectedText) {
        wrapSelection('[', '](url)');
      } else {
        insertAtCursor('[link text](url)');
      }
    }

    if ((event.ctrlKey || event.metaKey) && event.key === '`') {
      event.preventDefault();
      wrapSelection('`', '`');
    }
  };

  return {
    handleKeyDown,
    wrapSelection,
    insertAtCursor,
  };
};
