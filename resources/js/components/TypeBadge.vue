<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { PageType } from '@/types';
import { Book, FileText, FolderTree } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
  type: PageType;
  showLabel?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showLabel: true,
});

const typeConfig = computed(() => {
  const configs = {
    navigation: {
      label: 'Navigation',
      icon: Book,
      variant: 'default' as const,
    },
    group: {
      label: 'Group',
      icon: FolderTree,
      variant: 'secondary' as const,
    },
    document: {
      label: 'Document',
      icon: FileText,
      variant: 'outline' as const,
    },
  };
  return configs[props.type] || configs.document;
});
</script>

<template>
  <Badge :variant="typeConfig.variant" class="gap-1">
    <component :is="typeConfig.icon" class="h-3 w-3" />
    <span v-if="showLabel">{{ typeConfig.label }}</span>
  </Badge>
</template>
