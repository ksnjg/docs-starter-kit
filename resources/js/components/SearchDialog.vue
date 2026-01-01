<script setup lang="ts">
import { CommandDialog, CommandInput, CommandSeparator, useCommand } from '@/components/ui/command';
import { search } from '@/routes';
import { show as docsShow } from '@/routes/docs';
import { router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { FileText, Loader2, Search as SearchIcon } from 'lucide-vue-next';
import { computed, defineComponent, h, nextTick, onUnmounted, ref, watch } from 'vue';

interface SearchResult {
  id: number;
  title: string;
  slug: string;
  path: string;
  excerpt: string;
  type: string;
}

const open = defineModel<boolean>('open', { default: false });

const results = ref<SearchResult[]>([]);
const isLoading = ref(false);
const hasSearched = ref(false);
const currentQuery = ref('');
const selectedIndex = ref(-1);
const resultsContainer = ref<HTMLElement | null>(null);

function handleKeydown(e: KeyboardEvent) {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    open.value = !open.value;
    return;
  }

  if (!open.value || !showResults.value) {
    return;
  }

  switch (e.key) {
    case 'ArrowDown':
      e.preventDefault();
      selectedIndex.value = Math.min(selectedIndex.value + 1, results.value.length - 1);
      scrollToSelected();
      break;
    case 'ArrowUp':
      e.preventDefault();
      selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
      scrollToSelected();
      break;
    case 'Enter':
      if (selectedIndex.value >= 0 && results.value[selectedIndex.value]) {
        e.preventDefault();
        navigateToResult(results.value[selectedIndex.value]);
      }
      break;
  }
}

if (typeof window !== 'undefined') {
  window.addEventListener('keydown', handleKeydown);
}

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleKeydown);
  }
});

const performSearch = useDebounceFn(async (query: string) => {
  if (query.length < 2) {
    results.value = [];
    hasSearched.value = false;
    isLoading.value = false;
    return;
  }

  isLoading.value = true;
  hasSearched.value = true;

  try {
    const response = await fetch(search.url({ query: { q: query } }));
    const data = await response.json();
    results.value = data.results;
  } catch (error) {
    console.error('Search failed:', error);
    results.value = [];
  } finally {
    isLoading.value = false;
  }
}, 300);

watch(open, (isOpen) => {
  if (!isOpen) {
    results.value = [];
    hasSearched.value = false;
    isLoading.value = false;
    currentQuery.value = '';
    selectedIndex.value = -1;
  }
});

watch(results, () => {
  selectedIndex.value = results.value.length > 0 ? 0 : -1;
});

function scrollToSelected() {
  nextTick(() => {
    const container = resultsContainer.value;
    if (!container) {
      return;
    }

    const selectedElement = container.querySelector('[data-selected="true"]');
    if (selectedElement) {
      selectedElement.scrollIntoView({ block: 'nearest' });
    }
  });
}

function navigateToResult(result: SearchResult) {
  open.value = false;
  router.visit(docsShow.url(result.path));
}

const showEmptyState = computed(() => {
  return hasSearched.value && !isLoading.value && results.value.length === 0;
});

const showResults = computed(() => {
  return results.value.length > 0;
});

const showInitialState = computed(() => {
  return !hasSearched.value && !isLoading.value;
});

const SearchWatcher = defineComponent({
  setup() {
    const { filterState } = useCommand();

    watch(
      () => filterState.search,
      (newQuery) => {
        currentQuery.value = newQuery;
        if (newQuery.length >= 2) {
          isLoading.value = true;
        }
        performSearch(newQuery);
      },
    );

    return () => h('span', { class: 'hidden' });
  },
});
</script>

<template>
  <CommandDialog v-model:open="open">
    <CommandInput placeholder="Search documentation..." />
    <SearchWatcher />
    <div
      ref="resultsContainer"
      class="scrollbar max-h-[300px] overflow-x-hidden overflow-y-auto p-2"
      role="listbox"
      :aria-label="showResults ? `${results.length} results found` : undefined"
    >
      <div v-if="isLoading" class="flex items-center justify-center py-6">
        <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
      </div>

      <div v-else-if="showEmptyState" class="py-6 text-center text-sm text-muted-foreground">
        No results found for "{{ currentQuery }}"
      </div>

      <div v-else-if="showResults" class="space-y-1" role="group" aria-label="Documentation">
        <div class="px-2 py-1.5 text-xs font-medium text-muted-foreground" aria-hidden="true">
          Documentation
        </div>
        <button
          v-for="(result, index) in results"
          :key="result.id"
          role="option"
          :aria-selected="index === selectedIndex"
          :data-selected="index === selectedIndex"
          :class="[
            'relative flex w-full cursor-pointer items-center rounded-sm px-2 py-1.5 text-sm outline-none select-none',
            index === selectedIndex
              ? 'bg-accent text-accent-foreground'
              : 'hover:bg-accent hover:text-accent-foreground',
          ]"
          @click="navigateToResult(result)"
          @mouseenter="selectedIndex = index"
        >
          <FileText class="mr-2 h-4 w-4 shrink-0" />
          <div class="flex flex-col gap-1 overflow-hidden">
            <span class="text-left font-medium">{{ result.title }}</span>
            <span
              v-if="result.excerpt"
              class="text-left text-xs text-muted-foreground"
              v-html="result.excerpt"
            />
          </div>
        </button>
      </div>

      <div v-else-if="showInitialState" class="py-6 text-center text-sm text-muted-foreground">
        <SearchIcon class="mx-auto mb-2 h-6 w-6" />
        <p>Type to search documentation</p>
        <p class="mt-1 text-xs">Minimum 2 characters</p>
      </div>
    </div>

    <CommandSeparator />
    <div class="flex items-center justify-between px-3 py-2 text-xs text-muted-foreground">
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-1">
          <kbd class="rounded border bg-muted px-1.5 py-0.5 font-mono text-[10px]">↑</kbd>
          <kbd class="rounded border bg-muted px-1.5 py-0.5 font-mono text-[10px]">↓</kbd>
          <span>navigate</span>
        </div>
        <div class="flex items-center gap-1">
          <kbd class="rounded border bg-muted px-1.5 py-0.5 font-mono text-[10px]">↵</kbd>
          <span>select</span>
        </div>
      </div>
      <div class="flex items-center gap-1">
        <kbd class="rounded border bg-muted px-1.5 py-0.5 font-mono text-[10px]">esc</kbd>
        <span>close</span>
      </div>
    </div>
  </CommandDialog>
</template>
