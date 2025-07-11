<script setup lang="ts">
import { computed } from 'vue';
import { Pie } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Title } from 'chart.js';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

ChartJS.register(ArcElement, Tooltip, Legend, Title);

interface Document {
  id: number;
  status: number;
  [key: string]: any;
}

const props = defineProps<{
  documents: Document[];
}>();

const statusLabels = {
  0: 'Draft',
  1: 'Active',
  2: 'Expired',
  3: 'Archived'
};

const statusColors = {
  0: 'rgb(156, 163, 175)', // Gray for drafts
  1: 'rgb(16, 185, 129)',  // Green for active
  2: 'rgb(239, 68, 68)',   // Red for expired
  3: 'rgb(59, 130, 246)'   // Blue for archived
};

const chartData = computed(() => {
  // Count documents by status
  const statusCounts: Record<number, number> = { 0: 0, 1: 0, 2: 0, 3: 0 };
  
  props.documents.forEach(doc => {
    const status = doc.status;
    if (status in statusCounts) {
      statusCounts[status]++;
    }
  });
  
  // Convert to chart format
  const labels = Object.keys(statusCounts).map(status => 
    statusLabels[status as unknown as keyof typeof statusLabels] || `Status ${status}`
  );
  const data = Object.values(statusCounts);
  const backgroundColor = Object.keys(statusCounts).map(status => 
    statusColors[status as unknown as keyof typeof statusColors] || '#CCCCCC'
  );
  const hoverBackgroundColor = Object.keys(statusCounts).map(status => {
    const baseColor = statusColors[status as unknown as keyof typeof statusColors] || '#CCCCCC';
    return baseColor.replace('rgb', 'rgba').replace(')', ', 0.8)');
  });
  
  return {
    labels,
    datasets: [
      {
        data,
        backgroundColor,
        hoverBackgroundColor,
        borderWidth: 2,
        borderColor: 'rgba(255, 255, 255, 0.8)'
      }
    ]
  };
});

// Type for the chart tooltip context
interface ChartTooltipContext {
  label?: string;
  raw: number;
  dataset: {
    data: number[];
  };
  [key: string]: any;
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '60%',
  layout: {
    padding: 10
  },
  plugins: {
    legend: {
      position: 'right' as const,
      labels: {
        boxWidth: 15,
        padding: 15,
        usePointStyle: true,
        pointStyle: 'circle',
        font: {
          size: 12
        }
      }
    },
    title: {
      display: false,
    },
    tooltip: {
      backgroundColor: 'rgba(255, 255, 255, 0.95)',
      titleColor: '#333',
      bodyColor: '#666',
      bodyFont: {
        size: 13
      },
      borderColor: 'rgba(220, 220, 220, 0.9)',
      borderWidth: 1,
      padding: 12,
      boxPadding: 6,
      callbacks: {
        label: function(context: ChartTooltipContext) {
          const label = context.label || '';
          const value = context.raw || 0;
          const total = context.dataset.data.reduce((acc: number, curr: number) => acc + curr, 0);
          const percentage = Math.round((value / total) * 100);
          return `${label}: ${value} (${percentage}%)`;
        }
      }
    }
  },
  animation: {
    animateScale: true,
    animateRotate: true
  }
};
</script>

<template>
  <Card class="h-full shadow-md hover:shadow-lg transition-shadow duration-300">
    <CardHeader class="pb-2 border-b">
      <CardTitle class="flex items-center space-x-2">
        <span class="inline-block w-2 h-6 bg-gradient-to-b from-green-500 via-amber-500 to-red-500 rounded-sm mr-2"></span>
        <span>Document Status Distribution</span>
      </CardTitle>
    </CardHeader>
    <CardContent class="pt-4">
      <div class="h-80">
        <Pie 
          :data="chartData" 
          :options="chartOptions as any" 
          aria-label="Document status distribution chart"
        />
      </div>
    </CardContent>
  </Card>
</template>
