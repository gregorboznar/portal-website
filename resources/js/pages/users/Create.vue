<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import LeftArrowIcon from '@/assets/icons/left-arrow.svg'
import EyeIcon from '@/assets/icons/eye-icon.svg'
import EyeSlashIcon from '@/assets/icons/eye-icon-2.svg'
import { ref } from 'vue';
import UserIcon from '@/assets/icons/user-card.svg'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: 'Add User',
        href: '/users/create',
    },
];

const form = useForm({
    firstname: '',
    lastname: '',
    email: '',
    company: '',
    linkedin: '',
    facebook: '',
    password: '',
    password_confirmation: '',
    email_verified_at: '',
    role: 'user',
    profile_image: null as number | null,
});

const uploadedImages = ref<any[]>([]);
const filePondRef = ref();
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const handleFilePondAddFile = async (error: any, file: any) => {
    if (error) {
        console.error('FilePond add file error:', error);
        return;
    }
    
    await uploadImage(file.file);
};

const uploadImage = async (file: File) => {
    try {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('type', 'profile');
        
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
            form.profile_image = data.image.id;
        }
    } catch (error) {
        console.error('Error uploading image:', error);
    }
};

const removeImage = () => {
    uploadedImages.value = [];
    form.profile_image = null;
};

const submit = () => {
    console.log('Form data being submitted:', form.data());
    form.post('/users', {
        onSuccess: () => {
            console.log('User created successfully');
        },
        onError: (errors) => {
            console.log('Form submission errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Add User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-lg">
             <form @submit.prevent="submit" class="space-y-4">
            <div class="flex justify-between">
                <div class="flex justify-between pl-3 w-full">
                    <div>
                        <div class="flex items-center gap-3">
                            <UserIcon class="w-5 h-5" />
                            <h1>Add user</h1>
                        </div>
                        <Link 
                            :href="route('users')" 
                            class="flex items-center gap-2 text-green hover:text-green/70 transition-colors duration-300 text-sm underline ml-2 cursor-pointer"
                        >
                            <LeftArrowIcon class="w-4 h-4" />
                            Back to users list
                        </Link>
                    </div>
                    <div class="flex justify-end gap-4 items-center">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Adding...' : 'Add user' }}
                        </Button>
                    </div>
                </div>
            </div>
           
                <div class="flex gap-4">
                    <Card class="flex-1">
                        <CardContent>
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="firstname">First Name</Label>
                                        <Input
                                            id="firstname"
                                            v-model="form.firstname"
                                            type="text"
                                            placeholder="Enter first name"
                                            required
                                            :class="{ 'border-red-500': form.errors.firstname }"
                                        />
                                        <div v-if="form.errors.firstname" class="text-sm text-red-500">
                                            {{ form.errors.firstname }}
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="lastname">Last Name</Label>
                                        <Input
                                            id="lastname"
                                            v-model="form.lastname"
                                            type="text"
                                            placeholder="Enter last name"
                                            required
                                            :class="{ 'border-red-500': form.errors.lastname }"
                                        />
                                        <div v-if="form.errors.lastname" class="text-sm text-red-500">
                                            {{ form.errors.lastname }}
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Email</Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="Enter email address"
                                        required
                                        :class="{ 'border-red-500': form.errors.email }"
                                    />
                                    <div v-if="form.errors.email" class="text-sm text-red-500">
                                        {{ form.errors.email }}
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="company">Company</Label>
                                    <Input
                                        id="company"
                                        v-model="form.company"
                                        type="text"
                                        placeholder="Enter company name"
                                        :class="{ 'border-red-500': form.errors.company }"
                                    />
                                    <div v-if="form.errors.company" class="text-sm text-red-500">
                                        {{ form.errors.company }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="linkedin">LinkedIn</Label>
                                        <Input
                                            id="linkedin"
                                            v-model="form.linkedin"
                                            type="text"
                                            placeholder="Enter LinkedIn username"
                                            :class="{ 'border-red-500': form.errors.linkedin }"
                                        />
                                        <div v-if="form.errors.linkedin" class="text-sm text-red-500">
                                            {{ form.errors.linkedin }}
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="facebook">Facebook</Label>
                                        <Input
                                            id="facebook"
                                            v-model="form.facebook"
                                            type="text"
                                            placeholder="Enter Facebook username"
                                            :class="{ 'border-red-500': form.errors.facebook }"
                                        />
                                        <div v-if="form.errors.facebook" class="text-sm text-red-500">
                                            {{ form.errors.facebook }}
                                        </div>
                                    </div>
                                   
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="password">Password</Label>
                                        <div class="relative">
                                            <Input
                                                id="password"
                                                v-model="form.password"
                                                :type="showPassword ? 'text' : 'password'"
                                                placeholder="Password"
                                                required
                                                :class="{ 'border-red-500': form.errors.password }"
                                            />
                                            <button
                                                type="button"
                                                @click="showPassword = !showPassword"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                            >
                                                <EyeIcon v-if="!showPassword" class="h-4 w-4 text-gray-500" />
                                                <EyeSlashIcon v-else class="h-4 w-4 text-gray-500" />
                                            </button>
                                        </div>
                                        <div v-if="form.errors.password" class="text-sm text-red-500">
                                            {{ form.errors.password }}
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="password_confirmation">Repeat password</Label>
                                        <div class="relative">
                                            <Input
                                                id="password_confirmation"
                                                v-model="form.password_confirmation"
                                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                                placeholder="Repeat password"
                                                required
                                                :class="{ 'border-red-500': form.errors.password_confirmation }"
                                            />
                                            <button
                                                type="button"
                                                @click="showPasswordConfirmation = !showPasswordConfirmation"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                            >
                                                <EyeIcon v-if="!showPasswordConfirmation" class="h-4 w-4 text-gray-500" />
                                                <EyeSlashIcon v-else class="h-4 w-4 text-gray-500" />
                                            </button>
                                        </div>
                                        <div v-if="form.errors.password_confirmation" class="text-sm text-red-500">
                                            {{ form.errors.password_confirmation }}
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="email_verified_at">Registered at</Label>
                                        <Input
                                            id="email_verified_at"
                                            v-model="form.email_verified_at"
                                            type="datetime-local"
                                            :class="{ 'border-red-500': form.errors.email_verified_at }"
                                        />
                                        <div v-if="form.errors.email_verified_at" class="text-sm text-red-500">
                                            {{ form.errors.email_verified_at }}
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="role">Role</Label>
                                        <select
                                            id="role"
                                            v-model="form.role"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                            :class="{ 'border-red-500': form.errors.role }"
                                        >
                                            <option value="user">User</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                        <div v-if="form.errors.role" class="text-sm text-red-500">
                                            {{ form.errors.role }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card class="flex-1">
                        <CardContent>
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Profile Image</Label>   
                                </div>
                                <div class="mb-4">
                                    <FilePond
                                        ref="filePondRef"
                                        name="profile_image"
                                        label-idle='<span class="filepond--label-action">Browse</span> or drop a profile image here'
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