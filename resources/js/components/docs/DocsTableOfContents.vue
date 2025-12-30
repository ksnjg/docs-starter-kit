<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { onMounted, onUnmounted, ref } from 'vue';

interface TocItem {
  id: string;
  text: string;
  level: number;
}

interface Props {
  items: TocItem[];
}

defineProps<Props>();

const activeId = ref<string>('');

const handleScroll = () => {
  const headings = document.querySelectorAll('h1[id], h2[id], h3[id]');
  let current = '';

  headings.forEach((heading) => {
    const rect = heading.getBoundingClientRect();
    if (rect.top <= 100) {
      current = heading.id;
    }
  });

  activeId.value = current;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

const scrollTo = (id: string) => {
  const element = document.getElementById(id);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};

const getIndentClass = (level: number) => {
  if (level === 2) {
    return 'pl-3';
  }
  if (level === 3) {
    return 'pl-6';
  }
  return '';
};
</script>

<template>
  <aside v-if="items.length > 0" class="hidden w-56 shrink-0 lg:block">
    <div class="sticky top-20 overflow-y-auto pr-4">
      <h4 class="mb-3 text-sm font-semibold">On this page</h4>
      <ul class="space-y-1">
        <li v-for="item in items" :key="item.id" :class="getIndentClass(item.level)">
          <Button
            variant="ghost"
            size="sm"
            :class="
              cn(
                'h-auto w-full justify-start px-2 py-1 text-left text-sm',
                activeId === item.id
                  ? 'font-medium text-primary'
                  : 'font-normal text-muted-foreground',
              )
            "
            @click="scrollTo(item.id)"
          >
            {{ item.text }}
          </Button>
        </li>
      </ul>
    </div>
  </aside>
</template>
