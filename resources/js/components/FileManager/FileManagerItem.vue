<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import type { MediaFile } from '@/types/media';
import { FileIcon, FileTextIcon, ImageIcon, MusicIcon, VideoIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
  file: MediaFile;
  selected?: boolean;
  selectable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  selected: false,
  selectable: true,
});

const emit = defineEmits<{
  select: [file: MediaFile];
  toggle: [file: MediaFile];
}>();

const fileIcon = computed(() => {
  switch (props.file.file_type) {
    case 'image':
      return ImageIcon;
    case 'video':
      return VideoIcon;
    case 'audio':
      return MusicIcon;
    case 'document':
      return FileTextIcon;
    default:
      return FileIcon;
  }
});

const formattedSize = computed(() => {
  return props.file.human_size ?? '';
});

const handleClick = () => {
  emit('select', props.file);
};

const handleCheckboxChange = (_checked: boolean) => {
  emit('toggle', props.file);
};
</script>

<template>
  <div
    class="group relative cursor-pointer rounded-lg border bg-card p-2 transition-all hover:border-primary hover:shadow-sm"
    :class="{ 'border-primary ring-2 ring-primary/20': selected }"
    @click="handleClick"
  >
    <div v-if="selectable" class="absolute top-2 right-2 z-10" @click.stop>
      <Checkbox :checked="selected" @update:checked="handleCheckboxChange" />
    </div>

    <div class="aspect-square overflow-hidden rounded-md bg-muted">
      <img
        v-if="file.file_type === 'image' && file.thumbnail_url"
        :src="file.thumbnail_url"
        :alt="file.name"
        class="h-full w-full object-cover"
      />
      <div v-else class="flex h-full w-full items-center justify-center">
        <component :is="fileIcon" class="h-12 w-12 text-muted-foreground" />
      </div>
    </div>

    <div class="mt-2 space-y-1">
      <p class="truncate text-sm font-medium" :title="file.name">
        {{ file.name }}
      </p>
      <p class="text-xs text-muted-foreground">
        {{ formattedSize }}
      </p>
    </div>
  </div>
</template>
