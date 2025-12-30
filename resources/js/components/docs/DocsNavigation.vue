<script setup lang="ts">
import DocsNavigationItem from '@/components/docs/DocsNavigationItem.vue';
import { Input } from '@/components/ui/input';
import {
  Sidebar,
  SidebarContent,
  SidebarGroup,
  SidebarGroupContent,
  SidebarMenu,
} from '@/components/ui/sidebar';
import { Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

export interface SidebarItem {
  id: number;
  title: string;
  slug: string;
  type: 'navigation' | 'group' | 'document';
  icon: string | null;
  path: string;
  isExpanded: boolean;
  children?: SidebarItem[];
}

interface Props {
  items: SidebarItem[];
  currentPath: string;
}

const props = defineProps<Props>();

const searchQuery = ref('');

const filterItems = (items: SidebarItem[], query: string): SidebarItem[] => {
  if (!query.trim()) {
    return items;
  }

  const lowerQuery = query.toLowerCase();

  return items.reduce<SidebarItem[]>((acc, item) => {
    const titleMatches = item.title.toLowerCase().includes(lowerQuery);
    const filteredChildren = item.children ? filterItems(item.children, query) : [];

    if (titleMatches || filteredChildren.length > 0) {
      acc.push({
        ...item,
        isExpanded: filteredChildren.length > 0 ? true : item.isExpanded,
        children: filteredChildren.length > 0 ? filteredChildren : item.children,
      });
    }

    return acc;
  }, []);
};

const filteredItems = computed(() => filterItems(props.items, searchQuery.value));

const clearSearch = () => {
  searchQuery.value = '';
};
</script>

<template>
  <Sidebar collapsible="none" class="border-r">
    <SidebarContent class="pt-4">
      <div class="px-3 pb-3">
        <div class="relative">
          <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
          <Input
            v-model="searchQuery"
            type="search"
            placeholder="Search docs..."
            class="pr-8 pl-8"
          />
          <button
            v-if="searchQuery"
            type="button"
            class="absolute top-2.5 right-2.5 text-muted-foreground hover:text-foreground"
            @click="clearSearch"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
      </div>
      <SidebarGroup>
        <SidebarGroupContent>
          <SidebarMenu>
            <template v-if="filteredItems.length > 0">
              <DocsNavigationItem
                v-for="item in filteredItems"
                :key="item.id"
                :item="item"
                :current-path="currentPath"
                :depth="0"
              />
            </template>
            <div v-else class="px-3 py-6 text-center text-sm text-muted-foreground">
              No results found
            </div>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>
    </SidebarContent>
  </Sidebar>
</template>
