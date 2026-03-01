import { usePage } from '@inertiajs/vue3';

/**
 * Check if user has a specific permission
 * @param {string|string[]} permission 
 * @returns {boolean}
 */
export function hasPermission(permission) {
    const user = usePage().props.auth.user;
    if (!user || !user.permissions) return false;

    // SuperAdmin bypass (optional, depends if Spatie seeder already gives all permissions)
    if (user.roles.includes('superadmin')) return true;

    if (Array.isArray(permission)) {
        return permission.some(p => user.permissions.includes(p));
    }

    return user.permissions.includes(permission);
}

/**
 * Check if user has a specific role
 * @param {string|string[]} role 
 * @returns {boolean}
 */
export function hasRole(role) {
    const user = usePage().props.auth.user;
    if (!user || !user.roles) return false;

    if (Array.isArray(role)) {
        return role.some(r => user.roles.includes(r));
    }

    return user.roles.includes(role);
}

/**
 * Check if user has all specified roles
 * @param {string[]} roles 
 * @returns {boolean}
 */
export function hasAllRoles(roles) {
    const user = usePage().props.auth.user;
    if (!user || !user.roles) return false;

    return roles.every(r => user.roles.includes(r));
}

export default {
    install: (app) => {
        app.config.globalProperties.$can = hasPermission;
        app.config.globalProperties.$hasRole = hasRole;
        app.config.globalProperties.$hasAnyRole = hasRole;
        app.config.globalProperties.$hasAllRoles = hasAllRoles;
    }
};
