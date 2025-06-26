<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import EventIcon from '@/assets/icons/events.svg'
import LeftArrowIcon from '@/assets/icons/left-arrow.svg'
import ConfirmDeleteDialog from '@/components/ConfirmDeleteDialog.vue';
import { ref, onMounted } from 'vue';
import DeleteIcon from '@/assets/icons/delete.svg'

const props = defineProps({
    event: Object,
});

const showDeleteDialog = ref(false);
const isDeletingPost = ref(false);


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
    title: props.event?.title || '',
    short_description: props.event?.short_description || '',
    description: props.event?.description || '',
    date: props.event?.date ? new Date(props.event.date).toISOString().split('T')[0] : '',
    end_date: props.event?.end_date ? new Date(props.event.end_date).toISOString().split('T')[0] : '',
    location: props.event?.location || '',
    image: props.event?.image?.id || null as number | null,
    _method: 'PUT',
});

const uploadedImages = ref<any[]>([]);
const filePondRef = ref();
const existingImage = ref<any>(null);

onMounted(() => {
    if (props.event?.image) {
        existingImage.value = props.event.image;
    }
});

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

const removeExistingImage = () => {
    existingImage.value = null;
    form.image = null;
};

const removeNewImage = () => {
    uploadedImages.value = [];
    form.image = existingImage.value?.id || null;
};

const submit = () => {
    form.post(`/events/${props.event?.uuid}`);
};

const deleteEvent = async () => {
    isDeletingPost.value = true;
    try {
        const response = await fetch(`/events/${props.event?.uuid}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        const result = await response.json();
        
        if (result.success) {
            router.visit('/events');
        } else {
            alert('Error: ' + (result.message || 'Failed to delete event'));
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Network error while deleting event');
    } finally {
        showDeleteDialog.value = false;
        isDeletingPost.value = false;
    }
};
</script>

<template>
    <Head title="Edit Event" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-lg">
             <form @submit.prevent="submit" class="space-y-4">
            <div class="flex justify-between">
                <div class="flex justify-between pl-3 w-full">
                    <div>
                        <div class="flex items-center gap-3">
                            <EventIcon class="w-5 h-5" />
                            <h1> {{ props.event?.title }}</h1>
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
                        <Button type="button" @click="showDeleteDialog = true" class="bg-white text-black  w-[10rem] border border-red-500 relative hover:bg-white relative">
                            <DeleteIcon class="w-4 h-4 mr-2 absolute left-10 top-4.5 -translate-y-1/2" />
                            Delete
                        </Button>
                        <Button class="w-[10rem]" type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </Button>
                    </div>
                </div>
            </div>
                <div class="flex gap-4">
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
                                    <Label for="short_description">Short Description</Label>
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
                                        :label-idle="existingImage && !uploadedImages.length ? 
                                            '&lt;span class=&quot;filepond--label-action&quot;&gt;Browse&lt;/span&gt; to replace current image' : 
                                            '&lt;span class=&quot;filepond--label-action&quot;&gt;Browse&lt;/span&gt; or drop an image here'"
                                        :allow-multiple="false"
                                        :max-files="1"
                                        accepted-file-types="image/jpeg, image/png, image/gif, image/webp"
                                        @addfile="handleFilePondAddFile"
                                        class="filepond--custom"
                                        />
                                    </div>
                                <div v-if="existingImage && !uploadedImages.length" class="mb-4">
                                    
                                    <div class="relative group mt-2">
                                        <img 
                                            :src="existingImage.optimizations?.medium?.url || existingImage.url" 
                                            :alt="existingImage.original_filename"
                                            class="w-full h-48 object-cover rounded-lg border"
                                        />
                                        <button
                                            type="button"
                                            @click="removeExistingImage()"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>
                                <div v-if="uploadedImages.length > 0" class="mb-4">
                                    <Label>New Image</Label>
                                    <div class="relative group mt-2">
                                        <img 
                                            :src="uploadedImages[0].optimizations?.medium?.url || uploadedImages[0].url" 
                                            :alt="uploadedImages[0].original_filename"
                                            class="w-full h-48 object-cover rounded border"
                                        />
                                        <button
                                            type="button"
                                            @click="removeNewImage()"
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
      <ConfirmDeleteDialog
        v-model:open="showDeleteDialog"
        title="Delete Event"
        description="This action cannot be undone. This will permanently delete your event and remove it from our servers."
        confirm-text="Delete Event"
        :is-loading="isDeletingPost"
        @confirm="deleteEvent"
    />
</template>