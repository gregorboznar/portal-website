<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users } from 'lucide-vue-next';
import TalmanLogo from '@/assets/images/talman-logo.webp';
import FeedIcon from '@/assets/icons/feed.svg?component'
import FriendsIcon from '@/assets/icons/friends.svg?component'
import EventsIcon from '@/assets/icons/events.svg?component'


const mainNavItems: NavItem[] = [
    {
        title: 'Social feed ',
        href: '/dashboard',
        icon: FeedIcon,
    },
    {
        title: 'Friends',
        href: '/friends',
        icon: FriendsIcon,
    },
     {
        title: 'Whitney Wire Report',
        href: '/whitney-wire-report',
        icon: Users,
    },
      {
        title: 'Events',
        href: '/events',
        icon: EventsIcon,
    },
];

const footerNavItems: NavItem[] = [
   


];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton class="h-[10rem]" as-child>
                        <Link class="flex items-center justify-center" :href="route('dashboard')">
                            <img 
                                :src="TalmanLogo" 
                                alt="Talman Group Logo" 
                                class="w-[10rem]" 
                            />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

       <!--  <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter> -->
    </Sidebar>
    <slot />
</template>
