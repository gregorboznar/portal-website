<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import EventIcon from '@/assets/icons/events.svg'
import EventCard from '@/components/EventCard.vue';

interface Event {
    id: number;
    uuid: string;
    title: string;
    description?: string;
    date: string;
    time?: string;
    end_date?: string;
    location?: string;
    images?: any[];
}

interface Props {
    upcomingEvents: Event[];
    pastEvents: Event[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Events',
        href: '/events',
    },
];
</script>

<template>
    <Head title="Events" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4">
            <div class="flex items-center justify-between">
                <div class="flex gap-2 ml-3">
                    <EventIcon class="w-5 h-5 relative top-1" />
                    <div class="flex flex-col">
                        <h1 class="text-xl font-semibold">Events</h1>
                        <p>Upcoming Events and Activities</p>
                    </div>
                </div>
                <Link href="/events/create">
                    <Button>
                        Add Event
                    </Button>
                </Link>
            </div>
            <div class="space-y-4">
                <div class="flex flex-col gap-4">
                    <EventCard 
                        v-for="event in upcomingEvents" 
                        :key="event.id" 
                        :event="event"
                    />
                    <div v-if="upcomingEvents.length === 0" class="col-span-full">
                        <Card class="border-dashed">
                            <CardContent class="flex flex-col items-center justify-center py-8">
                                <p class="text-muted-foreground mb-4">No upcoming events</p>
                                <Link href="/events/create">
                                    <Button variant="outline">Create your first event</Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
              
                <div class="flex gap-2 ml-3">
                    <EventIcon class="w-5 h-5 relative top-1" />
                    <div class="flex flex-col">
                        <h1 class="text-xl font-semibold">Past Events</h1>
                        <p>Past Events and Activities</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex flex-col gap-4">
                    <EventCard 
                        v-for="event in pastEvents" 
                        :key="event.id" 
                        :event="event"
                        :is-past="true"
                    />
                    
                  
                        <div v-if="pastEvents.length === 0" class="col-span-full">
                            <Card class="border-dashed opacity-50">
                                <CardContent class="flex items-center justify-center py-8">
                                    <p class="text-muted-foreground">No past events</p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 