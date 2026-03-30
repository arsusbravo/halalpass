import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    role: string;
    company_id: number | null;
}

interface PageProps {
    auth: { user: AuthUser | null };
    activeCompany: { id: number; name: string } | null;
    [key: string]: unknown;
}

/**
 * Role hierarchy:
 *
 * Owner       → Platform owner. Sees all companies via Companies page.
 *               Enters a company context to manage it. Full CRUD on everything.
 * Admin       → Company admin. Manages own company profile, users, facilities.
 *               Full CRUD within their company.
 * Manager     → CRUD on products, ingredients, certificates, suppliers.
 *               Cannot manage facilities, users, or company profile.
 * Viewer      → Read-only. Can download exports.
 */
export function useAuth() {
    const page = usePage<PageProps>();

    const user = computed<AuthUser | null>(() => page.props.auth?.user ?? null);
    const role = computed<string>(() => user.value?.role ?? 'viewer');
    const activeCompany = computed(() => page.props.activeCompany ?? null);
    const hasCompanyContext = computed(() => !!activeCompany.value);

    // Role checks
    const isOwner = computed(() => role.value === 'owner');
    const isAdmin = computed(() => ['owner', 'admin'].includes(role.value));
    const isManager = computed(() => ['owner', 'admin', 'manager'].includes(role.value));
    const isViewer = computed(() => role.value === 'viewer');

    // Permissions
    const canManageCompany = computed(() => isAdmin.value);
    const canManageUsers = computed(() => isAdmin.value);
    const canManageFacilities = computed(() => isAdmin.value);
    const canCreate = computed(() => isManager.value);
    const canEdit = computed(() => isManager.value);
    const canDelete = computed(() => isAdmin.value);
    const canEditSjph = computed(() => isManager.value);
    const canApproveSjph = computed(() => isAdmin.value);
    const canManageTokens = computed(() => isManager.value);

    return {
        user,
        role,
        activeCompany,
        hasCompanyContext,
        isOwner,
        isAdmin,
        isManager,
        isViewer,
        canManageCompany,
        canManageUsers,
        canManageFacilities,
        canCreate,
        canEdit,
        canDelete,
        canEditSjph,
        canApproveSjph,
        canManageTokens,
    };
}