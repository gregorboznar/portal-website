<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import EventIcon from '@/assets/icons/events.svg'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Events',
        href: '/events',
    },
];

// Mock data - replace with actual props from your controller
const currentEvents = [
    { id: 1, title: 'Team Meeting', date: '2024-01-15', time: '10:00 AM' },
    { id: 2, title: 'Project Presentation', date: '2024-01-20', time: '2:00 PM' },
    { id: 3, title: 'Company Retreat', date: '2024-01-25', time: '9:00 AM' },
];

const pastEvents = [
    { id: 4, title: 'Holiday Party', date: '2023-12-20', time: '6:00 PM' },
    { id: 5, title: 'Quarterly Review', date: '2023-12-15', time: '1:00 PM' },
    { id: 6, title: 'Training Workshop', date: '2023-12-10', time: '10:00 AM' },
];
</script>

<template>
    <Head title="Events" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
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
                    <Card v-for="event in currentEvents" :key="event.id" class="cursor-pointer hover:shadow-md transition-shadow">
                        <Link :href="`/events/${event.id}`" class="block">
                            <CardHeader class="pb-3">
                                <CardTitle class="text-lg">{{ event.title }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-1 text-sm text-muted-foreground">
                                    <p>📅 {{ event.date }}</p>
                                    <p>🕐 {{ event.time }}</p>
                                </div>
                            </CardContent>
                        </Link>
                    </Card>
                    
                    <!-- Empty state for current events -->
                    <div v-if="currentEvents.length === 0" class="col-span-full">
                        <Card class="border-dashed">
                            <CardContent class="flex flex-col items-center justify-center py-8">
                                <p class="text-muted-foreground mb-4">No current events</p>
                                <Link href="/events/create">
                                    <Button variant="outline">Create your first event</Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            <!-- Past Events Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Past Events</h2>
                    <span class="text-sm text-muted-foreground">{{ pastEvents.length }} events</span>
                </div>
                
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="event in pastEvents" :key="event.id" class="cursor-pointer hover:shadow-md transition-shadow opacity-75">
                        <Link :href="`/events/${event.id}`" class="block">
                            <CardHeader class="pb-3">
                                <CardTitle class="text-lg">{{ event.title }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-1 text-sm text-muted-foreground">
                                    <p>📅 {{ event.date }}</p>
                                    <p>🕐 {{ event.time }}</p>
                                </div>
                            </CardContent>
                        </Link>
                    </Card>
                    
                    <!-- Empty state for past events -->
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
    </AppLayout>
</template> 