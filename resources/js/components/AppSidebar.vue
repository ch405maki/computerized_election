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
    
    // Grab the clean boolean flags we set up in the HandleInertiaRequests middleware
    const isAdmin = computed(() => page.props.isAdmin as boolean);
    const isSuperAdmin = computed(() => page.props.isSuperAdmin as boolean);

    // Helper to safely check user permissions
    const hasPermission = (permissionName: string) => {
        // Cast to any to bypass strict typing for the auth object if types aren't fully defined
        const user = (page.props.auth as any)?.user as any;
        
        // If the user has a permissions object, check if the specific key is true
        if (user?.permissions && user.permissions[permissionName] === true) {
            return true;
        }

        return false;
    };

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
                // { title: 'Activation', href: '/voters/status', icon: KeyRound },
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
                { title: 'Voter Turnout', href: '/reports/log', icon: Logs },
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

    // Filter main navigations based on isAdmin OR isSuperAdmin
    const mainNavItems = computed<DropdownNavItem[]>(() => {
        let items = baseMainNavItems.value;

        // Role-based filtering: If user is neither admin nor superadmin, restrict them
        if (!isAdmin.value && !isSuperAdmin.value) {
            const restrictedTitles = ['Voters', 'Voting Page', 'Candidates'];
            items = items.filter(item => !restrictedTitles.includes(item.title));
        }

        return items;
    });

    // Filter config navigations - only show Users tab to superadmin
    const configNavItems = computed<DropdownNavItem[]>(() => {
        // If user is neither admin nor superadmin, hide config entirely
        if (!isAdmin.value && !isSuperAdmin.value) {
            return [];
        }
        
        // If user is not superadmin (meaning they are just a regular admin at this point), filter out User Management
        if (!isSuperAdmin.value) {
            return baseConfigNavItems.value.filter(item => item.title !== 'User Management');
        }
        
        // Superadmin gets everything
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