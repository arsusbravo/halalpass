<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building,
    Building2,
    ChevronDown,
    Download,
    Upload,
    LayoutGrid,
    LogOut,
    MoreHorizontal,
    Package,
    ShieldCheck,
    Truck,
    TreePine,
    Users,
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
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
const page = usePage();
const showMore = ref(false);

const currentPath = computed(() => String(page.url));

function isActive(href: string): boolean {
    return currentPath.value.startsWith(href);
}

const journeySteps = computed(() => {
    if (!hasCompanyContext.value && isOwner.value) return [];
    return [
        { step: 1, title: t('Company profile'), href: '/company-profile', icon: Building, desc: t('NPWP, signature') },
        { step: 2, title: t('Facilities'), href: '/facilities', icon: Building2, desc: t('Production sites') },
        { step: 3, title: t('Ingredients'), href: '/ingredients', icon: TreePine, desc: t('Materials & certificates') },
        { step: 4, title: t('Products'), href: '/products', icon: Package, desc: t('Link ingredients') },
        { step: 5, title: t('Certification'), href: '/certification', icon: ShieldCheck, desc: t('Check readiness') },
        { step: 6, title: t('Export'), href: '/export', icon: Download, desc: t('Download ZIP') },
        { step: 7, title: 'SIHALAL', href: 'https://ptsp.halal.go.id', icon: Upload, desc: t('Submit to government'), external: true },
    ];
});

const moreNavItems = computed<NavItem[]>(() => {
    if (!hasCompanyContext.value && isOwner.value) return [];
    return [
        { title: t('Suppliers'), href: '/suppliers', icon: Truck },
        { title: t('Certificates'), href: '/certificates', icon: ShieldCheck },
    ];
});

const footerNavItems = computed<NavItem[]>(() => [
    { title: t('Documentation'), href: '/doc.html', icon: BookOpen },
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

            <!-- Active company for owner -->
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
            <!-- Owner: Companies -->
            <div v-if="isOwner" class="px-3 pb-1">
                <Link href="/companies" :class="['flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition', isActive('/companies') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800']">
                    <Building class="h-4 w-4" />
                    {{ t('Companies') }}
                </Link>
            </div>

            <!-- Dashboard -->
            <div class="px-3 pb-1">
                <Link href="/dashboard" :class="['flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition', isActive('/dashboard') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800']">
                    <LayoutGrid class="h-4 w-4" />
                    {{ t('Dashboard') }}
                </Link>
            </div>

            <!-- Certification Journey -->
            <div v-if="journeySteps.length > 0" class="px-3 pt-3">
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                    {{ t('Certification Journey') }}
                </p>

                <div class="relative">
                    <!-- Vertical line connecting steps -->
                    <div class="absolute bottom-4 left-[22px] top-4 w-px bg-gray-200 dark:bg-gray-700" />

                    <div v-for="item in journeySteps" :key="item.step" class="relative">
                        <component
                            :is="item.external ? 'a' : Link"
                            :href="item.href"
                            :target="item.external ? '_blank' : undefined"
                            :class="['group flex items-start gap-3 rounded-lg px-3 py-2.5 transition',
                                !item.external && isActive(item.href)
                                    ? 'bg-emerald-50 dark:bg-emerald-950'
                                    : 'hover:bg-gray-50 dark:hover:bg-gray-800']"
                        >
                            <!-- Step number -->
                            <div :class="['relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold transition',
                                !item.external && isActive(item.href)
                                    ? 'bg-emerald-600 text-white shadow-sm'
                                    : 'bg-gray-100 text-gray-500 group-hover:bg-emerald-100 group-hover:text-emerald-700 dark:bg-gray-800 dark:text-gray-400']">
                                {{ item.step }}
                            </div>

                            <!-- Label + description -->
                            <div class="min-w-0 flex-1 pt-0.5">
                                <p :class="['text-sm font-medium leading-tight',
                                    !item.external && isActive(item.href)
                                        ? 'text-emerald-700 dark:text-emerald-400'
                                        : 'text-gray-700 dark:text-gray-300']">
                                    {{ item.title }}
                                    <svg v-if="item.external" class="ml-1 inline h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </p>
                                <p class="text-[11px] leading-tight text-gray-400 dark:text-gray-500">{{ item.desc }}</p>
                            </div>
                        </component>
                    </div>
                </div>
            </div>

            <!-- Admin -->
            <div v-if="isAdmin && (hasCompanyContext || !isOwner)" class="px-3 pt-3">
                <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ t('Admin') }}</p>
                <Link href="/users" :class="['flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition', isActive('/users') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800']">
                    <Users class="h-4 w-4" />
                    {{ t('Users') }}
                </Link>
            </div>

            <!-- More -->
            <div v-if="moreNavItems.length > 0" class="px-3 pt-2">
                <button @click="showMore = !showMore" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-gray-400 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                    <MoreHorizontal class="h-4 w-4" />
                    <span>{{ t('More') }}</span>
                    <ChevronDown :class="['ml-auto h-3 w-3 transition-transform', showMore ? 'rotate-180' : '']" />
                </button>
                <div v-if="showMore" class="mt-1 space-y-0.5">
                    <Link v-for="item in moreNavItems" :key="String(item.href)" :href="item.href" :class="['flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition', isActive(String(item.href)) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800']">
                        <component :is="item.icon" class="h-4 w-4" />
                        {{ item.title }}
                    </Link>
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