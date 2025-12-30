<script setup lang="ts">
import DashboardStats from '@/components/dashboard/DashboardStats.vue';
import FeedbackChart from '@/components/dashboard/FeedbackChart.vue';
import RecentPages from '@/components/dashboard/RecentPages.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, Page } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Stats {
  totalPages: number;
  publishedPages: number;
  draftPages: number;
  totalFeedback: number;
  positiveFeedback: number;
}

defineProps<{
  stats: Stats;
  recentPages: Page[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
];
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="px-4 py-6">
      <h1 class="mb-6 text-2xl font-bold">Dashboard</h1>
      <DashboardStats :stats="stats" />
      <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <FeedbackChart :stats="stats" />
        <RecentPages :pages="recentPages" />
      </div>
    </div>
  </AppLayout>
</template>
