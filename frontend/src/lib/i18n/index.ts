import { getLocaleFromNavigator, init, register } from 'svelte-i18n';

register('en', () => import('./locales/en.json'));
register('de', () => import('./locales/de.json'));
register('tr', () => import('./locales/tr.json'));

const savedLocale = typeof localStorage !== 'undefined' ? localStorage.getItem('locale') : null;

init({
    fallbackLocale: 'tr',
    initialLocale: savedLocale || getLocaleFromNavigator(),
});
