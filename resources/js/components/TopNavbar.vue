<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import HomeIcon from '@/assets/icons/home.svg'
import HomeGreenIcon from '@/assets/icons/home-green.svg'
import MessageIcon from '@/assets/icons/message-2.svg'
import MessageGreenIcon from '@/assets/icons/message-2-green.svg'
import CircleIcon from '@/assets/icons/circle.svg'
import CircleGreenIcon from '@/assets/icons/circle-green.svg'
/* import { SidebarTrigger } from '@/components/ui/sidebar';
 */
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { Link, usePage } from '@inertiajs/vue3';
import { MoreHorizontal } from 'lucide-vue-next';
import { computed } from 'vue';

interface User {
    name: string;
    email: string;
    avatar?: string;
    company?: string;
    profile_image?: string;
    slug?: string;
}

interface AuthData {
    user: User;
}

const page = usePage();
const auth = computed(() => page.props.auth as AuthData);

const navItems = computed(() => [
    {
        icon: page.url === '/dashboard' ? HomeGreenIcon : HomeIcon,
        href: '/dashboard',
        active: page.url === '/dashboard',
        label: 'Social Feed'
    },
    {
        icon: page.url === '/chat' ? MessageGreenIcon : MessageIcon,
        href: '/chat',
        active: page.url === '/chat',
        label: 'Chat'
    },
    {
        icon: page.url === '/events' ? CircleGreenIcon : CircleIcon,
        href: '/events',
        active: page.url === '/events',
        label: 'Events'
    },
]);
</script>

<template>
    <div class="absolute top-4 left-[20rem] right-4 z-50 bg-white rounded-lg shadow-sm">
        <div class="flex items-center justify-between px-6 py-3 ">
               <SidebarTrigger class="-ml-1" />

            <!-- Center Navigation Icons -->
            <div class="flex items-center space-x-8">
                <template v-for="item in navItems" :key="item.href">
                    <Link :href="item.href" class="relative">
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            class="h-10 w-16 rounded-md hover:bg-bg relative"
                        >
                            <component :is="item.icon" class="w-[1.375rem]" />
                            <div 
                                v-if="item.active" 
                                class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 w-15 h-0.5 bg-green rounded-full"
                            />
                            <div 
                                v-if="'hasNotification' in item && item.hasNotification" 
                                class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 border-2 border-white rounded-full"
                            />
                        </Button>
                    </Link>
                </template>
            </div>

            <div class="flex items-center space-x-3">
                <Link :href="`/profile/${auth.user.slug}`" class="flex items-center space-x-3">
                <Avatar class="h-8 w-8 hover:opacity-80 transition duration-500">
                    <AvatarImage 
                        v-if="auth.user.profile_image" 
                        :src="auth.user.profile_image" 
                        :alt="auth.user.firstname + ' ' + auth.user.lastname" 
                    />
                    <AvatarFallback v-else class="bg-gray-300 text-gray-700 text-sm font-medium">
                        {{ getInitials(auth.user?.firstname + ' ' + auth.user?.lastname) }}
                    </AvatarFallback>
                </Avatar>
                <div class="hidden md:block text-left">
                    <div class="text-sm font-semibold text-gray-900">{{ auth.user.firstname + ' ' + auth.user.lastname }}</div>
                    <div class="text-xs text-gray-500">{{ auth.user.company || 'Company' }}</div>
                </div>
                </Link>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button 
                            variant="ghost" 
                            class="flex items-center space-x-3 px-3 py-2 hover:bg-gray-50 rounded-lg"
                        >
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