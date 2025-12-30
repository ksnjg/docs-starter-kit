<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import Sonner from '@/components/ui/sonner/Sonner.vue';
import { useAppearance } from '@/composables/useAppearance';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
});

const page = usePage();
const { appearance } = useAppearance();

interface FlashMessages {
  success?: string;
  info?: string;
  fail?: string;
  message?: string;
}

const showFlashMessages = () => {
  const flash = page.props.flash as FlashMessages;

  if (flash?.success) {
    toast.success(flash.success);
  }
  if (flash?.info) {
    toast.info(flash.info);
  }
  if (flash?.fail) {
    toast.error(flash.fail);
  }
  if (flash?.message) {
    toast.message(flash.message);
  }
};

onMounted(() => {
  showFlashMessages();
});

// Watch for flash message changes
watch(() => page.props.flash, showFlashMessages);
</script>

<template>
  <AppShell variant="sidebar">
    <AppSidebar />
    <AppContent variant="sidebar" class="overflow-x-hidden">
      <AppSidebarHeader :breadcrumbs="breadcrumbs" />
      <slot />
    </AppContent>
    <Sonner position="top-right" :rich-colors="true" :close-button="true" :theme="appearance" />
  </AppShell>
</template>
