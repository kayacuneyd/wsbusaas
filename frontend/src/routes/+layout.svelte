<script lang="ts">
  import "../app.css";
  import Header from "$lib/components/Header.svelte";
  import Footer from "$lib/components/Footer.svelte";
  import { onMount } from 'svelte';
  import { seoSettings, loadSEOSettings } from '$lib/stores/seo';
  import { isLoading } from 'svelte-i18n';
  import '$lib/i18n'; // Initialize i18n

  onMount(async () => {
    await loadSEOSettings();
  });
</script>

<svelte:head>
  <title>{$seoSettings.seo_title || 'Bezmidar Sitebuilder'}</title>
  <meta name="description" content={$seoSettings.seo_description || 'Domain, hosting ve özel tasarım tek pakette.'} />
  <meta name="keywords" content={$seoSettings.seo_keywords || 'website, domain, hosting, sitebuilder'} />
  
  {#if $seoSettings.seo_og_title || $seoSettings.seo_og_description}
    <meta property="og:title" content={$seoSettings.seo_og_title || $seoSettings.seo_title} />
    <meta property="og:description" content={$seoSettings.seo_og_description || $seoSettings.seo_description} />
    {#if $seoSettings.seo_og_image}
      <meta property="og:image" content={$seoSettings.seo_og_image} />
    {/if}
    <meta property="og:type" content="website" />
  {/if}
</svelte:head>

{#if $isLoading}
  <div class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-dark"></div>
  </div>
{:else}
  <div class="flex flex-col min-h-screen bg-gray-50">
    <Header />
    <main class="flex-grow">
      <slot />
    </main>
    <Footer />
  </div>
{/if}
