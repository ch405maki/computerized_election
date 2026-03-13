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
    import { Link, usePage } from '@inertiajs/vue3';
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
        Cog,
        UserRound
    } from 'lucide-vue-next';
    import AppLogo from './AppLogo.vue';
    import { ref, computed } from 'vue';
    import { type NavItem } from '@/types';

    interface DropdownNavItem extends NavItem {
        children?: NavItem[];
        isOpen?: boolean;
    }

    const page = usePage();
    
    const userRole = computed(() => {
        const props = page.props as { auth?: { user?: { role?: string } } };
        return props.auth?.user?.role || 'user';
    });

    const baseMainNavItems = ref<DropdownNavItem[]>([
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
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
            title: 'Voting Page',
            href: '#',
            icon: Vote,
            isOpen: false,
            children: [
                { title: 'Vote', href: '/vote', icon: List },
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
                { title: 'Results', href: '/reports/results', icon: Package2 },
                { title: 'Log', href: '/reports/log', icon: Logs },
            ],
        },
    ]);

    const baseConfigNavItems = ref<DropdownNavItem[]>([
        {
            title: 'Election',
            href: '/election',
            icon: Cog,
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
    ]);

    const mainNavItems = computed<DropdownNavItem[]>(() => {
        if (userRole.value === 'user') {
            const restrictedTitles = ['Voters', 'Voting Page', 'Candidates'];
            return baseMainNavItems.value.filter(item => !restrictedTitles.includes(item.title));
        }
        return baseMainNavItems.value;
    });

    const configNavItems = computed<DropdownNavItem[]>(() => {
        if (userRole.value === 'user') {
            return [];
        }
        return baseConfigNavItems.value;
    });

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
        <NavMain :items="mainNavItems" group-label="Navigations" />
        <NavMain v-if="configNavItems.length > 0" :items="configNavItems" group-label="Configuration" />
        </SidebarContent>

        <SidebarFooter>
        <NavFooter :items="footerNavItems" />
        <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>