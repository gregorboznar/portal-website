<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import HomeIcon from '@/assets/icons/home.svg'
import MessageIcon from '@/assets/icons/message-2.svg'
import CircleGreenIcon from '@/assets/icons/circle.svg'
/* import { SidebarTrigger } from '@/components/ui/sidebar';
 */
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { Link, usePage } from '@inertiajs/vue3';
import { Home, Bell, Search, MoreHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';

interface User {
    name: string;
    email: string;
    avatar?: string;
    company?: string;
    profile_image?: string;
}

interface AuthData {
    user: User;
}

const page = usePage();
const auth = computed(() => page.props.auth as AuthData);

const navItems = computed(() => [
    {
        icon: HomeIcon,
        href: '/dashboard',
        active: page.url === '/dashboard',
        label: 'Social Feed'
    },
    {
        icon: MessageIcon,
        href: '/friends',
        active: page.url === '/friends',
        label: 'Friends'
    },
    {
        icon: CircleGreenIcon,
        href: '/events',
        active: page.url === '/events',
        label: 'Events'
    },
]);
</script>

<template>
    <div class="fixed top-0 left-[18rem] right-0 z-50 bg-white border-b border-gray-200 shadow-sm rounded-lg m-4 ml-0">
        <div class="flex items-center justify-between px-6 py-3">
               <SidebarTrigger class="-ml-1" />

            <!-- Center Navigation Icons -->
            <div class="flex items-center space-x-1">
                <template v-for="item in navItems" :key="item.href">
                    <Link :href="item.href" class="relative">
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            class="h-12 w-12 rounded-full hover:bg-gray-100"
                            :class="{ 'bg-gray-100': item.active }"
                        >
                            <component :is="item.icon" class="h-6 w-6 text-gray-600" />
                            <div 
                                v-if="'hasNotification' in item && item.hasNotification" 
                                class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 border-2 border-white rounded-full"
                            />
                        </Button>
                    </Link>
                </template>
            </div>

            <div class="flex items-center space-x-3">
                <Avatar class="h-8 w-8">
            <AvatarImage 
             v-if="auth.user.profile_image" 
                :src="auth.user.profile_image" 
                :alt="auth.user.name" 
            />
             <AvatarFallback v-else class="bg-gray-300 text-gray-700 text-sm font-medium">
                {{ getInitials(auth.user?.name) }}
            </AvatarFallback>
            </Avatar>
                            <div class="hidden md:block text-left">
                                <div class="text-sm font-semibold text-gray-900">{{ auth.user.name }}</div>
                                <div class="text-xs text-gray-500">{{ auth.user.company || 'Company' }}</div>
                            </div>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="flex items-center space-x-3 px-3 py-2 hover:bg-gray-50 rounded-lg">
                         
                            <MoreHorizontal class="h-4 w-4 text-gray-400" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </div>
</template> 