<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Cog, FileBox, LayoutGrid, List, Logs, Package2, UserRound, UserRoundCog, UsersRound, Vote } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLogo from './AppLogo.vue';

interface DropdownNavItem extends NavItem {
    children?: NavItem[];
    isOpen?: boolean;
    permissionKey?: string;
}

const page = usePage();

// 1. Centralize roles & user data
const isAdmin = computed(() => page.props.isAdmin as boolean);
const currentUser = computed(() => (page.props.auth as any)?.user as any);

// 2. Parse permissions exactly ONCE and cache them
const userPermissions = computed(() => {
    let perms = currentUser.value?.permissions;
    if (!perms) return null;

    if (typeof perms === 'string') {
        try { perms = JSON.parse(perms); } 
        catch (e) { perms = {}; }
    }
    return perms;
});

// Helper is now much faster—it just reads the cached object
const hasPermission = (permissionName: string) => {
    if (!userPermissions.value) return false;
    const val = userPermissions.value[permissionName];
    return val === true || val === 'true' || val === 1;
};

const baseMainNavItems = ref<DropdownNavItem[]>([
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid, permissionKey: 'showDashboardTab' },
    {
        title: 'Voters', href: '#', icon: Vote, isOpen: false, permissionKey: 'showVoterTab',
        children: [{ title: 'Voter List', href: '/voters', icon: List }],
    },
    {
        title: 'Candidates', href: '#', icon: UserRound, isOpen: false, permissionKey: 'showCandidateTab',
        children: [
            { title: 'Candidates List', href: '/candidates', icon: UsersRound },
            { title: 'Positions', href: '/candidates/positions', icon: List },
        ],
    },
    {
        title: 'Reports', href: '#', icon: FileBox, isOpen: false, permissionKey: 'showReportsTab',
        children: [
            { title: 'Results', href: '/reports/results', icon: Package2 },
            { title: 'Voter Turnout', href: '/reports/log', icon: Logs },
        ],
    },
]);

const baseConfigNavItems = ref<DropdownNavItem[]>([
    { title: 'Election', href: '/elections', icon: Cog, permissionKey: 'showElectionTab' },
    {
        title: 'User Management', href: '#', icon: UserRoundCog, isOpen: false,
        children: [{ title: 'Users', href: '/users', icon: UsersRound }],
    },
]);

const filterNavItems = (
    baseItems: DropdownNavItem[], 
    legacyRestrictedTitles: string[], 
    forceBlockTitles: string[] = []
) => {
    if (isAdmin.value) return baseItems;

    return baseItems.filter((item) => {
        if (forceBlockTitles.includes(item.title)) return false;

        if (currentUser.value?.permissions) {
            return !item.permissionKey || hasPermission(item.permissionKey);
        }

        if (legacyRestrictedTitles.includes(item.title)) {
            return false;
        }

        return true;
    });
};

const mainNavItems = computed(() => filterNavItems(
    baseMainNavItems.value, 
    ['Voters', 'Voting Page', 'Candidates']
));

const configNavItems = computed(() => filterNavItems(
    baseConfigNavItems.value, 
    ['Election', 'User Management'],
    ['User Management']              
));

const footerNavItems: NavItem[] = [];
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
            <NavMain v-if="mainNavItems.length > 0" :items="mainNavItems" group-label="Navigations" />
            <NavMain v-if="configNavItems.length > 0" :items="configNavItems" group-label="Configuration" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>