<script setup lang="ts">
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, Title } from 'chart.js';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

ChartJS.register(ArcElement, Tooltip, Legend, Title);

interface Document {
  id: number;
  document_type?: {
    id: number;
    name: string;
  };
  [key: string]: any;
}

const props = defineProps<{
  documents: Document[];
}>();

// Generate a consistent color based on a string
const stringToColor = (str: string) => {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  
  const hue = Math.abs(hash % 360);
  return `hsl(${hue}, 70%, 60%)`;
};

interface DocTypeCount {
  name: string;
  count: number;
}

const chartData = computed(() => {
  // Group documents by type
  const docTypeMap: Record<number, DocTypeCount> = {};
  
  props.documents.forEach(doc => {
    if (doc.document_type?.id && doc.document_type?.name) {
      const typeId = doc.document_type.id;
      const typeName = doc.document_type.name;
      
      if (!docTypeMap[typeId]) {
        docTypeMap[typeId] = {
          name: typeName,
          count: 0
        };
      }
      
      docTypeMap[typeId].count++;
    }
  });
  
  // Sort by count in descending order
  const sortedTypes = Object.values(docTypeMap)
    .sort((a: DocTypeCount, b: DocTypeCount) => b.count - a.count);
  
  // Take top 7 types and group the rest as "Other"
  const topTypes = sortedTypes.slice(0, 7);
  
  const otherCount = sortedTypes.slice(7).reduce((total: number, type: DocTypeCount) => total + type.count, 0);
  if (otherCount > 0) {
    topTypes.push({ name: 'Other', count: otherCount });
  }
  
  // Convert to chart format
  const labels = topTypes.map((type: DocTypeCount) => type.name);
  const data = topTypes.map((type: DocTypeCount) => type.count);
  const backgroundColor = topTypes.map((type: DocTypeCount) => stringToColor(type.name));
  const hoverBackgroundColor = topTypes.map((type: DocTypeCount) => {
    const color = stringToColor(type.name);
    // Convert HSL to a slightly darker version for hover
    const hslMatch = color.match(/hsl\((\d+),\s*(\d+)%,\s*(\d+)%\)/);
    if (hslMatch) {
      const [, hue, sat, light] = hslMatch;
      return `hsl(${hue}, ${sat}%, ${Math.max(40, parseInt(light) - 10)}%)`;
    }
    return color;
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

// Type for chart tooltip context
interface DoughnutTooltipContext {
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
  cutout: '65%',
  layout: {
    padding: 15
  },
  plugins: {
    legend: {
      position: 'right' as const,
      labels: {
        boxWidth: 12,
        padding: 15,
        usePointStyle: true,
        pointStyle: 'rectRounded',
        font: {
          size: 11
        }
      }
    },
    title: {
      display: false,
    },
    tooltip: {
      backgroundColor: 'rgba(255, 255, 255, 0.95)',
      titleColor: '#333',
      bodyColor: '#555',
      bodyFont: {
        size: 13
      },
      caretSize: 6,
      cornerRadius: 4,
      xPadding: 12,
      yPadding: 12,
      borderColor: 'rgba(220, 220, 220, 0.9)',
      borderWidth: 1,
      callbacks: {
        label: function(context: DoughnutTooltipContext) {
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
        <span class="inline-block w-2 h-6 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-sm mr-2"></span>
        <span>Document Type Distribution</span>
      </CardTitle>
    </CardHeader>
    <CardContent class="pt-4">
      <div class="h-80 relative">
        <Doughnut 
          :data="chartData" 
          :options="chartOptions as any" 
          aria-label="Document type distribution chart"
        />
      </div>
    </CardContent>
  </Card>
</template>
