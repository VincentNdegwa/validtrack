<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CategoryScale, Chart as ChartJS, Legend, LinearScale, LineElement, PointElement, Title, Tooltip } from 'chart.js';
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

interface Document {
    id: number;
    expiry_date?: string;
    [key: string]: any;
}

const props = defineProps<{
    documents: Document[];
}>();

interface MonthData {
    month: string;
    year: number;
    fullDate: Date;
    expiringCount: number;
    criticalCount: number; // Documents expiring within 7 days of that month
}

// Group documents by month/year of expiry
const chartData = computed(() => {
    const today = new Date();
    const next12Months = Array.from({ length: 12 }, (_, i) => {
        const date = new Date();
        date.setMonth(today.getMonth() + i);
        return {
            month: date.toLocaleDateString('default', { month: 'short' }),
            year: date.getFullYear(),
            fullDate: date,
            expiringCount: 0,
            criticalCount: 0,
        } as MonthData;
    });

    // Count documents expiring in each month
    props.documents.forEach((doc) => {
        if (doc.expiry_date) {
            const expiryDate = new Date(doc.expiry_date);

            // Check if it's within our 12-month window
            for (let i = 0; i < next12Months.length; i++) {
                const periodStart = new Date(next12Months[i].fullDate);
                periodStart.setDate(1); // Start of month

                const periodEnd = new Date(periodStart);
                periodEnd.setMonth(periodStart.getMonth() + 1);
                periodEnd.setDate(0); // End of month

                if (expiryDate >= periodStart && expiryDate <= periodEnd) {
                    next12Months[i].expiringCount++;

                    // Check if it's a critical expiry (within 7 days of current date)
                    const today = new Date();
                    const diffTime = expiryDate.getTime() - today.getTime();
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                    if (diffDays >= 0 && diffDays <= 7) {
                        next12Months[i].criticalCount++;
                    }

                    break;
                }
            }
        }
    });

    // Format for chart
    const labels = next12Months.map((m) => `${m.month} ${m.year}`);
    const regularData = next12Months.map((m) => m.expiringCount - m.criticalCount);
    const criticalData = next12Months.map((m) => m.criticalCount);

    // Calculate the maximum value for setting up a good y-axis scale
    const maxCount = Math.max(...next12Months.map((m) => m.expiringCount));
    const suggestedMax = maxCount <= 10 ? maxCount + 2 : Math.ceil(maxCount * 1.2);

    return {
        labels,
        datasets: [
            {
                label: 'Expiring Soon (< 7 days)',
                data: criticalData,
                borderColor: 'rgb(239, 68, 68)', // Red
                backgroundColor: 'rgba(239, 68, 68, 0.7)',
                tension: 0.2,
                fill: true,
                pointBackgroundColor: 'rgb(239, 68, 68)',
                pointRadius: 4,
                pointHoverRadius: 6,
                order: 1,
            },
            {
                label: 'Regular Expiry',
                data: regularData,
                borderColor: 'rgb(245, 158, 11)', // Amber
                backgroundColor: 'rgba(245, 158, 11, 0.3)',
                tension: 0.2,
                fill: true,
                pointBackgroundColor: 'rgb(245, 158, 11)',
                pointRadius: 3,
                pointHoverRadius: 5,
                order: 2,
            },
        ],
        suggestedMax,
    };
});

// Type for tooltip items
interface TooltipItem {
    label: string;
    [key: string]: any;
}

// Type for context
interface TimelineContext {
    dataset: {
        label?: string;
    };
    raw: number;
    [key: string]: any;
}

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        x: {
            grid: {
                display: false,
                drawBorder: false,
            },
            ticks: {
                color: 'rgba(100, 100, 100, 0.8)',
                font: {
                    size: 10,
                },
            },
        },
        y: {
            beginAtZero: true,
            suggestedMax: chartData.value.suggestedMax,
            grid: {
                color: 'rgba(200, 200, 200, 0.1)',
                borderDash: [5, 5],
            },
            ticks: {
                precision: 0, // Only show whole numbers
                stepSize: 1,
                color: 'rgba(100, 100, 100, 0.8)',
                padding: 10,
            },
            title: {
                display: true,
                text: 'Number of Documents',
                color: 'rgba(100, 100, 100, 0.8)',
                font: {
                    weight: 'bold' as const,
                },
            },
        },
    },
    interaction: {
        mode: 'index' as const,
        intersect: false,
    },
    plugins: {
        title: {
            display: false,
        },
        legend: {
            display: true,
            position: 'top' as const,
            labels: {
                usePointStyle: true,
                pointStyle: 'rectRounded',
                padding: 20,
                font: {
                    size: 12,
                },
            },
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#333',
            bodyColor: '#555',
            bodyFont: {
                size: 13,
            },
            cornerRadius: 6,
            padding: 12,
            boxPadding: 6,
            borderColor: 'rgba(220, 220, 220, 0.9)',
            borderWidth: 1,
            callbacks: {
                title: function (tooltipItems: TooltipItem[]) {
                    return tooltipItems[0].label;
                },
                label: function (context: TimelineContext) {
                    const label = context.dataset.label || '';
                    const value = context.raw || 0;
                    if (value === 1) {
                        return `${label}: ${value} document`;
                    }
                    return `${label}: ${value} documents`;
                },
            },
        },
    },
};
</script>

<template>
    <Card class="h-full shadow-md transition-shadow duration-300 hover:shadow-lg">
        <CardHeader class="border-b pb-2">
            <CardTitle class="flex items-center space-x-2">
                <span class="mr-2 inline-block h-6 w-2 rounded-sm bg-gradient-to-b from-amber-500 to-red-500"></span>
                <span>Document Expiry Timeline</span>
            </CardTitle>
        </CardHeader>
        <CardContent class="pt-4">
            <div class="h-80">
                <Line :data="chartData" :options="chartOptions as any" aria-label="Document expiry timeline chart" />
            </div>
            <div class="mt-4 flex justify-center gap-6 text-xs text-muted-foreground">
                <div class="flex items-center">
                    <span class="mr-1 inline-block h-3 w-3 rounded-sm bg-red-500 opacity-70"></span>
                    <span>Critical Expiry (&lt;7 days)</span>
                </div>
                <div class="flex items-center">
                    <span class="mr-1 inline-block h-3 w-3 rounded-sm bg-amber-500 opacity-30"></span>
                    <span>Regular Expiry</span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
