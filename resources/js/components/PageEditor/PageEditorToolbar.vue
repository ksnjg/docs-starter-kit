<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import type { Editor } from '@tiptap/vue-3';
import {
  Bold,
  Code,
  Heading1,
  Heading2,
  Heading3,
  ImageIcon,
  Italic,
  Link,
  List,
  ListOrdered,
  Quote,
  Redo,
  Strikethrough,
  Underline,
  Undo,
} from 'lucide-vue-next';

interface Props {
  editor: Editor;
}

const props = defineProps<Props>();

const emit = defineEmits<{
  openFileManager: [];
}>();

const setLink = () => {
  const previousUrl = props.editor.getAttributes('link').href;
  const url = window.prompt('URL', previousUrl);

  if (url === null) {
    return;
  }

  if (url === '') {
    props.editor.chain().focus().extendMarkRange('link').unsetLink().run();
    return;
  }

  props.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};
</script>

<template>
  <div class="flex flex-wrap items-center gap-1 border-b p-2">
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('bold') }"
      @click="editor.chain().focus().toggleBold().run()"
    >
      <Bold class="h-4 w-4" />
    </Button>
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('italic') }"
      @click="editor.chain().focus().toggleItalic().run()"
    >
      <Italic class="h-4 w-4" />
    </Button>
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('underline') }"
      @click="editor.chain().focus().toggleUnderline().run()"
    >
      <Underline class="h-4 w-4" />
    </Button>
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('strike') }"
      @click="editor.chain().focus().toggleStrike().run()"
    >
      <Strikethrough class="h-4 w-4" />
    </Button>

    <Separator orientation="vertical" class="mx-1 h-6" />

    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('heading', { level: 1 }) }"
      @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
    >
      <Heading1 class="h-4 w-4" />
    </Button>
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('heading', { level: 2 }) }"
      @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
    >
      <Heading2 class="h-4 w-4" />
    </Button>
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('heading', { level: 3 }) }"
      @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
    >
      <Heading3 class="h-4 w-4" />
    </Button>

    <Separator orientation="vertical" class="mx-1 h-6" />

    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('bulletList') }"
      @click="editor.chain().focus().toggleBulletList().run()"
    >
      <List class="h-4 w-4" />
    </Button>
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('orderedList') }"
      @click="editor.chain().focus().toggleOrderedList().run()"
    >
      <ListOrdered class="h-4 w-4" />
    </Button>

    <Separator orientation="vertical" class="mx-1 h-6" />

    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('blockquote') }"
      @click="editor.chain().focus().toggleBlockquote().run()"
    >
      <Quote class="h-4 w-4" />
    </Button>
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('codeBlock') }"
      @click="editor.chain().focus().toggleCodeBlock().run()"
    >
      <Code class="h-4 w-4" />
    </Button>
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :class="{ 'bg-muted': editor.isActive('link') }"
      @click="setLink"
    >
      <Link class="h-4 w-4" />
    </Button>
    <Button variant="ghost" size="icon" type="button" @click="emit('openFileManager')">
      <ImageIcon class="h-4 w-4" />
    </Button>

    <Separator orientation="vertical" class="mx-1 h-6" />

    <Button
      variant="ghost"
      size="icon"
      type="button"
      :disabled="!editor.can().undo()"
      @click="editor.chain().focus().undo().run()"
    >
      <Undo class="h-4 w-4" />
    </Button>
    <Button
      variant="ghost"
      size="icon"
      type="button"
      :disabled="!editor.can().redo()"
      @click="editor.chain().focus().redo().run()"
    >
      <Redo class="h-4 w-4" />
    </Button>
  </div>
</template>
