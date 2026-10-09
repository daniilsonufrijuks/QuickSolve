<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { CreditCard, FileText, FolderDown, LayoutGrid, Receipt, Shield } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage<SharedData>();

const mainNavItems = [
    { title: 'Dashboard', url: '/dashboard', icon: LayoutGrid },
    { title: 'Subscription', url: '/dashboard/subscription', icon: CreditCard },
    { title: 'Purchases', url: '/dashboard/purchases', icon: Receipt },
    { title: 'Downloads', url: '/dashboard/downloads', icon: FolderDown },
    { title: 'Documents', url: '/dashboard/documents', icon: FileText },
    { title: 'Tools', url: '/tools', icon: LayoutGrid },
    { title: 'Templates', url: '/templates', icon: FileText },
];

if (page.props.auth.user?.is_admin) {
    mainNavItems.push({ title: 'Admin', url: '/admin', icon: Shield });
}

const footerNavItems: { title: string; href: string; icon?: typeof LayoutGrid }[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
