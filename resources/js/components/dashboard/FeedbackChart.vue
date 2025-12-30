<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useAppearance } from '@/composables/useAppearance';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

interface Stats {
  totalFeedback: number;
  positiveFeedback: number;
}

const props = defineProps<{ stats: Stats }>();

const { appearance } = useAppearance();

const negativeFeedback = computed(() => props.stats.totalFeedback - props.stats.positiveFeedback);

const chartOptions = computed(() => ({
  chart: {
    type: 'donut' as const,
    background: 'transparent',
  },
  labels: ['Helpful', 'Not Helpful'],
  colors: ['#22c55e', '#ef4444'],
  legend: {
    position: 'bottom' as const,
    labels: {
      colors: appearance.value === 'dark' ? '#9ca3af' : '#4b5563',
    },
  },
  dataLabels: {
    enabled: true,
    formatter: (val: number) => `${Math.round(val)}%`,
  },
  plotOptions: {
    pie: {
      donut: {
        size: '60%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            color: appearance.value === 'dark' ? '#9ca3af' : '#4b5563',
            formatter: () => props.stats.totalFeedback.toString(),
          },
        },
      },
    },
  },
  stroke: {
    show: false,
  },
  theme: {
    mode: appearance.value === 'system' ? 'light' : appearance.value,
  },
}));

const series = computed(() => [props.stats.positiveFeedback, negativeFeedback.value]);
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Feedback Overview</CardTitle>
    </CardHeader>
    <CardContent>
      <div
        v-if="stats.totalFeedback === 0"
        class="flex h-[200px] items-center justify-center text-muted-foreground"
      >
        No feedback data yet
      </div>
      <VueApexCharts v-else type="donut" height="200" :options="chartOptions" :series="series" />
    </CardContent>
  </Card>
</template>
