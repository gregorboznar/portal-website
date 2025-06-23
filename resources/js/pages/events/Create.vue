<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import EventIcon from '@/assets/icons/events.svg'
import LeftArrowIcon from '@/assets/icons/left-arrow.svg'
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Events',
        href: '/events',
    },
    {
        title: 'Create Event',
        href: '/events/create',
    },
];

const form = useForm({
    title: '',
    short_description: '',
    description: '',
    date: '',
    end_date: '',
    location: '',
    image: null as number | null,
});

const uploadedImages = ref<any[]>([]);
const filePondRef = ref();

const handleFilePondAddFile = async (error: any, file: any) => {
    if (error) {
        console.error('FilePond add file error:', error);
        return;
    }
    
    // Upload the file when it's added
    await uploadImage(file.file);
};

const uploadImage = async (file: File) => {
    try {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', 'events');
        
        const response = await fetch('/api/images/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            uploadedImages.value = [data.image];
            form.image = data.image.id;
        }
    } catch (error) {
        console.error('Error uploading image:', error);
    }
};

const removeImage = () => {
    uploadedImages.value = [];
    form.image = null;
};

const submit = () => {
    form.post('/events');
};
</script>

<template>
    <Head title="Create Event" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-lg">
             <form @submit.prevent="submit" class="space-y-4">
            <div class="flex justify-between">
                <div class="flex justify-between pl-3 w-full">
                    <div>
                        <div class="flex items-center gap-3">
                            <EventIcon class="w-5 h-5" />
                            <h1>Create new event</h1>
                        </div>
                        <Link 
                            :href="route('events')" 
                            class="flex items-center gap-2 text-green hover:text-green/70 transition-colors duration-300 text-sm underline ml-2 cursor-pointer"
                        >
                            <LeftArrowIcon class="w-4 h-4" />
                            Back to events
                        </Link>
                    </div>
                    <div class="flex justify-end gap-4 items-center">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Creating...' : 'Create Event' }}
                        </Button>
                    </div>
                </div>
            </div>
           
                <div class="flex gap-6">
                    <Card class="flex-1">
                        <CardContent>
                            <div class="space-y-6">
                                <div class="space-y-2">
                                    <Label for="title">Title</Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        placeholder="Enter event title"
                                        required
                                        :class="{ 'border-red-500': form.errors.title }"
                                    />
                                    <div v-if="form.errors.title" class="text-sm text-red-500">
                                        {{ form.errors.title }}
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="description">Short Description</Label>
                                    <Textarea
                                        id="short_description"
                                        v-model="form.short_description"
                                        placeholder="Brief description of the event"
                                        rows="4"
                                        :class="{ 'border-red-500': form.errors.short_description }"
                                    />
                                    <div v-if="form.errors.short_description" class="text-sm text-red-500">
                                        {{ form.errors.short_description }}
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Enter event description"
                                        rows="6"
                                        :class="{ 'border-red-500': form.errors.description }"
                                    />
                                    <div v-if="form.errors.description" class="text-sm text-red-500">
                                        {{ form.errors.description }}
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="date">Start Date</Label>
                                        <Input
                                            id="date"
                                            v-model="form.date"
                                            type="date"
                                            required
                                            :class="{ 'border-red-500': form.errors.date }"
                                        />
                                        <div v-if="form.errors.date" class="text-sm text-red-500">
                                            {{ form.errors.date }}
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="end_date">End date</Label>
                                        <Input
                                            id="end_date"
                                            v-model="form.end_date"
                                            type="date"
                                            :class="{ 'border-red-500': form.errors.end_date }"
                                        />
                                        <div v-if="form.errors.end_date" class="text-sm text-red-500">
                                            {{ form.errors.end_date }}
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label for="location">Location</Label>
                                    <Input
                                        id="location"
                                        v-model="form.location"
                                        type="text"
                                        placeholder="Enter event location"
                                        :class="{ 'border-red-500': form.errors.location }"
                                    />
                                    <div v-if="form.errors.location" class="text-sm text-red-500">
                                        {{ form.errors.location }}
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card class="flex-1">
                        <CardContent>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Event Image</Label>   
                                </div>
                                <div class="mb-4">
                                    <FilePond
                                        ref="filePondRef"
                                        name="image"
                                        label-idle='<span class="filepond--label-action">Browse</span> or drop an image here'
                                        :allow-multiple="false"
                                        :max-files="1"
                                        accepted-file-types="image/jpeg, image/png, image/gif, image/webp"
                                        @addfile="handleFilePondAddFile"
                                        class="filepond--custom"
                                    />
                                </div>
                                
                                <div v-if="uploadedImages.length > 0" class="space-y-2">
                                    <Label>Uploaded Image</Label>
                                    <div class="relative group w-full">
                                        <img 
                                            :src="uploadedImages[0].optimizations?.small?.url || uploadedImages[0].url" 
                                            :alt="uploadedImages[0].original_filename"
                                            class="w-full h-32 object-cover rounded border"
                                        />
                                        <button
                                            type="button"
                                            @click="removeImage()"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </form>
        </div>
    </AppLayout>
</template>