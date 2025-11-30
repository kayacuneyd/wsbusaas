<script lang="ts">
  import { customerAuth } from '$lib/stores/auth';
  import { t, locale } from 'svelte-i18n';
  import { page } from '$app/stores';
  import LanguageSwitcher from './LanguageSwitcher.svelte';

  let isMobileMenuOpen = false;

  function toggleMobileMenu() {
    isMobileMenuOpen = !isMobileMenuOpen;
  }
</script>

<header class="bg-brand-dark shadow-sm sticky top-0 z-50">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" aria-label="Top">
    <div class="w-full py-4 flex items-center justify-between border-b border-indigo-500 lg:border-none">
      <div class="flex items-center">
        <div class="flex-shrink-0 flex items-center gap-3">
          <a href="/" class="text-2xl font-bold text-white flex items-center gap-2">
            <span class="text-brand-light">BEZMIDAR</span>
          </a>
        </div>
        <!-- Desktop Navigation -->
        <div class="hidden md:flex space-x-8 ml-10">
          <a href="/" class="text-base font-medium text-gray-300 hover:text-white transition-colors">
            {$t('common.home')}
          </a>
          <a href="/about" class="text-base font-medium text-gray-300 hover:text-white transition-colors">
            {$t('common.about')}
          </a>
          <a href="/#pricing" class="text-base font-medium text-gray-300 hover:text-white transition-colors">
            {$t('common.packages')}
          </a>
          <a href="/contact" class="text-base font-medium text-gray-300 hover:text-white transition-colors">
            {$t('common.contact')}
          </a>
        </div>
      </div>

      <div class="flex items-center gap-4">
        <LanguageSwitcher />

        <div class="hidden md:flex items-center gap-4">
          {#if $customerAuth.isAuthenticated}
            <a href="/dashboard" class="text-base font-medium text-gray-300 hover:text-white transition-colors">
              {$t('common.account')}
            </a>
            <button 
              on:click={() => {
                customerAuth.set({ isAuthenticated: false, token: null, user: null });
                window.location.href = '/';
              }}
              class="ml-8 whitespace-nowrap inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-brand-dark bg-brand-light hover:bg-white"
            >
              {$t('common.logout')}
            </button>
          {:else}
            <a href="/login" class="whitespace-nowrap text-base font-medium text-gray-300 hover:text-white transition-colors">
              {$t('common.login')}
            </a>
            <a href="/register" class="ml-8 whitespace-nowrap inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-brand-dark bg-brand-light hover:bg-white">
              {$t('common.register')}
            </a>
          {/if}
        </div>

        <!-- Mobile menu button -->
        <div class="flex items-center md:hidden">
          <button 
            type="button" 
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-dark"
            on:click={toggleMobileMenu}
          >
            <span class="sr-only">Open main menu</span>
            {#if isMobileMenuOpen}
              <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            {:else}
              <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            {/if}
          </button>
        </div>
      </div>
    </div>
  </nav>

  <!-- Mobile menu -->
  {#if isMobileMenuOpen}
    <div class="md:hidden bg-white border-t border-gray-100">
      <div class="pt-2 pb-3 space-y-1 px-4">
        <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
          {$t('common.home')}
        </a>
        <a href="/#pricing" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
          {$t('common.packages')}
        </a>
      </div>
      
      <div class="pt-4 pb-4 border-t border-gray-200 px-4">
        <div class="flex items-center justify-between mb-4">
          <span class="text-sm font-medium text-gray-500">Language</span>
          <LanguageSwitcher />
        </div>

        {#if $customerAuth.isAuthenticated}
          <div class="space-y-2">
            <a href="/dashboard" class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-brand-dark hover:bg-opacity-90">
              {$t('common.account')}
            </a>
            <button 
              on:click={() => {
                customerAuth.set({ isAuthenticated: false, token: null, user: null });
                window.location.href = '/';
              }}
              class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              {$t('common.logout')}
            </button>
          </div>
        {:else}
          <div class="space-y-2">
            <a href="/login" class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50">
              {$t('common.login')}
            </a>
            <a href="/register" class="block w-full text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-brand-dark hover:bg-opacity-90">
              {$t('common.register')}
            </a>
          </div>
        {/if}
      </div>
    </div>
  {/if}
</header>
