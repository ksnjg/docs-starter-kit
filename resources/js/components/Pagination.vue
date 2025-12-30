<script setup lang="ts">
import type { PaginationLink } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
  links: PaginationLink[];
  from: number | null;
  to: number | null;
  total: number;
  itemLabel?: string;
}

withDefaults(defineProps<Props>(), {
  itemLabel: 'items',
});
</script>

<template>
  <div v-if="links.length > 3" class="border-t p-4">
    <nav class="flex items-center justify-between">
      <p class="text-sm text-muted-foreground">
        Showing <span class="font-medium">{{ from ?? 0 }}</span> to
        <span class="font-medium">{{ to ?? 0 }}</span> of
        <span class="font-medium">{{ total }}</span> {{ itemLabel }}
      </p>
      <div class="flex gap-1">
        <template v-for="link in links" :key="link.label">
          <Link
            v-if="link.url"
            :href="link.url"
            :class="[
              'inline-flex items-center rounded-md border px-3 py-1.5 text-sm',
              link.active
                ? 'border-primary bg-primary text-primary-foreground'
                : 'border-border bg-background hover:bg-accent',
            ]"
          >
            <span v-html="link.label" />
          </Link>
          <span
            v-else
            :class="[
              'inline-flex items-center rounded-md border px-3 py-1.5 text-sm',
              'pointer-events-none border-border bg-background opacity-50',
            ]"
            v-html="link.label"
          />
        </template>
      </div>
    </nav>
  </div>
</template>
