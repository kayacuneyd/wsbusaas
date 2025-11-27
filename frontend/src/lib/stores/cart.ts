import { browser } from '$app/environment';
import { writable } from 'svelte/store';

interface CartItem {
    domain: string;
    packageType: string;
    price: number;
}

const storedCart = browser ? localStorage.getItem('cart') : null;
const initialCart: CartItem | null = storedCart ? JSON.parse(storedCart) : null;

export const cart = writable<CartItem | null>(initialCart);

if (browser) {
    cart.subscribe(value => {
        if (value) {
            localStorage.setItem('cart', JSON.stringify(value));
        } else {
            localStorage.removeItem('cart');
        }
    });
}
