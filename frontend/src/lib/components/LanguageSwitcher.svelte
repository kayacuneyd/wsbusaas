<script lang="ts">
  import { locale } from 'svelte-i18n';
  import { onMount } from 'svelte';

  let isOpen = false;
  let container: HTMLDivElement;

  const languages = [
    { code: 'tr', label: 'Türkçe', flag: '🇹🇷' },
    { code: 'en', label: 'English', flag: '🇬🇧' },
    { code: 'de', label: 'Deutsch', flag: '🇩🇪' }
  ];

  $: currentLang = languages.find(l => l.code === $locale) || languages[0];

  function toggleDropdown() {
    isOpen = !isOpen;
  }

  function selectLanguage(langCode: string) {
    locale.set(langCode);
    localStorage.setItem('locale', langCode);
    isOpen = false;
  }

  function handleClickOutside(event: MouseEvent) {
    if (container && !container.contains(event.target as Node)) {
      isOpen = false;
    }
  }

  onMount(() => {
    document.addEventListener('click', handleClickOutside);
    return () => {
      document.removeEventListener('click', handleClickOutside);
    };
  });
</script>

<div class="relative" bind:this={container}>
  <button
    type="button"
    class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-dark"
    on:click={toggleDropdown}
  >
    <span class="text-lg leading-none">{currentLang.flag}</span>
    <span class="hidden md:inline">{currentLang.label}</span>
    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
  </button>

  {#if isOpen}
    <div class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
      <div class="py-1" role="menu" aria-orientation="vertical">
        {#each languages as lang}
          <button
            class="flex items-center w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 hover:text-gray-900"
            role="menuitem"
            on:click={() => selectLanguage(lang.code)}
          >
            <span class="text-lg mr-3 leading-none">{lang.flag}</span>
            {lang.label}
          </button>
        {/each}
      </div>
    </div>
  {/if}
</div>
