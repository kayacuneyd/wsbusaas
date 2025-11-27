import { browser } from '$app/environment';
import { writable } from 'svelte/store';

// Admin Auth
const storedAdminAuth = browser ? localStorage.getItem('adminAuth') : null;
const initialAdminAuth = storedAdminAuth ? JSON.parse(storedAdminAuth) : { isAuthenticated: false, token: null };

export const adminAuth = writable(initialAdminAuth);

if (browser) {
    adminAuth.subscribe(value => {
        localStorage.setItem('adminAuth', JSON.stringify(value));
    });
}

// Customer Auth
const storedCustomerAuth = browser ? localStorage.getItem('customerAuth') : null;
const initialCustomerAuth = storedCustomerAuth ? JSON.parse(storedCustomerAuth) : { isAuthenticated: false, token: null, user: null };

export const customerAuth = writable(initialCustomerAuth);

if (browser) {
    customerAuth.subscribe(value => {
        localStorage.setItem('customerAuth', JSON.stringify(value));
    });
}

export function login(token: string) {
    // Update adminAuth store and localStorage
    adminAuth.set({ isAuthenticated: true, token });
}

export function logout() {
    // Clear adminAuth store and localStorage
    adminAuth.set({ isAuthenticated: false, token: null });
}
