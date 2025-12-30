<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { CheckIcon, CopyIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
  code: string;
  language?: string;
}

const props = withDefaults(defineProps<Props>(), {
  language: 'plaintext',
});

const copied = ref(false);

const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(props.code);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (error) {
    console.error('Failed to copy:', error);
  }
};
</script>

<template>
  <div class="group relative">
    <div class="absolute top-2 right-2 opacity-0 transition-opacity group-hover:opacity-100">
      <Button
        variant="ghost"
        size="icon"
        class="h-8 w-8 bg-background/80 backdrop-blur-sm"
        @click="copyToClipboard"
      >
        <CheckIcon v-if="copied" class="h-4 w-4 text-green-500" />
        <CopyIcon v-else class="h-4 w-4" />
      </Button>
    </div>
    <div v-if="language && language !== 'plaintext'" class="absolute top-2 left-3">
      <span class="text-xs text-muted-foreground">{{ language }}</span>
    </div>
    <pre
      class="overflow-x-auto rounded-lg bg-zinc-950 p-4 pt-8 text-sm dark:bg-zinc-900"
    ><code :class="`language-${language} hljs`">{{ code }}</code></pre>
  </div>
</template>
