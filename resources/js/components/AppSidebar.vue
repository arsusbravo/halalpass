<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    Building,
    Building2,
    ChevronDown,
    Download,
    LayoutGrid,
    LogOut,
    MoreHorizontal,
    Package,
    ScrollText,
    ShieldCheck,
    Truck,
    TreePine,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { useAuth } from '@/composables/useAuth';
import { useTrans } from '@/composables/useTrans';
import { type NavItem } from '@/types';
import { computed, ref } from 'vue';

const { isOwner, isAdmin, activeCompany, hasCompanyContext } = useAuth();
const { t } = useTrans();

const showMore = ref(false);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (isOwner.value) {
        items.push({ title: t('Companies'), href: '/companies', icon: Building });
    }

    items.push({ title: t('Dashboard'), href: '/dashboard', icon: LayoutGrid });

    if (hasCompanyContext.value || !isOwner.value) {
        items.push(
            { title: t('Facilities'), href: '/facilities', icon: Building2 },
            { title: t('Ingredients'), href: '/ingredients', icon: TreePine },
            { title: t('Products'), href: '/products', icon: Package },
            { title: t('Certification'), href: '/certification', icon: ShieldCheck },
            { title: t('Export'), href: '/export', icon: Download },
        );

        if (isAdmin.value) {
            items.push({ title: t('Users'), href: '/users', icon: Users });
        }
    }

    return items;
});

const moreNavItems = computed<NavItem[]>(() => {
    if (!hasCompanyContext.value && !isOwner.value) return [];
    return [
        { title: t('Suppliers'), href: '/suppliers', icon: Truck },
        { title: t('Certificates'), href: '/certificates', icon: ShieldCheck },
    ];
});

const footerNavItems = computed<NavItem[]>(() => [
    { title: t('SIHALAL Docs'), href: 'https://sihalal.halal.go.id', icon: ScrollText },
    { title: t('Documentation'), href: 'https://laravel.com/docs/starter-kits#vue', icon: BookOpen },
]);

function leaveCompany() {
    router.post('/companies/leave');
}
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>

            <!-- Active company indicator for owner -->
            <div v-if="isOwner && activeCompany" class="mx-2 mb-1 flex items-center justify-between rounded-lg bg-emerald-50 px-3 py-2 dark:bg-emerald-950">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-semibold text-emerald-800 dark:text-emerald-200">{{ activeCompany.name }}</p>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ t('Active') }}</p>
                </div>
                <button @click="leaveCompany" class="ml-2 shrink-0 text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200" :title="t('Leave Company Context')">
                    <LogOut class="h-3.5 w-3.5" />
                </button>
            </div>

            <!-- Company name for admin -->
            <div v-else-if="!isOwner && activeCompany" class="mx-2 mb-1">
                <Link href="/company-profile" class="flex items-center rounded-lg px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800">
                    <Building class="mr-2 h-3.5 w-3.5" />
                    {{ activeCompany.name }}
                </Link>
            </div>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />

            <!-- More section (Suppliers, Certificates) -->
            <div v-if="moreNavItems.length > 0" class="px-3 pt-2">
                <button @click="showMore = !showMore" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-gray-400 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                    <MoreHorizontal class="h-4 w-4" />
                    <span>{{ t('More') }}</span>
                    <ChevronDown :class="['ml-auto h-3 w-3 transition-transform', showMore ? 'rotate-180' : '']" />
                </button>
                <div v-if="showMore" class="mt-1">
                    <NavMain :items="moreNavItems" />
                </div>
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <div class="px-3 py-2">
                <LanguageSwitcher />
            </div>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>