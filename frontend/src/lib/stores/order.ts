import { writable } from 'svelte/store';

export const orderStore = writable({
    domain: '',
    tld: 'de',
    packageType: 'starter',
    price: 299,
    customerEmail: '',
    customerName: '',
    businessName: ''
});
