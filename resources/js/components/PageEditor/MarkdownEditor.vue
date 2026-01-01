<script setup lang="ts">
import { useMarkdownEditor } from '@/composables/useMarkdownEditor';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

interface Props {
  modelValue: string;
  placeholder?: string;
  minHeight?: number;
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Write your markdown content...',
  minHeight: 300,
});

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const textareaRef = ref<HTMLTextAreaElement | null>(null);
const lineNumbersRef = ref<HTMLDivElement | null>(null);

const content = computed({
  get: () => props.modelValue,
  set: (value: string) => emit('update:modelValue', value),
});

const { handleKeyDown } = useMarkdownEditor({ textareaRef, content });

const lineCount = computed(() => Math.max(props.modelValue.split('\n').length, 1));
const lineNumbers = computed(() => Array.from({ length: lineCount.value }, (_, i) => i + 1));

const syncScroll = () => {
  if (textareaRef.value && lineNumbersRef.value) {
    lineNumbersRef.value.scrollTop = textareaRef.value.scrollTop;
  }
};

const adjustHeight = () => {
  const textarea = textareaRef.value;
  if (!textarea) {
    return;
  }
  textarea.style.height = 'auto';
  textarea.style.height = `${Math.max(textarea.scrollHeight, props.minHeight)}px`;
};

watch(
  () => props.modelValue,
  () => nextTick(adjustHeight),
);
onMounted(adjustHeight);
</script>

<template>
  <div
    class="markdown-editor relative flex overflow-hidden"
    :style="{ minHeight: `${minHeight}px` }"
  >
    <div
      ref="lineNumbersRef"
      class="line-numbers overflow-hidden border-r border-border bg-muted/30 px-3 py-4 text-right font-mono text-xs leading-6 text-muted-foreground select-none"
    >
      <div v-for="line in lineNumbers" :key="line" class="h-6">
        {{ line }}
      </div>
    </div>
    <textarea
      ref="textareaRef"
      v-model="content"
      :placeholder="placeholder"
      spellcheck="false"
      class="flex-1 resize-none overflow-auto border-0 bg-transparent p-4 font-mono text-sm leading-6 text-foreground outline-none placeholder:text-muted-foreground focus:ring-0"
      :style="{ minHeight: `${minHeight}px` }"
      @scroll="syncScroll"
      @keydown="handleKeyDown"
      @input="adjustHeight"
    />
  </div>
</template>

<style scoped>
.markdown-editor {
  font-variant-ligatures: none;
}

.markdown-editor textarea {
  tab-size: 2;
  -moz-tab-size: 2;
}

.line-numbers {
  min-width: 3rem;
}
</style>
