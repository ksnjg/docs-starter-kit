<script setup lang="ts">
import DeleteConfirmDialog from '@/components/DeleteConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import PageTreeDraggable from '@/components/PageTreeDraggable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Page, StatusOption } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { FileText, Plus } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface TreeItem {
  id: number;
  title: string;
  slug: string;
  type: 'navigation' | 'group' | 'document';
  status: 'draft' | 'published' | 'archived';
  updated_at: string;
  children: TreeItem[];
}

interface Props {
  treeData: TreeItem[];
  navigationTabs: Pick<Page, 'id' | 'title' | 'slug'>[];
  filters: {
    status?: string;
    search?: string;
    type?: string;
  };
  statuses: StatusOption[];
  types: StatusOption[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Pages', href: '/admin/pages' },
];

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const typeFilter = ref(props.filters.type ?? '');
const isDeleteDialogOpen = ref(false);
const itemToDelete = ref<TreeItem | null>(null);
const processingId = ref<number | null>(null);

const applyFilters = useDebounceFn(() => {
  router.get(
    '/admin/pages',
    {
      search: search.value || undefined,
      status: statusFilter.value || undefined,
      type: typeFilter.value || undefined,
    },
    {
      preserveState: true,
      replace: true,
    },
  );
}, 300);

watch([search], () => applyFilters());

const onStatusChange = (value: unknown) => {
  statusFilter.value = String(value ?? '');
  applyFilters();
};

const onTypeChange = (value: unknown) => {
  typeFilter.value = String(value ?? '');
  applyFilters();
};

const openDeleteDialog = (item: TreeItem) => {
  itemToDelete.value = item;
  isDeleteDialogOpen.value = true;
};

const confirmDelete = () => {
  if (!itemToDelete.value) {
    return;
  }

  processingId.value = itemToDelete.value.id;
  router.delete(`/admin/pages/${itemToDelete.value.id}`, {
    onFinish: () => {
      processingId.value = null;
      isDeleteDialogOpen.value = false;
      itemToDelete.value = null;
    },
  });
};

const handleDuplicate = (item: TreeItem) => {
  processingId.value = item.id;
  router.post(
    `/admin/pages/${item.id}/duplicate`,
    {},
    {
      onFinish: () => {
        processingId.value = null;
      },
    },
  );
};

const handlePublish = (item: TreeItem) => {
  processingId.value = item.id;
  router.post(
    `/admin/pages/${item.id}/publish`,
    {},
    {
      onFinish: () => {
        processingId.value = null;
      },
    },
  );
};

const handleUnpublish = (item: TreeItem) => {
  processingId.value = item.id;
  router.post(
    `/admin/pages/${item.id}/unpublish`,
    {},
    {
      onFinish: () => {
        processingId.value = null;
      },
    },
  );
};
</script>

<template>
  <Head title="Pages Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-4 py-6">
      <div class="flex items-center justify-between">
        <Heading title="Pages" description="Manage your documentation pages" />
        <Button as-child>
          <Link href="/admin/pages/create">
            <Plus class="mr-2 h-4 w-4" />
            Create Page
          </Link>
        </Button>
      </div>

      <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center">
        <div class="flex-1">
          <Input v-model="search" placeholder="Search pages..." class="max-w-sm" />
        </div>
        <Select :model-value="typeFilter" @update:model-value="onTypeChange">
          <SelectTrigger class="w-[180px]">
            <SelectValue placeholder="Filter by type" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="t in types" :key="t.value" :value="t.value">
              {{ t.label }}
            </SelectItem>
          </SelectContent>
        </Select>
        <Select :model-value="statusFilter" @update:model-value="onStatusChange">
          <SelectTrigger class="w-[180px]">
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem v-for="status in statuses" :key="status.value" :value="status.value">
              {{ status.label }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="mt-6">
        <div v-if="treeData.length === 0" class="rounded-lg border bg-card py-12 text-center">
          <div
            class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted"
          >
            <FileText class="h-6 w-6 text-muted-foreground" />
          </div>
          <h3 class="text-lg font-medium">No pages yet</h3>
          <p class="mt-1 text-sm text-muted-foreground">
            Get started by creating your first documentation page.
          </p>
          <Button as-child class="mt-4">
            <Link href="/admin/pages/create">
              <Plus class="mr-2 h-4 w-4" />
              Create Page
            </Link>
          </Button>
        </div>
        <PageTreeDraggable
          v-else
          :items="treeData"
          :processing-id="processingId"
          @delete="openDeleteDialog"
          @duplicate="handleDuplicate"
          @publish="handlePublish"
          @unpublish="handleUnpublish"
        />
      </div>
    </div>

    <DeleteConfirmDialog
      v-model:open="isDeleteDialogOpen"
      title="Delete this page?"
      description="This action cannot be undone. This will permanently delete the page"
      :item-name="itemToDelete?.title"
      :processing="processingId !== null"
      @confirm="confirmDelete"
    />
  </AppLayout>
</template>
