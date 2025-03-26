<script setup lang="ts">
    import NavFooter from '@/components/NavFooter.vue';
    import NavMain from '@/components/NavMain.vue';
    import NavUser from '@/components/NavUser.vue';
    import { 
        Sidebar, 
        SidebarContent, 
        SidebarFooter, 
        SidebarHeader, 
        SidebarMenu, 
        SidebarMenuButton, 
        SidebarMenuItem
    } from '@/components/ui/sidebar';
    import { Link } from '@inertiajs/vue3';
    import { 
        LayoutGrid,
        UserRoundCog,
        UsersRound,
        Vote,
        KeyRound,
        List,
        FileBox,
        Package2,
        Logs,
        UserRound
    } from 'lucide-vue-next';
    import AppLogo from './AppLogo.vue';
    import { ref } from 'vue';
    import { type NavItem } from '@/types';
    import { type SharedData } from '@/types';

    interface DropdownNavItem extends NavItem {
        children?: NavItem[];
        isOpen?: boolean;
    }

    const mainNavItems = ref<DropdownNavItem[]>([
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'User Management',
        href: '#',
        icon: UserRoundCog,
        isOpen: false,
        children: [
        { title: 'Users', href: '/users', icon: UsersRound },
        ],
    },
    {
        title: 'Voters',
        href: '#',
        icon: Vote,
        isOpen: false,
        children: [
        { title: 'Voter List', href: '/voters', icon: List },
        { title: 'Activation', href: '/voters/status', icon: KeyRound },
        ],
    },
    {
        title: 'Candidates',
        href: '#',
        icon: UserRound,
        isOpen: false,
        children: [
        { title: 'Candidates List', href: '/candidates', icon: UsersRound },
        { title: 'Positions', href: '/candidates/positions', icon: List },
        ],
    },
    {
        title: 'Reports',
        href: '#',
        icon: FileBox,
        isOpen: false,
        children: [
        { title: 'Result', href: '/reports/result', icon: Package2 },
        { title: 'Log', href: '/reports/log', icon: Logs },
        ],
    },
    ]);

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
        <NavMain :items="mainNavItems" group-label="Navigation" />
        </SidebarContent>

        <SidebarFooter>
        <NavFooter :items="footerNavItems" />
        <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>