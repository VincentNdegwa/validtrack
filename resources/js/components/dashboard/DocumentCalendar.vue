<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import type { EventClickArg, EventMountArg } from '@fullcalendar/core';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
  documents: any[];
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const hasPermission = (permission: string): boolean => {
    if (!user.value?.permissions) return false;
    return user.value?.permissions?.includes(permission) || false;
};

const calendarRef = ref(null);

const calendarEvents = computed(() => {
  return props.documents
    .filter(doc => doc.expiry_date)
    .map(doc => {
      // Determine color based on days until expiry
      const daysUntilExpiry = getDaysUntilExpiry(doc.expiry_date);
      let color = '#10B981'; // Green for documents with expiry more than 30 days away
      
      if (daysUntilExpiry <= 0) {
        color = '#EF4444'; // Red for expired documents
      } else if (daysUntilExpiry <= 7) {
        color = '#F59E0B'; // Amber for documents expiring within 7 days
      } else if (daysUntilExpiry <= 30) {
        color = '#F97316'; // Orange for documents expiring within 30 days
      }
      
      const title = doc.subject?.name 
        ? `${doc.subject.name} - ${doc.document_type?.name || 'Document'}` 
        : doc.file_name || doc.document_type?.name || 'Document';
        const url = hasPermission('documents-view') ? `/documents/${doc.slug}` : '#';
      return {
        id: doc.id.toString(),
        title: title,
        start: doc.expiry_date,
        allDay: true,
          url: url,
        backgroundColor: color,
        borderColor: color,
        extendedProps: {
          document: doc
        }
      };
    });
});

const getDaysUntilExpiry = (expiryDate: string) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const expiry = new Date(expiryDate);
  expiry.setHours(0, 0, 0, 0);
  
  const diffTime = expiry.getTime() - today.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
};

const handleEventClick = (info: EventClickArg) => {
  info.jsEvent.preventDefault();
  if (info.event.url) {
    window.location.href = info.event.url;
  }
};

const handleEventDidMount = (info: EventMountArg) => {
  // Add tooltip
  const tooltip = document.createElement('div');
  tooltip.classList.add('calendar-tooltip');
  
  if (info.event.start) {
    const expiryDate = new Date(info.event.start);
    const days = getDaysUntilExpiry(expiryDate.toISOString().split('T')[0]);
    tooltip.innerHTML = `
      <strong>${info.event.title}</strong><br>
      Expires: ${expiryDate.toLocaleDateString()}<br>
      ${days <= 0 ? 'EXPIRED' : `${days} days left`}
    `;
  }
  
  tooltip.style.position = 'absolute';
  tooltip.style.zIndex = '10000';
//   tooltip.style.backgroundColor = '#fff';
//   tooltip.style.border = '1px solid #ddd';
  tooltip.style.padding = '10px';
  tooltip.style.borderRadius = '4px';
  tooltip.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
    tooltip.style.display = 'none';
  
  
  document.body.appendChild(tooltip);
  
  info.el.addEventListener('mouseover', function() {
    const rect = info.el.getBoundingClientRect();
    tooltip.style.top = `${rect.bottom + window.scrollY}px`;
    tooltip.style.left = `${rect.left + window.scrollX}px`;
    tooltip.style.display = 'block';
  });
  
  info.el.addEventListener('mouseout', function() {
    tooltip.style.display = 'none';
  });
};

const calendarOptions = computed(() => {
  return {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek'
    },
    events: calendarEvents.value,
    eventClick: handleEventClick,
    eventDidMount: handleEventDidMount,
    height: 'auto',
    expandRows: true,
    contentHeight: 'auto'
  };
});

onMounted(() => {
  // Apply custom styles
  const style = document.createElement('style');
  style.innerHTML = `
    .fc-daygrid-day.fc-day-future:has(.fc-event) {
      background-color: rgba(240, 255, 244, 0.5);
    }
    .fc-event {
      cursor: pointer;
    }
    .fc-event-title {
      font-weight: 500;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  `;
  document.head.appendChild(style);
});
</script>

<template>
  <Card class="shadow">
    <CardHeader>
      <div class="flex flex-row items-center justify-between">
        <CardTitle>Document Expiry Calendar</CardTitle>
        <div class="flex items-center gap-2">
          <div class="flex items-center gap-1">
            <span class="inline-block h-3 w-3 rounded-full bg-red-500"></span>
            <span class="text-xs">Expired</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="inline-block h-3 w-3 rounded-full bg-amber-500"></span>
            <span class="text-xs">&lt; 7 days</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="inline-block h-3 w-3 rounded-full bg-orange-500"></span>
            <span class="text-xs">&lt; 30 days</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="inline-block h-3 w-3 rounded-full bg-green-500"></span>
            <span class="text-xs">&gt; 30 days</span>
          </div>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <FullCalendar 
        ref="calendarRef"
        :options="calendarOptions"
        class="document-calendar"
      />
    </CardContent>
  </Card>
</template>

<style>
.fc-theme-standard td,
.fc-theme-standard th,
.fc-theme-standard .fc-scrollgrid{
  border: 1px solid var(--border);
}
.fc .fc-daygrid-day.fc-day-today{
    background-color: var(--popover);
}
.fc-daygrid-day.fc-day-future:has(.fc-event){
    background-color: var(--popover) !important;
}
.fc .fc-scrollgrid table> thead{
    background-color: var(--popover);
}
.calendar-tooltip{
    background-color: var(--popover) !important;
    font-size: 10px !important;
}
</style>

