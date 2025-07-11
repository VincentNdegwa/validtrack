<script setup lang="ts">
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

interface Subject {
  id: number;
  name: string;
  documents?: any[];
  [key: string]: any;
}

interface Document {
  id: number;
  subject_id: number;
  status: number;
  expiry_date?: string;
  [key: string]: any;
}

const props = defineProps<{
  subjects: Subject[];
  documents: Document[];
}>();

const subjectDocumentCounts = computed(() => {
  // Create a map of subject IDs to document counts
  const subjectMap: Record<number, string> = {};
  const subjectActiveDocuments: Record<number, number> = {};
  const subjectExpiringDocuments: Record<number, number> = {};
  
  // Initialize with all subjects
  props.subjects.forEach(subject => {
    subjectMap[subject.id] = subject.name;
    subjectActiveDocuments[subject.id] = 0;
    subjectExpiringDocuments[subject.id] = 0;
  });
  
  // Count documents per subject
  props.documents.forEach(doc => {
    if (doc.subject_id && subjectMap[doc.subject_id]) {
      // Count active documents (status = 1)
      if (doc.status === 1) {
        subjectActiveDocuments[doc.subject_id]++;
      }
      
      // Count documents expiring soon (within 30 days)
      if (doc.expiry_date) {
        const expiryDate = new Date(doc.expiry_date);
        const today = new Date();
        const diffDays = Math.ceil((expiryDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
        
        if (diffDays >= 0 && diffDays <= 30) {
          subjectExpiringDocuments[doc.subject_id]++;
        }
      }
    }
  });
  
  // Convert to arrays for chart
  const labels = Object.values(subjectMap);
  const activeData = Object.values(subjectActiveDocuments);
  const expiringData = Object.values(subjectExpiringDocuments);
  
  return {
    labels,
    activeData,
    expiringData
  };
});

const chartData = computed(() => {
  const { labels, activeData, expiringData } = subjectDocumentCounts.value;
  
  return {
    labels,
    datasets: [
      {
        label: 'Active Documents',
        backgroundColor: 'rgba(16, 185, 129, 0.8)',
        borderColor: 'rgba(16, 185, 129, 1)',
        borderWidth: 1,
        borderRadius: 4,
        hoverBackgroundColor: 'rgba(16, 185, 129, 0.9)',
        data: activeData as number[]
      },
      {
        label: 'Expiring Soon',
        backgroundColor: 'rgba(245, 158, 11, 0.8)',
        borderColor: 'rgba(245, 158, 11, 1)',
        borderWidth: 1,
        borderRadius: 4,
        hoverBackgroundColor: 'rgba(245, 158, 11, 0.9)',
        data: expiringData as number[]
      }
    ]
  };
});

// Type for the chart context
interface TooltipContext {
  dataset: {
    label?: string;
  };
  raw: number;
  [key: string]: any;
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  layout: {
    padding: {
      top: 5,
      right: 15,
      bottom: 5,
      left: 15
    }
  },
  scales: {
    x: {
      stacked: false,
      grid: {
        color: 'rgba(200, 200, 200, 0.1)',
        borderDash: [5, 5]
      },
      ticks: {
        autoSkip: true,
        maxRotation: 45,
        minRotation: 45,
        color: 'rgba(100, 100, 100, 0.8)'
      }
    },
    y: {
      stacked: false,
      beginAtZero: true,
      grid: {
        color: 'rgba(200, 200, 200, 0.1)',
        borderDash: [5, 5]
      },
      ticks: {
        color: 'rgba(100, 100, 100, 0.8)',
        padding: 10
      },
      title: {
        display: true,
        text: 'Number of Documents',
        color: 'rgba(100, 100, 100, 0.8)',
        font: {
          weight: 'bold'
        }
      }
    }
  },
  plugins: {
    title: {
      display: false,
    },
    legend: {
      labels: {
        usePointStyle: true,
        pointStyle: 'circle',
        padding: 20,
        font: {
          size: 12
        }
      }
    },
    tooltip: {
      backgroundColor: 'rgba(255, 255, 255, 0.9)',
      titleColor: '#333',
      bodyColor: '#666',
      bodyFont: {
        size: 13
      },
      borderColor: 'rgba(200, 200, 200, 0.9)',
      borderWidth: 1,
      padding: 12,
      boxPadding: 6,
      callbacks: {
        label: function(context: TooltipContext) {
          const label = context.dataset.label || '';
          const value = context.raw || 0;
          return `${label}: ${value}`;
        }
      }
    }
  }
};
</script>

<template>
  <Card class="h-full shadow-md hover:shadow-lg transition-shadow duration-300">
    <CardHeader class="pb-2 border-b">
      <CardTitle class="flex items-center space-x-2">
        <span class="inline-block w-2 h-6 bg-gradient-to-b from-green-500 to-amber-500 rounded-sm mr-2"></span>
        <span>Subject Compliance</span>
      </CardTitle>
    </CardHeader>
    <CardContent class="pt-4">
      <div style="height: 400px;">
        <Bar 
          :data="chartData" 
          :options="chartOptions as any" 
          aria-label="Subject compliance chart"
        />
      </div>
      <div class="mt-4 flex justify-center gap-6 text-xs text-muted-foreground">
        <div class="flex items-center">
          <span class="inline-block w-3 h-3 bg-emerald-500 opacity-80 rounded-sm mr-1"></span>
          <span>Active Documents</span>
        </div>
        <div class="flex items-center">
          <span class="inline-block w-3 h-3 bg-amber-500 opacity-80 rounded-sm mr-1"></span>
          <span>Expiring Soon</span>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
